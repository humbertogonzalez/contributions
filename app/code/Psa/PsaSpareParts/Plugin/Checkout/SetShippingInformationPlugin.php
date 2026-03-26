<?php

namespace Psa\PsaSpareParts\Plugin\Checkout;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\QuoteRepository;

class SetDefaultShippingAddressPlugin
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    public function __construct(QuoteRepository $quoteRepository)
    {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Set default shipping address
     *
     * @param \Magento\Checkout\Api\ShippingInformationManagementInterface $subject
     * @param string $cartId
     * @param AddressInterface $addressInformation
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Api\ShippingInformationManagementInterface $subject,
        $cartId,
        AddressInterface $addressInformation
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();

        // If the shipping address is not already set, create a default address
        if (!$shippingAddress->getStreetLine1()) {
            $shippingAddress->setCountryId('AR')
                ->setCity('Buenos Aires')
                ->setPostcode('1091')
                ->setStreet(['Moreno 877'])
                ->setTelephone('0-810-2222-772')
                ->setIsDefaultShipping(true);
            $quote->setShippingAddress($shippingAddress);
            //$quote->setBillingAddress($shippingAddress)
            $quote->save();
        }

        return [$cartId, $addressInformation];
    }
}
