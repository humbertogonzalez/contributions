<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Register\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Session\SessionManager;
use \Magento\Framework\Session\StorageInterface;

class Success extends Action
{
    /**
     * Success constructor
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param SessionManager $sessionManager
     * @param StorageInterface $storage
     */
    public function __construct(
        Context $context,
        protected PageFactory $pageFactory,
        private SessionManager $sessionManager,
        private StorageInterface $storage
    )
    {
        return parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->sessionManager->getFormSubmitted()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $this->sessionManager->unsFormSubmitted();
        return $this->pageFactory->create();
    }
}
