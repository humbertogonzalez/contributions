<?php

namespace BalloonGroup\OrdersByDealer\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use BalloonGroup\OrdersByDealer\Controller\Adminhtml\DefaultAction;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends DefaultAction implements HttpGetActionInterface
{
    public function execute(): Page
    {
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('BalloonGroup_OrdersByDealer::grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Pedidos'));
        return $resultPage;
    }
}
