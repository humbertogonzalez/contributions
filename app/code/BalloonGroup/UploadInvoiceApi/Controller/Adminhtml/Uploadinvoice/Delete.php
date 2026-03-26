<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice;

use Magento\Framework\Controller\ResultInterface;
use BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice as ParentClass;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use BalloonGroup\UploadInvoiceApi\Model\Uploadinvoice as UploadinvoiceController;
use Magento\Framework\Registry;


class Delete extends ParentClass
{
    /**
     * Delete constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UploadinvoiceController $modelUploadinvoice
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        protected readonly UploadinvoiceController $modelUploadinvoice
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
        return $this->_authorization->isAllowed('BalloonGroup_UploadInvoiceApi::upload_invoice_delete');
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $this->modelUploadinvoice->load($id);
                $this->modelUploadinvoice->delete();
                $this->messageManager->addSuccessMessage(__('Successfully Deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find Invoice'));
        return $resultRedirect->setPath('*/*/');
    }
}

