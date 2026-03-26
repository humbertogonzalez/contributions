<?php
namespace Redegal\Checkout\Api;

class QuoteManagement {

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession    
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Check if given email is associated with a customer account in given website.
     *
     * @param string $customerEmail
     * @return bool
     */
    public function setEmailInQuote($customerEmail)
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            if ($quote) {
                $quote->setCustomerEmail($customerEmail);
                $quote->save();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}