<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice;

use BalloonGroup\UploadInvoiceApi\Controller\Adminhtml\Uploadinvoice as ParentClass;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends ParentClass
{

    /**
     * NewAction constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        protected readonly ForwardFactory $resultForwardFactory
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
        return $this->_authorization->isAllowed('BalloonGroup_UploadInvoiceApi::upload_invoice_save');
    }

    /**
     * New action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}

