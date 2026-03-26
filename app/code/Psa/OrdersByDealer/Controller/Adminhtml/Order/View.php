<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BalloonGroup\OrdersByDealer\Controller\Adminhtml\Order;

use BalloonGroup\OrdersByDealer\Controller\Adminhtml\DefaultAction;

class View extends DefaultAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'BalloonGroup_OrdersByDealer::view';

    /**
     * View order detail
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($order) {
            try {
                $resultPage = $this->_initAction();
                $resultPage->setActiveMenu('BalloonGroup_OrdersByDealer::grid');
                $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage(__('Exception occurred during order load'));
                $resultRedirect->setPath('sales_dealer/grid/index');
                return $resultRedirect;
            }
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $order->getIncrementId()));
            return $resultPage;
        }
        $resultRedirect->setPath('sales_dealer/*/');
        return $resultRedirect;
    }
}
