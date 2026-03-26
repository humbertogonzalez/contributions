<?php

namespace Balloon\RestClient\Model\Config;

use Balloon\Core\Model\Config\ConfigProvider as CoreConfigResolver;
use \Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    const PARENT_CONFIG = 'web_services/';
    public function __construct(
        private readonly CoreConfigResolver $configProvider
    ) {
    }
    public function getUrlBase()
    {
        $value = 'config/url_base';
        return $this->configProvider->getValue($this::PARENT_CONFIG. $value);
    }
    public function getStoreCode()
    {
        $value = 'config/store_code';
        return $this->configProvider->getValue($this::PARENT_CONFIG. $value);
    }
    public function getEnabledLogs()
    {
        $value = 'config/enabled_logs';
        return $this->configProvider->getValue($this::PARENT_CONFIG. $value);
    }
    public function getEnabledLogsOnDB()
    {
        $value = 'config/enabled_logs_db';
        return $this->configProvider->getValue($this::PARENT_CONFIG. $value);
    }
    public function getEnabledServices()
    {
        $value = 'config/enabled';
        return $this->configProvider->getValue($this::PARENT_CONFIG. $value);
    }
}
