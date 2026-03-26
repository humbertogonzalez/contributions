<?php
namespace BalloonGroup\Distributes\Controller\Index;

use BalloonGroup\Distributes\Helper\Dealers\Process;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use BalloonGroup\Distributes\Helper\Order;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Process $process
     * @param Order $order
     */
    public function __construct(
        Context $context,
        protected PageFactory $pageFactory,
        protected Process $process,
        protected Order $order
    )
    {
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $getOrder = $this->order->getOrderData(449);
        $items = $getOrder->getAllItems();
        $grandTotal = $getOrder->getGrandTotal();;

        $resultPage = $this->pageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__(' heading '));

        $block = $resultPage->getLayout()
            ->createBlock('WeltPixel\ThankYouPage\Block\Onepage\Success')
            ->setTemplate('BalloonGroup_Distributes::email/personalData.phtml')
            ->toHtml();
        $this->getResponse()->setBody($block);
    }
}
