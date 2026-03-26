<?php

namespace Redegal\Middleware\Model\Email;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Redegal\Middleware\Model\Config\Config;
use Zend_Mime;
use Zend\Mime\Part;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Basic email sender
 */
class Sender
{

    public function __construct(
        TransportBuilder $transportBuilder,
        Config $config,
        LoggerInterface $logger,
        DirectoryList $dir,
        StoreManagerInterface $storeManager,
        File $fileDriver
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->config           = $config;
        $this->log              = $logger;
        $this->dir              = $dir;
        $this->storeManager     = $storeManager;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Send email
     *
     * @param string $templateId
     * @param array $data
     * @param array|string $from could be a department identifier string (string, contact...),
     * from customer if 'customer' string is passed or an array formatted as [name => email]
     * general department will be the default value if none is set
     * @param array $to can be a department string, 'sales' (which is default) or an array
     * formatted as [email => name]
     * @return void
     */
    public function send(string $templateId, array $data = [], $from = 'general', $to = 'sales', $cc = null, array $attachments = [])
    {
        $transport = $this->prepareTemplate($templateId, $data, $from, $to, $cc);

        foreach ($attachments as $attachment) {
            $transport = $this->addAttachment($transport, $attachment);
        }

        $transport->sendMessage();

        return $this;
    }

    /**
     * Prepare template with variables
     *
     * @param array $templateId
     * @param array $data
     * @param array|string $from
     * @param array $to
     * @return void
     */
    protected function prepareTemplate($templateId, $data, $from, $to, $cc)
    {
        $this->log->info('[Email] Sending email {email}', ['email' => $to]);

        try {
            $to = $this->getReceiverData($to);
            $from = $this->getSenderData($from);

            return $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => 'frontend',
                    'store' => $this->getStoreId() ?? \Magento\Store\Model\Store::DEFAULT_STORE_ID
                ])
                ->setTemplateVars($data)
                ->addTo($to)
                ->setFrom($from)
                ->getTransport();
        } catch (\Exception $e) {
            $this->log->error('[Email] Error while sending email "{email}"', ['email' => $templateId, 'exception' => $e]);
        }
    }

    /**
     * Get receiver (to) data
     *
     * If 'to' is an array without the key 'name', it's implicit it will follow the same
     * structure expected in /zendframework1/library/Zend/Mail
     *
     * @return array
     */
    protected function getReceiverData($to)
    {
        if (is_array($to) && !isset($to['name'])) {
            return $to;
        }

        return $this->getParticipantData($to);
    }

    /**
     * Get sender (from) data
     *
     * @return array|string
     */
    protected function getSenderData($from)
    {
        $from = $this->getParticipantData($from);
        $name = key($from);

        return ['name' => $name, 'email' => $from[$name]];
    }

    /**
     * Get email name and address from a participant
     *
     * @param array|string $participant if it is an array, it will be the pair name/address,
     * if it's a string, then it can be either an email address, a customer (the logged
     * in customer) or one of the store departments
     * @return array formatted as [name => email]
     */
    protected function getParticipantData($participant)
    {
        $isArray = is_array($participant);
        $hasKey = isset($participant['name']);
        $isEmail = !$isArray && strpos($participant, '@');

        if ($isArray) {
            $name  = $hasKey ? $participant['name']  : key($participant);
            $email = $hasKey ? $participant['email'] : $participant[$name];
        } elseif ($isEmail) {
            $name  = 0;
            $email = $participant;
        } else {
            $name  = $this->getStoreContactName($participant);
            $email = $this->getStoreContactEmail($participant);
        }

        return [$name => $email];
    }

    /**
     * Add an attachment to the message inside the transport builder.
     *
     * @param TransportInterface $transportBuilder
     * @param array $file Sanitized index from $_FILES
     * @return TransportInterface
     */
    protected function addAttachment(TransportInterface $transport, $filePath): TransportInterface
    {

        $part = new \Zend\Mime\Part($this->fileDriver->fileGetContents('/media/devnutrisa1.des2.net/current/prueba.txt'));
        $part->encoding = \Zend_Mime::ENCODING_BASE64;
        $part->type = \Zend_Mime::TYPE_OCTETSTREAM;
        $part->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $part->filename = basename($filePath);
    
        $transport->getMessage()->getBody()->addPart($part);

        return $transport;
    }



    /**
     * Get store email
     *
     * @return string
     */
    protected function getStoreContactEmail($department)
    {
        return $this->config->get(
            'trans_email/ident_'.$department.'/email'
        );
    }

    /**
     * Get store name
     *
     * @return string
     */
    protected function getStoreContactName($department)
    {
        return $this->config->get(
            'trans_email/ident_'.$department.'/name'
        );
    }

     /**
     * Get store code
     *
     * @return string
     */
    private function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
