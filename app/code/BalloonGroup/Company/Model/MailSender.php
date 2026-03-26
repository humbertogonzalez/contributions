<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Model;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\MailSender as AmastyEmailSender;
use Magento\Framework\Exception\LocalizedException;

class MailSender extends AmastyEmailSender
{
    /**
     * @param int $companyId
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function sendCreditChangesByAdmin(int $companyId, array $data): void
    {
        return;
    }

    /**
     * @param int $companyId
     * @param array $data
     * @return void
     */
    public function sendOverdraftUsed(int $companyId, array $data): void
    {
        return;
    }

    /**
     * @param CompanyInterface $company
     * @return void
     */
    public function sendOverdraftChanged(CompanyInterface $company): void
    {
        return;
    }

    /**
     * @param int $companyId
     * @param array $data
     * @return void
     */
    public function sendOverdraftPenalty(int $companyId, array $data): void
    {
        return;
    }
}
