<?php

namespace Balloon\RestClientErrorReport\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{

    public function __construct(
        Context $context,
        private readonly PageFactory $rawFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Add the main Admin Grid page
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->rawFactory->create();
        $resultPage->setActiveMenu('Balloon_RestClientErrorReport::grid');
        $resultPage->getConfig()->getTitle()->prepend(__('Rest Client Log'));

        return $resultPage;
    }
}
