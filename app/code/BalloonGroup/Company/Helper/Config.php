<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Helper;

use Amasty\RequestQuote\Helper\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    /**
     * Config constructor
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * Return CustomerGroups (companies) enabled for quote
     *
     * @return string
     */
    public function getAllowedForQuoteGroups(): string
    {
        return (string)$this->scopeConfig->getValue(
            Data::CONFIG_PATH_DISPLAY_FOR_GROUP,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get if specific CustomerGroup (company) is allowed to quote
     *
     * @param $customerGroupId
     * @return bool
     */
    public function getIsCompanyAllowedForQuote($customerGroupId)
    {
        return in_array($customerGroupId, explode(',', $this->getAllowedForQuoteGroups()));
    }
}
