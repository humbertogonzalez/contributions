<?php

namespace Balloon\RestClient\Sdk\Gtd;

use Balloon\RestClient\Api\Sdk\Gtd\ClientInterface;
use Balloon\RestClient\Service\CurlClient;
use Balloon\RestClient\Service\Gtd\Client as ServiceClient;
use Balloon\RestClient\Model\Config\ConfigProvider;
use Psr\Log\LoggerInterface;

class ClientEmulate extends BaseClient
{
    protected function sendRequest(string $endpoint, array $params = []): CurlClient {
        $this->urlBase = $this->configProvider->getUrlBase();
        $this->storeCode = $this->configProvider->getStoreCode();
        return parent::sendRequest(
            $endpoint,
            $params
        );
    }
}
