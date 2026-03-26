<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Controller\Adminhtml\Ume;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use BalloonGroup\Ume\Controller\Adminhtml\Ume;
use BalloonGroup\Ume\Model\UmeFactory;

class Edit extends Ume
{
    /**
     * Edit constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param UmeFactory $umeFactory
     */
    public function __construct(
        Context $context,
        private readonly Registry $coreRegistry,
        private readonly PageFactory $resultPageFactory,
        private readonly UmeFactory $umeFactory
    ) {
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('ume_id');
        $model = $this->umeFactory->create();

        if ($id) {
            $model->load($id);

            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Ume no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('balloongroup_ume_ume', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Ume') : __('New Ume'),
            $id ? __('Edit Ume') : __('New Ume')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Umes'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ? __('Edit Ume %1', $model->getId()) : __('New Ume')
        );

        return $resultPage;
    }
}
