<?php

namespace Psa\PsaPayment\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config - Brief description of class objective
 * @package  Psa\PsaPayment\Model\Config
 */
class Config
{
    const CODE = 'atm_psa';

    public const PATH_ACTIVE = 'payment/atm_psa/active';
    public const TITLE = 'payment/atm_psa/title';
    public const ENVIROMENT = 'payment/atm_psa/environment';
    public const GATEWAY_URL = 'payment/atm_psa/gateway_url';
    public const CLIENT_ID = 'payment/atm_psa/client_id';
    public const PUBLIC_KEY = 'payment/atm_psa/public_key';
    public const PRIVATE_KEY = 'payment/atm_psa/private_key';
    public const SUCCESS_URL = 'atmpayment/checkout/success';
    public const FAILURE_URL = 'atmpayment/checkout/failure';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Get is active
     * @return bool
     */
    public function isActive(): bool
    {
        return (boolean)$this->scopeConfig->getValue(self::PATH_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get title
     * @return string
     */
    public function getTitle(): string
    {
        return $this->scopeConfig->getValue(self::TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Enviroment
     * @return string
     */
    public function getEnviroment(): string
    {
        return $this->scopeConfig->getValue(self::ENVIROMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Private Key
     * @return string
     */
    public function getGatewayUrl(): string
    {
        return $this->scopeConfig->getValue(self::GATEWAY_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Private Key
     * @return string
     */
    public function getClientId(): string
    {
        return $this->scopeConfig->getValue(self::CLIENT_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Private Key
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->scopeConfig->getValue(self::PUBLIC_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Private Key
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->scopeConfig->getValue(self::PRIVATE_KEY, ScopeInterface::SCOPE_STORE);
    }
}
