<?php
namespace BalloonGroup\EndOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class CustomCheckoutSuccessRedirect implements ObserverInterface
{
    protected $url;
    protected $response;
    protected $checkoutSession;
    protected $customerSession;

    public function __construct(
        UrlInterface $url,
        RedirectInterface $response,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        $this->url = $url;
        $this->response = $response;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    public function execute(Observer $observer)
    {
        // Borrar la variable de sesión del carrito
        $this->checkoutSession->unsQuoteId();

        // Borrar la variable de sesión del método de pago seleccionado
        $this->checkoutSession->unsSelectedPaymentMethod();

        // Borrar la variable de sesión del método de envío seleccionado
        $this->checkoutSession->unsSelectedShippingMethod();

        // Borrar una variable de sesión personalizada para el cliente
        $this->customerSession->unsMiVariablePersonalizada();

        // Eliminar la página de pago del historial del navegador
        echo '<script type="text/javascript">
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            </script>';
    }
}

