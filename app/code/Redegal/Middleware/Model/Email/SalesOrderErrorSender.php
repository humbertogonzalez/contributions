<?php

namespace Redegal\Middleware\Model\Email;

use Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Email\Sender;
use Redegal\Middleware\Model\Helper\DateHelper;

class SalesOrderErrorSender extends Sender
{
    const LOG_FOLDER_PATH = 'orders';

    public function sendEmail($order, $message = '')
    {
        $emails = explode(',', $this->getMiddlewareErrorEmails());
        $date = DateHelper::getNowInTimeZone('UTC')->sub(new \DateInterval('P1D'))->format('Y-m-d-h-m');
        $fileContent = $this->getAttachmentContent($order, $message);

        foreach($emails ?? [] as $email)
        {
            $this->send(
                'sales_order_error_email_template',
                [
                    'env' => $this->getCurrentEnvironment(),
                    'orderId' => $order->getIncrementId(),
                    'customerEmail' => $order->getCustomerEmail(),
                    'date' => $date,
                    'errorMessage' => $this->getErrorMessage($fileContent),
                    'fileContent' => $fileContent
                ],
                'general',
                trim($email),
                null
            );
        }
    }

    /**
     * Get current environment
     *
     * @return string
     */
    protected function getCurrentEnvironment()
    {
        $magentoEnv = $this->config->get('middleware/environment/magento');
        return $magentoEnv;
    }

    /**
     * Get store email
     *
     * @return string
     */
    protected function getMiddlewareErrorEmails()
    {
        $env = $this->config->get('middleware/environment/env');
        return trim($this->config->get('middleware/'.$env.'/report_error_email'));
    }

    protected function getAttachmentContent($order, $message)
    {
        $orderId = $order->getIncrementId();
        $filesPath = $this->dir->getPath('log').'/'.static::LOG_FOLDER_PATH.'/';

        $files = glob($filesPath ."*".$orderId."-sales-order.json");
        $index = count($files) - 1;

        return file_get_contents($files[$index]);
    }

    protected function getErrorMessage($fileContent)
    {
        $content = json_decode($fileContent, true);
        $text = $content['response']['body']['error']['message'] ?? $content['response']['body']['message'] ?? $message ?? "No se pudo obtener el mensaje del error.";

        if(!empty($content['response']['body']['message']) && is_array($content['response']['body']['message'])) {
            $text = implode(",", $content['response']['body']['message']);
        }

        return $text;
    }
}
