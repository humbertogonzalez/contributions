<?php

namespace Redegal\Middleware\Model\Config;

use \Redegal\Middleware\Model\Helper\FileHelper;

/**
 * Configuration Class
 */
class Config 
{
    const REGEX_BIND_VARIABLES = '/:([a-z]\w*)/';
    private $scopeConfig;
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve configuration from core_config by Store
     * @param  string $path  Path to de configuration
     * @param  string $store Store code
     * @return string        Configuration value
     */
    public function get($path, $store = null)
    {
        $path = FileHelper::joinPath($path);
        $store = is_int($store) ? $this->storeManager->getStore($store)->getCode() : $store;
        $store = $store ?? $this->storeCode();
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }


    /**
     * Parse array with config
     * TODO: Modify if shop is multistore
     * @param array $data
     * @return array
     */
    public function parse(array $data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->bind($value);
        }

        return $data;
    }

    private function bind($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback($this::REGEX_BIND_VARIABLES, function ($match) {
            return $this->get($match[1]);
        }, $value);
    }

    /**
     * Returns the current store (lang) code
     *
     * @return string
     */
    public function storeCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
