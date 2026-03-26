<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use Magento\Customer\Model\EmailNotificationInterface;

class EmailNotificationPlugin
{
    /**
     * @param EmailNotification $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int|null $storeId
     * @param string|null $sendemailStoreId
     * @return void
     */
    public function aroundNewAccount(
        EmailNotification $subject,
        callable $proceed,
        CustomerInterface $customer,
        $type = EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ) {
        return null;
    }

}
