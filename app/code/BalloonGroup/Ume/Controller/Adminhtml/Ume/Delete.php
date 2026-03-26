<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Controller\Adminhtml\Ume;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Registry;
use BalloonGroup\Ume\Controller\Adminhtml\Ume;
use BalloonGroup\Ume\Model\Ume as UmeModel;

class Delete extends Ume
{
    /**
     * Delete constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param UmeModel $umeModel
     */
    public function __construct(
        Context $context,
        private readonly Registry $coreRegistry,
        private readonly UmeModel $umeModel
    ) {
        parent::__construct($context, $coreRegistry);
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
        $id = $this->getRequest()->getParam('ume_id');

        if ($id) {
            try {
                $model = $this->umeModel->load($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the Ume.'));

                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['ume_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a Ume to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
