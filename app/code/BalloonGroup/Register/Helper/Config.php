<?php

declare(strict_types=1);

namespace BalloonGroup\Register\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    /** @var string */
    public const XML_PATH_FORM_RECEIVER = "smtp/configuration_option/username";

    /**
     * Config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Get receiver email
     *
     * @return string
     */
    public function getRegisterFormReceiver(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_FORM_RECEIVER);
    }
}
