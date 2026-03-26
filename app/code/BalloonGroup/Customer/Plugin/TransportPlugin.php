<?php
declare(strict_types=1);

namespace BalloonGroup\Customer\Plugin;

use Magento\Email\Model\Transport as EmailTransport;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Address;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\Address as MagentoAddress; // optional import for clarity

class TransportPlugin
{
    private CustomerRepositoryInterface $customerRepository;
    private CustomerCollectionFactory $customerCollectionFactory;
    private LoggerInterface $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerCollectionFactory $customerCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->logger = $logger;
    }

    public function aroundSendMessage(
        EmailTransport $subject,
        callable $proceed
    ) {
        $this->logger->info(">>> TransportPlugin::aroundSendMessage");
        try {
            $message = $subject->getMessage();

            if ($message instanceof EmailMessage) {
                $toEmails = $message->getTo()
                    ? implode(', ', array_map(function ($addr) {
                        return method_exists($addr, 'getEmail') ? $addr->getEmail() : (string)$addr;
                    }, $message->getTo()))
                    : 'no-to';

                $this->logger->info('TransportPlugin: aroundSendMessage CALLED - Recipients: ' . $toEmails);

                $this->convertToOriginalEmail($message);
            }
        } catch (\Exception $e) {
            $this->logger->error('EmailToOriginal Plugin Error: ' . $e->getMessage());
        }

        return $proceed();
    }

    private function convertToOriginalEmail(EmailMessage $message): void
    {
        $this->logger->info(">>> TransportPlugin::convertToOriginalEmail");
        $this->processAddressList($message, 'To');
        // $this->processAddressList($message, 'Cc');
        // $this->processAddressList($message, 'Bcc');
    }

    private function processAddressList(EmailMessage $message, string $type): void
    {
        $this->logger->info(">>> TransportPlugin::processAddressList");
        $this->logger->info($type);
        $methodGet = 'get' . $type;
        $methodSet = 'set' . $type;

        if (!method_exists($message, $methodGet) || !method_exists($message, $methodSet)) {
            $this->logger->info("> 1");
            return;
        }

        $addresses = $message->{$methodGet}();
        $this->logger->info(print_r($addresses, true));

        if (empty($addresses)) {
            $this->logger->info("> 2");
            return;
        }

        $newAddresses = [];
        foreach ($addresses as $address) {
            // Use getEmail() — works on both Symfony\Component\Mime\Address and Magento\Framework\Mail\Address
            $email = method_exists($address, 'getEmail')
                ? $address->getEmail()
                : (method_exists($address, 'getAddress') ? $address->getAddress() : (string)$address);

            $name = method_exists($address, 'getName') ? $address->getName() : '';

            $finalEmail = $this->getFinalRecipientEmail($email);
            $newAddresses[] = new Address($finalEmail, $name);
        }

        $message->{$methodSet}(...$newAddresses);
    }

    private function getFinalRecipientEmail(string $recipientEmail): string
    {
        $this->logger->info(">>> TransportPlugin::getFinalRecipientEmail");
        try {
            $this->logger->info("> RecipientEmail: " . $recipientEmail);
            $customer = $this->customerRepository->get($recipientEmail);

            $isFakeAttr = $customer->getCustomAttribute('is_fake_email');
            $isFake = $isFakeAttr && (int)$isFakeAttr->getValue() === 1;

            if ($isFake) {
                $collection = $this->customerCollectionFactory->create();
                $collection->addAttributeToSelect('email');
                $collection->addAttributeToFilter('related_emails', ['like' => '%' . $recipientEmail . '%']);
                $collection->setPageSize(1);

                $originalCustomer = $collection->getFirstItem();

                if ($originalCustomer && $originalCustomer->getId()) {
                    $originalEmail = $originalCustomer->getEmail();
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
