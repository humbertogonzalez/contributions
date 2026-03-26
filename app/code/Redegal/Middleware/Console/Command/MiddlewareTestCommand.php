<?php

namespace Redegal\Middleware\Console\Command;

use Redegal\Middleware\Model\Console\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Repository\InventoryBalanceMiddlewareRepository;
use Redegal\Middleware\Model\Repository\InventoryBalanceOrderMiddlewareRepository;
use Redegal\Middleware\Model\Repository\SalesOrderMiddlewareRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Framework\App\State;
use Redegal\Middleware\Model\ResourceModel\InventoryReservations\CustomCleanupReservations;
use Redegal\Middleware\Model\Email\SalesOrderErrorSender;
use Redegal\Middleware\Model\Email\MiddlewareErrorSender;

class MiddlewareTestCommand extends Command
{
    public function __construct(
        State $state,
        LoggerInterface $logger,
        InventoryBalanceMiddlewareRepository $inventoryMiddlewareRepository,
        InventoryBalanceOrderMiddlewareRepository $inventoryOrderMiddlewareRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,   
        DefaultSourceProviderInterface $defaultSource,
        SalesOrderMiddlewareRepository $salesOrderMiddlewareRepository,
        ProductCollection $productCollectionFactory,
        OrderCollection $orderCollectionFactory,
        CustomCleanupReservations $customCleanUpReservation,
        SalesOrderErrorSender $sender,
        MiddlewareErrorSender $senderMiddlewareError
    ) {
        $this->logger                             = $logger;
        $this->state                              = $state;
        $this->sourceItemFactory                  = $sourceItemFactory;
        $this->sourceItemsSave                    = $sourceItemsSave;
        $this->defaultSource                      = $defaultSource;
        $this->inventoryMiddlewareRepository      = $inventoryMiddlewareRepository;
        $this->inventoryOrderMiddlewareRepository = $inventoryOrderMiddlewareRepository;
        $this->salesOrderMiddlewareRepository     = $salesOrderMiddlewareRepository;
        $this->productCollectionFactory           = $productCollectionFactory;
        $this->orderCollectionFactory             = $orderCollectionFactory;
        $this->customCleanUpReservation           = $customCleanUpReservation;
        $this->sender                             = $sender;
        $this->senderMiddlewareError              = $senderMiddlewareError;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('test:middleware')
            ->setDescription('Test middleware connections')
            ->setDefinition([
                new InputArgument('service', InputArgument::REQUIRED, 'Service to call'),
                new InputOption('raw', null, InputOption::VALUE_NONE, 'Raw response without transform')
            ]);

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->service = $input->getArgument('service');
        $this->raw = $input->getOption('raw');

        Console::enable();
        Console::green('[START TEST MIDDLEWARE SERVICE - '.$this->service.' ]');
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND); 

        if ($this->service === 'update-stock') {
            $this->updateProductsStock();
        }

        if ($this->service === 'check-stock') {
            $this->checkProductsStock();
        }

        if ($this->service === 'send-orders') {
            $this->sendPayedOrdersToMiddleware();
        }

