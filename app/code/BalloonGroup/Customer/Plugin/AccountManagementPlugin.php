<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\EmailNotification;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Customer\Helper\View as CustomerViewHelper;

class AccountManagementPlugin
{
    /**
     * AccountManagementPlugin constructor
     *
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param SenderResolverInterface $senderResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomerRegistry $customerRegistry
     * @param DataObjectProcessor $dataProcessor
     * @param CustomerViewHelper $customerViewHelper
     */
    public function __construct(
        private TransportBuilder $transportBuilder,
        private StoreManagerInterface $storeManager,
        private LoggerInterface $logger,
        private SenderResolverInterface $senderResolver,
        private ScopeConfigInterface $scopeConfig,
        private CustomerRegistry $customerRegistry,
        private DataObjectProcessor $dataProcessor,
        private CustomerViewHelper $customerViewHelper
    ) {

    }

    /**
     * Send custom email after creating a customer.
     *
     * @param AccountManagement $subject
     * @param CustomerInterface $customer
     * @param CustomerInterface $customerData
     * @param string|null $password
     * @return CustomerInterface
     */
    public function afterCreateAccount(
        AccountManagement $subject,
        CustomerInterface $customer,
        CustomerInterface $customerData,
        string $password = null
    ) {
        try {
            $customerName = $customerData->getFirstname() . ' ' . $customerData->getLastname();
            $customerEmail = $customerData->getEmail();
            $store = $this->storeManager->getStore($customer->getStoreId());

            $from = $this->senderResolver->resolve(
                $this->scopeConfig->getValue(
                    EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY,
                    ScopeInterface::SCOPE_STORE, $customer->getStoreId()
                ),
                $customer->getStoreId()
            );

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('email_password_template')
                ->setTemplateOptions(['area' => 'frontend', 'store' => $customer->getStoreId()])
                ->setTemplateVars(
                    [
                        'customer' => $this->getFullCustomerObject($customer),
                        'store' => $store,
                        'password' => $password
                    ]
                )
                ->setFrom($from)
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error(__('Error sending welcome email: %1', $e->getMessage()));
        }

        return $customer;
    }

    /**
     * @param $customer
     * @return CustomerSecure
     * @throws NoSuchEntityException
     */
    private function getFullCustomerObject($customer): CustomerSecure
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }
}
