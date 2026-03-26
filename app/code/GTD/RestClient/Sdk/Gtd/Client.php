<?php

namespace Balloon\RestClient\Sdk\Gtd;

use Balloon\RestClient\Service\Gtd\Client as ServiceClient;
use Balloon\RestClient\Model\Config\ConfigProvider;
use Balloon\RestClientMapErrors\Resolve\MapError;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Balloon\RestClientErrorReport\Model\LogFactory;
use Balloon\RestClientErrorReport\Model\ResourceModel\Logs;
use Balloon\RestClient\Helper\Data;

class Client extends BaseClient
{
    public function __construct(
        ServiceClient $serviceClient,
        protected ConfigProvider $configProvider,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        LogFactory $logFactory,
        Logs $resourceLogs,
        MapError $mapErrorResolver,
        Data $helper
    )
    {
        $this->urlBase = $this->configProvider->getUrlBase() ?? '';
        $this->storeCode = $this->configProvider->getStoreCode();
        parent::__construct(
            $serviceClient,
            $this->configProvider,
            $logger,
            $serializer,
            $logFactory,
            $resourceLogs,
            $mapErrorResolver,
            $helper
        );
    }
}