        Console::green('[FINISH TEST MIDDLEWARE SERVICE - '.$this->service.' ]');
    }

    private function updateProductsStock()
    {
        try {
            $response = $this->inventoryMiddlewareRepository->findAll();
            Console::green(json_encode($response));
            $productsToUpdateStock = json_decode($response, true);
            $responseByChunk = array_chunk($productsToUpdateStock, 1000);
            $itemsSkus = [];
            foreach ($responseByChunk as $idx => $chunk) {

                    Console::green('Processing chunk ('.$idx.') - Total products '.count($chunk).'/'.count($productsToUpdateStock));
                    $this->logger->debug('Processing chunk ('.$idx.') - Total products '.count($chunk).'/'.count($productsToUpdateStock));
                    $stocksIndexedBySku = [];
                    foreach($chunk as $stock) {
                        $stocksIndexedBySku[$stock['sku']] = $stock;
                    }

                    $itemsSkus = array_merge($itemsSkus, $this->updateProductsStockInDB($stocksIndexedBySku));
            }

            $this->cleanInventoryReservations($itemsSkus);

            $rejected = array_diff(array_column($productsToUpdateStock, 'sku'), $itemsSkus);
            $this->logger->info("Products Not Found in DB(".count($rejected)."/".count($productsToUpdateStock).")");
            $this->logger->info(join(',', array_values($rejected)));
            Console::green("Products Not Found in DB(".count($rejected)."/".count($productsToUpdateStock).")");
            Console::green(join(',', array_values($rejected)));

        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $this->logger->error('[Update Stock From Command] Error when getting stock information from middleware:'. $ex->getMessage());
            if ($ex->getResponse()) {
                $message = $ex->getResponse()->getBody()->getContents();
            } else {
                $message = $ex->getMessage();
            }
            $this->sendMiddlewareErrorEmail('actualizando el stock desde el comando', $message);
            Console::error('[Update Stock From Command] Error when getting stock information from middleware: '. $ex->getMessage());

        } catch (\Exception $e) {
            $this->logger->error('[Update Stock From Command] Error processing chunk ('.$idx.') -> '.$e->getMessage());
            $this->logger->error('[Update Stock From Command] Not processed stock from products:'. join(',', array_keys($stocksIndexedBySku)));
            Console::error('[Update Stock From Command] Error processing chunk ('.$idx.') -> '.$e->getMessage());
            Console::error('[Update Stock From Command] Not processed stock from products:'. join(',', array_keys($stocksIndexedBySku)));
            $this->sendMiddlewareErrorEmail('actualizando el stock desde el comando', $e->getMessage());
        }

       
    }


    /**
     * Update stock from products in DB
     *
     * @param array $productsSkus
     * @return void
     */
    private function updateProductsStockInDB($stocks)
    {
        $productsSkus = array_keys($stocks);
        $collection = $this->productCollectionFactory->create()->setFlag('has_stock_status_filter', false)
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('sku', $productsSkus)
        ->load();
        $items = [];
        $itemsSkus = [];
        foreach ($collection as $product) {
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode($this->defaultSource->getCode());
            $sourceItem->setSku($product->getSku());
            $sourceItem->setQuantity($stocks[$product->getSku()]['qty']);
            $sourceItem->setStatus((bool)$stocks[$product->getSku()]['qty']);
            $items[] = $sourceItem;
            $itemsSkus[] = $product->getSku();

            Console::green($product->getName());
            $this->logger->debug($product->getName());
        }

        if (!empty($items)) {
            $this->sourceItemsSave->execute($items);
        }

        return $itemsSkus;
    }

    /**
     * Remove inventory reservations entries for updated products
     *
     * @param array $productsSkus
     * @return void
     */
    private function cleanInventoryReservations($productsSkus)
    {
        try {
            if (!empty($productsSkus)) {
                $this->customCleanUpReservation->setSkus($productsSkus);
                $this->customCleanUpReservation->execute();
            }
        } catch (\Exception $e) {
            $this->logger->error('[Update Stock Cron] Error cleaning reservations -> '.$e->getMessage());
            Console::error('[Update Stock Cron] Error cleaning reservations -> '.$e->getMessage());
        }
    }

    private function checkProductsStock()
    {
        $items = [
            ['sku' => '675241', 'qty' => '10'],
            ['sku' => '985821', 'qty' => '10'],
            ['sku' => '025825', 'qty' => '10']
        ];

        $response = $this->inventoryOrderMiddlewareRepository->findByItems($items);
        Console::green(json_encode($response));
    }

    /**
     * Test ERP service to send processing orders
     *
     * @return void
     */
    private function sendPayedOrdersToMiddleware()
    {
        $orders = $this->orderCollectionFactory->create()->addAttributeToSelect('*')->addFieldToFilter(
            'status',['processing', 'pending']);

        foreach($orders as $order) {
            try {
                Console::green('Processing order with Increment ID: '.$order->getIncrementId());
                $response = $this->salesOrderMiddlewareRepository->sendSalesOrder($order);
                Console::green(json_encode($response));
                $order->setStatus('sended');//TODO: Change when decided status
                $order->setState('sended');
                $order->save();
            } catch (\Exception $e) {
                Console::red('Error processing order: '.$e->getMessage());
                $this->sendOrderErrorEmail($order, $e->getMessage());
            }
        }
    }

    private function updateStockTest()
    {
        $collection = $this->productCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sku', ['675336', '675313'])
                    ->load();
        foreach ($collection as $product) {
            //Console::green(json_encode($product->getName()));
            $product->setQuantityAndStockStatus(['qty' => 200, 'is_in_stock' => 1]);
            $product->save();
        }
    }

    private function sendOrderErrorEmail($order, $message = '')
    {
        $this->sender->sendEmail($order, $message);
    }

    private function sendMiddlewareErrorEmail($customMessage, $exceptionMessage)
    {
        $this->senderMiddlewareError->sendEmail($customMessage, $exceptionMessage);
    }
}
