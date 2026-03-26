<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\RestrictAccess\Observer;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;

class RedirectIfNotLoggedIn implements ObserverInterface
{
    /**
     * RedirectIfNotLoggedIn constructor
     *
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param ActionFlag $actionFlag
     * @param Http $response
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private Session $customerSession,
        private RedirectInterface $redirect,
        private ActionFlag $actionFlag,
        private Http $response,
        private RequestInterface $request,
        private StoreManagerInterface $storeManager,
        private UrlInterface $urlBuilder,
        private FormKey $formKey
    ) {

    }

    public function execute(Observer $observer)
    {
        $actionName = $this->request->getFullActionName();

        $loginAction = 'customer_account_login';
        // Exclude the login and registration related pages to prevent redirect loops
        $excludedRoutes = [
            'swagger_index_index',
            'customer_account',
            'customer_account_index',
            'customer_account_loginPost',
            'customer_account_createPost',
            'customer_account_createPassword',
            'customer_account_createpassword',
            'customer_account_forgotpassword',
            'customer_account_forgotpasswordpost',
            'customer_account_resetpasswordpost',
            'register_index_index',
            'register_index_post',
            'register_index_success',
            'loginascustomer_login_login',
            'loginascustomer_login_index',
            'configurations_index_index'
        ];

        if (!$this->customerSession->isLoggedIn()) {
            // Validate if login url has referer param included
            if($actionName == $loginAction && $this->loginUrlHasRefererParam($observer->getData('request')->getRequestUri()))
            {
                array_push($excludedRoutes, $loginAction);
            }

            if (!in_array($actionName, $excludedRoutes)) {
                $this->redirectToLogin(($actionName == $loginAction));
            }
        }
    }

    private function redirectToLogin($cameFromLogin=false) : void
    {
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);

        if($cameFromLogin)
            $referer = $this->storeManager->getStore()->getBaseUrl(); // Get homepage URL as the referer
        else
            $referer = $this->urlBuilder->getCurrentUrl(); // Get the current URL as the referer

        $loginUrl = $this->storeManager->getStore()->getUrl('customer/account/login', [
            'referer' => base64_encode($referer),
            'form_key' => $this->formKey->getFormKey()
        ]);
        $this->response->setRedirect($loginUrl);
    }

    private function loginUrlHasRefererParam($requestUri) : bool
    {
        $params = explode('/', $requestUri);
        return in_array('referer', $params);
    }
}
