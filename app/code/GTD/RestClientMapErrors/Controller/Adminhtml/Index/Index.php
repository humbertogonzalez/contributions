<?php

namespace Balloon\RestClientMapErrors\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Balloon_RestClientMapErrors::menu';

    public function execute(): ResultInterface
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE)->setActiveMenu('Balloon_RestClientMapErrors::grid');
    }
}
