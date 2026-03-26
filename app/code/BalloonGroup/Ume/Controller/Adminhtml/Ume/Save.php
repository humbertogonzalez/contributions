<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Controller\Adminhtml\Ume;

use BalloonGroup\Ume\Model\Ume;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\Model\View\Result\Redirect;

class Save extends Action
{
    /**
     * Save constructor
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param Ume $ume
     */
    public function __construct(
        Context $context,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly Ume $ume
    ) {
        parent::__construct($context);
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
            $id = $this->getRequest()->getParam('ume_id');
            $model = $this->ume->load($id);

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Ume no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Ume.'));
                $this->dataPersistor->clear('balloongroup_ume_ume');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['ume_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Ume.'));
            }

            $this->dataPersistor->set('balloongroup_ume_ume', $data);

            return $resultRedirect->setPath('*/*/edit', ['ume_id' => $this->getRequest()->getParam('ume_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
