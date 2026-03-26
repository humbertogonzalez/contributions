<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice;

use BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice as ParentClass;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use BalloonGroup\UploadInvoiceApi\Model\UploadInvoice as UploadInvoiceController;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;

class Edit extends ParentClass
{
    /**
     * Edit constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param UploadInvoiceController $modelUploadInvoice
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        protected readonly PageFactory $resultPageFactory,
        protected readonly UploadInvoiceController $modelUploadInvoice
    ) {
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Is Allowed
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BalloonGroup_UploadInvoiceApi::upload_invoice_update');
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('entity_id');
       $this->modelUploadInvoice->load($id);

        if ($id) {
            if (!$this->modelUploadInvoice->getId()) {
                $this->messageManager->addErrorMessage(__('This Upload Invoice no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('balloongroup_uploadinvoiceapi_upload_invoice', $this->modelUploadInvoice);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Invoice') : __('Upload Invoice'),
            $id ? __('Edit Invoice') : __('Upload Invoice')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Invoice'));
        $resultPage->getConfig()->getTitle()->prepend($this->modelUploadInvoice->getId() ? __('Edit Invoice %1', $this->modelUploadInvoice->getId()) : __('Upload Invoice'));
        return $resultPage;
    }
}

