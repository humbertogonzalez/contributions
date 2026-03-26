<?php

namespace Psa\Distributes\Observers;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class SalesAddItem implements ObserverInterface
{
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly CheckoutSession $checkoutSession,
        protected LoggerInterface $logger
    )
    {
    }

    public function execute(Observer $observer)
    {
        $this->logger->info("===== SalesAddItem::execute =====");
        $canje = $this->request->getParam('canje');
        $purificador = $this->request->getParam('purificador');

        $this->logger->info("> Current getCanje: " . $this->checkoutSession->getCanje());

        if($canje) {
            $this->logger->info("> es Canje");
            $quoteItem = $observer->getQuoteItem();
            $qty = $this->request->getParam('qty') ?? 1;
            $canjeQty = $quoteItem->getQtyCanje() ?? 0;
            $quoteItem->setCanje(true);
            $quoteItem->setQtyCanje($canjeQty + $qty);
            $dipId = (int)$this->request->getParam('dip');

            if($this->checkoutSession->getCanje() != "repuestos") {
                $this->checkoutSession->setCanje('canje');
                $this->checkoutSession->setIsPurificador(false);
            }

            if ($dipId) {
                $this->checkoutSession->getQuote()->setDipId($dipId);
            }
        }

        if($purificador) {
            $this->logger->info("> es Purificador");
            $quoteItem = $observer->getQuoteItem();
            $qty = $this->request->getParam('qty') ?? 1;
            $canjeQty = $quoteItem->getQtyCanje() ?? 0;
            $quoteItem->setCanje(true);
            $quoteItem->setQtyCanje($canjeQty + $qty);
            $this->checkoutSession->setCanje('');
            $this->checkoutSession->setIsPurificador(true);
        }
    }
}
