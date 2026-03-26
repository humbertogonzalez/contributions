<?php

namespace Redegal\CustomGoogleTagManager\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Redegal\CustomGoogleTagManager\Helper\Data;

class CustomerCookie implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var Data
     */
    private $data;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CustomerCookie constructor.
     * @param Data $data
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Data $data,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->data = $data;
        $this->customerRepository = $customerRepository;
    }
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getEvent()->getCustomer()->getId();
        $customer = $this->customerRepository->getById($customerId);
        $helperData = $this->data;

        if (empty($helperData->getCookieData())) {
            $data['userID'] = uniqid();
            $data['register_date'] = $customer->getCreatedAt();
            $data['newsletter'] =  $helperData->isCustomerSubscribeById($customerId);
            $helperData->setCookieData(json_encode($data));
        }
    }
}
