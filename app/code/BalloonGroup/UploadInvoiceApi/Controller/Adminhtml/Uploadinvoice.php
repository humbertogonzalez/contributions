<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\Page;

abstract class Uploadinvoice extends Action
{
    /** @var string */
    const ADMIN_RESOURCE = 'BalloonGroup_UploadInvoiceApi::top_level';

    /**
     * Uploadinvoice consturctor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        protected readonly Registry $coreRegistry
    ) {
        parent::__construct($context);
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BalloonGroup_UploadInvoiceApi::upload_invoice');
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    public function initPage($resultPage): Page
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('BalloonGroup'), __('BalloonGroup'))
            ->addBreadcrumb(__('Carga de Facturas'), __('Manage Invoices'));
        return $resultPage;
    }
}

