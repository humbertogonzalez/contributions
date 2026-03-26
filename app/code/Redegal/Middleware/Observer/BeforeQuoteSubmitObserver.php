<?php
namespace Redegal\Middleware\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Redegal\Middleware\Model\Repository\InventoryBalanceOrderMiddlewareRepository;
use Magento\Framework\Exception\LocalizedException;
use \Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\CatalogInventory\Helper\Data;
use Redegal\Middleware\Model\Email\MiddlewareErrorSender;

class BeforeQuoteSubmitObserver implements ObserverInterface
{

    public function __construct(
        LoggerInterface $logger,
        InventoryBalanceOrderMiddlewareRepository $inventoryOrderMiddlewareRepository,
        ManagerInterface $messageManager,
        MiddlewareErrorSender $sender
    ) {
        $this->logger = $logger;
        $this->sender = $sender;
        $this->inventoryOrderMiddlewareRepository = $inventoryOrderMiddlewareRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $quote = $observer->getQuote();
        if ($quote) {
            $quoteItems = $quote->getAllVisibleItems();
            $quoteItemsIndexed = [];
            $itemsWithSkuAndQty = [];
            foreach($quoteItems as $quoteItem) {
                $quoteItemsIndexed[$quoteItem->getSku()] = $quoteItem;
                $itemsWithSkuAndQty[$quoteItem->getSku()] = [
                    'sku' => $quoteItem->getSku(),
                    'qty' => $quoteItem->getQty()
                ];
            }

            try {
                $response = $this->inventoryOrderMiddlewareRepository->findByItems($itemsWithSkuAndQty);
                if (!$response) {
                    throw new Exception('[Validate Stock Observer] Error checking stock in middleware. Problem with middleware response');
                }
                $this->logger->debug('[Validate Stock Observer] Transformed response: '. json_encode($response));
            } catch (\GuzzleHttp\Exception\RequestException $ex) {
                $this->logger->error('[Validate Stock Observer] Error when getting stock information from middleware:'. $ex->getMessage());
                if ($ex->getResponse()) {
                    $message = $ex->getResponse()->getBody()->getContents();
                } else {
                    $message = $ex->getMessage();
                }
                $this->sendMiddlewareErrorEmail('comprobando el stock en el proceso de compra', $message);

                throw $ex;
            } catch(\Exception $e) {
                $this->logger->error('[Validate Stock Observer] Error when getting stock information from middleware:'. $e->getMessage());
                $this->sendMiddlewareErrorEmail('comprobando el stock en el proceso de compra', $e->getMessage());

                throw $e;
            }

            $indexedResponseItems = [];
            $outOutItems = [];
            foreach($response as $item) {
                $indexedResponseItems[$item['sku']] = $item;
                $quoteItem =  $quoteItemsIndexed[$item['sku']];
                if ($quoteItem->getQty() > $item['availableQty']) {
                                  
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Data::ERROR_QTY,
                        __('The requested qty is not available')
                    );

                    $this->messageManager->addErrorMessage(__('The requested qty is not available %1', '('.(int) $item['availableQty'].')'), 'quote_item'.$quoteItem->getId());

                    $quoteItem->getQuote()->addErrorInfo(
                        'stock',
                        'cataloginventory',
                        Data::ERROR_QTY,
                        __('Some of the products are out of stock.')
                    );

                    $outOutItems[] = ['sku' => $quoteItem->getSku(), 'qty' => $item['availableQty']];
                }
            }

            if (!empty($outOutItems)) {
                $strings = ' ';
                foreach($outOutItems as $item) {
                    $strings .= ', '.$item['sku'].'('.(int) $item['qty'].')';
                }
                $message = __('The requested qty is not available %1', $strings);
                $this->messageManager->addErrorMessage($message);

                throw new \LocalizedException($message);
            }
        }
    }

    private function sendMiddlewareErrorEmail($customMessage, $exceptionMessage)
    {
        $this->sender->sendEmail($customMessage, $exceptionMessage);
    }
}