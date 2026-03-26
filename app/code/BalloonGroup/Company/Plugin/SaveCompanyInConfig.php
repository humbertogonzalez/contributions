<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Plugin;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Repository\CompanyRepository;
use BalloonGroup\Company\Helper\Config;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Amasty\RequestQuote\Helper\Data;
use Amasty\RequestQuote\Model\Source\Group;

class SaveCompanyInConfig
{
    /**
     * SaveCompanyInConfig constructor
     *
     * @param Config $config
     * @param ConfigInterface $configInterface
     * @param Group $customerGroupSource
     */
    public function __construct(
        private Config $config,
        private ConfigInterface $configInterface,
        private Group $customerGroupSource
    )
    {
    }

    /**
     * @param CompanyRepository $subject
     * @param CompanyInterface $result
     * @param CompanyInterface $company
     * @return CompanyInterface
     */
    public function afterSave(
        CompanyRepository $subject,
        CompanyInterface $result,
        CompanyInterface $company
    ): CompanyInterface {
        $isCompanyAllowed = $this->config->getIsCompanyAllowedForQuote($company->getCustomerGroupId());

        if (!$isCompanyAllowed) {
            $currentValue = $this->config->getAllowedForQuoteGroups();
            $currentValueArray = $currentValue ? explode(',', $currentValue) : [];
            $currentValueArray[] = $company->getCustomerGroupId();
            $allOptions = array_column($this->customerGroupSource->toOptionArray(), 'value');
            $currentValueArray = array_intersect($allOptions, $currentValueArray);
            $updatedValue = implode(',', $currentValueArray);
            $this->configInterface->saveConfig(Data::CONFIG_PATH_DISPLAY_FOR_GROUP,$updatedValue);
        }

        return $result;
    }

}
