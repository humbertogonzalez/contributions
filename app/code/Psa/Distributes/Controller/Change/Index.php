<?php

namespace Psa\Distributes\Controller\Change;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;


class Index extends Action implements HttpGetActionInterface
{

    public function __construct(
        Context $context,
        protected Session $session,
        protected readonly PageFactory $_pageFactory
    )
    {
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute(): ResultInterface
    {
        $this->session->setCanje('canje');
        $page = $this->_pageFactory
            ->create();
        $page->getConfig()
            ->getTitle()
            ->set(__('Canje'));
        return $page;
    }
}
