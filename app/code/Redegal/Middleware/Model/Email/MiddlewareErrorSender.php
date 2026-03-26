<?php

namespace Redegal\Middleware\Model\Email;

use Psr\Log\LoggerInterface;
use Redegal\Middleware\Model\Email\Sender;
use Redegal\Middleware\Model\Helper\DateHelper;

class MiddlewareErrorSender extends Sender
{
    public function sendEmail($customMessage, $exceptionMessage)
    {
        $emails = explode(',', $this->getMiddlewareErrorEmails());
        $date = DateHelper::getNowInTimeZone('UTC')->sub(new \DateInterval('P1D'))->format('Y-m-d-h-m');

        foreach($emails ?? [] as $email)
        {
            $this->send(
                'middleware_error_email_template',
                [
                    'env' => $this->getCurrentEnvironment(),
                    'date' => $date,
                    'error' => $exceptionMessage,
                    'customMessage' => $customMessage
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
}
