<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Psr\Log\LoggerInterface;

class TransportBuilderPlugin
{
    /**
     * TransportBuilderPlugin constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly CustomerCollectionFactory $customerCollectionFactory,
        private readonly LoggerInterface $logger
    ) {

    }

    /**
     * @param TransportBuilder $subject
     * @param array|string $address
     * @param string $name
     * @return array
     */
    public function beforeAddTo(TransportBuilder $subject, $address, $name = ''): array
    {
        $finalEmailAddress = $this->getFinalRecipientEmail($address);

        return [$finalEmailAddress, $name];
    }

    /**
     * Get Final recipient email
     *
     * @param string $recipientEmail
     * @return string
     */
    private function getFinalRecipientEmail(string $recipientEmail): string
    {
        try {
            $this->logger->info("> RecipientEmail: " . $recipientEmail);
            $customer = $this->customerCollectionFactory->create();
            $customer->addAttributeToSelect('email','id');
            $customer->addAttributeToFilter('email', $recipientEmail);
            $customer->setPageSize(1);
            $customer = $customer->getFirstItem();

            $customer = $this->customerRepository->getById($customer->getId());
            $isFakeAttr = $customer->getCustomAttribute('is_fake_email');
            $isFake = $isFakeAttr && (int)$isFakeAttr->getValue() === 1;

            if ($isFake) {
                $this->logger->info("> isFakeEmail: " . $isFake);
                $collection = $this->customerCollectionFactory->create();
                $collection->addAttributeToSelect('email');
                $collection->addAttributeToFilter('related_emails', ['like' => '%' . $recipientEmail . '%']);
                $collection->setPageSize(1);

                $originalCustomer = $collection->getFirstItem();

                if ($originalCustomer && $originalCustomer->getId()) {
                    $originalEmail = $originalCustomer->getEmail();
                    $this->logger->info("> originalEmail: " . $originalEmail);
                    $this->logger->info("Email redirected via related_emails: {$recipientEmail} → {$originalEmail}");
                    return $originalEmail;
                } else {
                    $this->logger->warning("No original customer found for fake email: {$recipientEmail}");
                }
            }
        } catch (NoSuchEntityException $e) {
            // Not a registered customer → keep original
        } catch (\Exception $e) {
            $this->logger->error("Error resolving original email for {$recipientEmail}: " . $e->getMessage());
        }

        return $recipientEmail;
    }
}
