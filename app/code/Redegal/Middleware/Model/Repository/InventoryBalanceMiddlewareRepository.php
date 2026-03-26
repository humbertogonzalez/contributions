<?php

namespace Redegal\Middleware\Model\Repository;

use GuzzleHttp\Exception\ClientException;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Redegal\Middleware\Model\Repository\Request\InventoryBalanceRestRequest;
use Redegal\Middleware\Model\Client\Transformer\InventoryBalanceTransformer;
use Redegal\Middleware\Security\Middleware\MiddlewareAuthManager;
use Redegal\Middleware\Model\Repository\SessionAwareMiddlewareRepository;
use Redegal\Middleware\Model\Client\Transformer\InventoryBalanceRestTransformer;
use Psr\Log\LoggerInterface;

class InventoryBalanceMiddlewareRepository extends SessionAwareMiddlewareRepository
{

    public function __construct(
        LoggerInterface $logger,
        MiddlewareClientFactory $clientFactory,
        MiddlewareRequestFactory $requestFactory,
        MiddlewareTransformerFactory $transformerFactory,
        MiddlewareAuthManager $authManager,
        array $params = []
    ) {
        parent::__construct($logger, $clientFactory, $requestFactory, $transformerFactory, $authManager, $params);
    }

    public function findAll()
    {
        try {
            return $this->getInventoryBalance();
        } catch (\Exception $e) {
            if ($this->shouldUpdateCredentials($e)) {
                $this->authManager->updateCredentials();
                return $this->getInventoryBalance();
            }
            $this->logger->critical(json_encode($e->getMessage()));
            $this->logger->critical(json_encode($e->getTrace()));
            $this->logger->critical("[Inventory Balance Service] Request to inventory balance service fail");
            throw $e;
        }
    }

    private function getInventoryBalance()
    {
        $params = [];
        $params['client_options'] = $this->getCredentialsHeaders();
        $params['client_options']['connection'] = 'keep-alive';
        $params['client_options']['headers'] = [
            'content-type' => 'application/json',
        ];

        $params['body'] = $this->getInventoryBalanceRequestBody();
        $params['headers'] = ['Content-Type' => 'application/json', 'Content-Length' => strlen(json_encode($params['body']))];

        $this->logger->debug(json_encode($params));

        return $this->invoke(
            $params,
            InventoryBalanceRestRequest::class,
            InventoryBalanceRestTransformer::class
        );
    }

    private function getInventoryBalanceRequestBody()
    {
        return [
            'Header' => [
                'FinancialPartyDescription' => 'nutrisa',
                'InventoryBalanceDate' => '<dateTime>',
                'InventoryTransactionDate' => '<dateTime>'
            ]
        ];
    }
}
