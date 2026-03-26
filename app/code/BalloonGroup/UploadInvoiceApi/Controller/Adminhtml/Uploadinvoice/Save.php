<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\Model\View\Result\Redirect;
use BalloonGroup\UploadInvoiceApi\Model\UploadInvoice as UploadInvoiceController;
use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;

class Save extends Action
{
    /**
     * Save constructor
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param UploadInvoiceController $modelUploadInvoice
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        Context $context,
        protected readonly DataPersistorInterface $dataPersistor,
        protected readonly UploadInvoiceController $modelUploadInvoice,
        protected readonly CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BalloonGroup_UploadInvoiceApi::upload_invoice_save');
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');

            $model = $this->modelUploadInvoice->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('Invoice does not exists'));
                return $resultRedirect->setPath('*/*/');
            }

            if (isset($data['id_customer'])) {
                $company = $this->companyRepository->getByField("reseller_id", $data['id_customer']);

                if (!$company->getId()) {
                    $this->messageManager->addErrorMessage(__('The requested company not exists: %1', $data['id_customer']));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Invoice saved'));
                $this->dataPersistor->clear('balloongroup_uploadinvoiceapi_upload_invoice');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong with the invoice'));
            }

            $this->dataPersistor->set('balloongroup_uploadinvoiceapi_upload_invoice', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

