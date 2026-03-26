<?php

namespace Redegal\Middleware\Cron;

use \Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Repository\InventoryBalanceMiddlewareRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Redegal\Middleware\Model\ResourceModel\InventoryReservations\CustomCleanupReservations;
use Redegal\Middleware\Model\Email\MiddlewareErrorSender;

class UpdateStock
{
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        InventoryBalanceMiddlewareRepository $inventoryMiddlewareRepository,
        CustomCleanupReservations $customCleanUpReservation,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory,   
        DefaultSourceProviderInterface $defaultSource,
        CollectionFactory $productCollectionFactory,
        MiddlewareErrorSender $sender
    ) {
        $this->logger = $logger;
        $this->sender = $sender;
        $this->inventoryMiddlewareRepository = $inventoryMiddlewareRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->customCleanUpReservation = $customCleanUpReservation;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->defaultSource = $defaultSource;
    }

    public function execute()
    {
        $this->logger->debug('[Update Stock Cron] Running `cron` from `Redegal\UpdateStock`');
        try {
            $this->updateProductsStock();

        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            $this->logger->error('[Update Stock Cron] Error when getting stock information from middleware:'. $ex->getMessage());
            if ($ex->getResponse()) {
                $message = $ex->getResponse()->getBody()->getContents();
            } else {
                $message = $ex->getMessage();
            }
            
            $this->sendMiddlewareErrorEmail('actualizando el stock desde el cron automático', $message);

            throw $ex;
        } catch (\Exception $e) {
            $this->sendMiddlewareErrorEmail('actualizando el stock desde el cron automático', $e->getMessage());
        }

        $this->logger->debug('[Update Stock Cron] Finish `cron` from `Redegal\UpdateStock`');
        return $this;
    }


    private function updateProductsStock()
    {
        $response = $this->inventoryMiddlewareRepository->findAll();

        $this->logger->debug('[Update Stock Cron] Transformed response: '. json_encode($response));
        $productsToUpdateStock = json_decode($response, true);
        $responseByChunk = array_chunk($productsToUpdateStock, 1000);
        $itemsSkus = [];
        foreach ($responseByChunk as $idx => $chunk) {
            try {
                $this->logger->debug('Processing chunk ('.$idx.') - Total products '.count($chunk).'/'.count($productsToUpdateStock));
                $stocksIndexedBySku = [];
                foreach($chunk as $stock) {
                    $stocksIndexedBySku[$stock['sku']] = $stock;
                }

                $itemsSkus = array_merge($itemsSkus, $this->updateProductsStockInDB($stocksIndexedBySku));

            } catch (\Exception $e) {
                $this->logger->error('[Update Stock Cron] Error processing chunk ('.$idx.') -> '.$e->getMessage());
                $this->logger->error('[Update Stock Cron] Not processed stock from products:'. join(',', array_keys($stocksIndexedBySku)));
                throw $e;
            }
        }

        $this->cleanInventoryReservations($itemsSkus);

        $rejected = array_diff(array_column($productsToUpdateStock, 'sku'), $itemsSkus);
        $this->logger->info("Products Not Found in DB(".count($rejected)."/".count($productsToUpdateStock).")");
        $this->logger->info(join(',', array_values($rejected)));
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
        }
    }

    private function sendMiddlewareErrorEmail($customMessage, $exceptionMessage)
    {
        $this->sender->sendEmail($customMessage, $exceptionMessage);
    }
}
