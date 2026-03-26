<?php

namespace Redegal\Middleware\Model\Repository;

use GuzzleHttp\Exception\ClientException;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareClientFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareRequestFactory;
use Redegal\Middleware\Model\Repository\Factory\MiddlewareTransformerFactory;
use Redegal\Middleware\Model\Repository\Request\InventoryBalanceOrderRestRequest;
use Redegal\Middleware\Security\Middleware\MiddlewareAuthManager;
use Redegal\Middleware\Model\Repository\SessionAwareMiddlewareRepository;
use Redegal\Middleware\Model\Client\Transformer\InventoryBalanceOrderRestTransformer;
use Psr\Log\LoggerInterface;

class InventoryBalanceOrderMiddlewareRepository extends SessionAwareMiddlewareRepository
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

    public function findByItems($items)
    {
        try {
            return $this->getInventoryBalanceOrder($items);
        } catch (\Exception $e) {
            if ($this->shouldUpdateCredentials($e)) {
                $this->authManager->updateCredentials();
                return $this->getInventoryBalanceOrder($items);
            }
            $this->logger->critical(json_encode($e->getMessage()));
            $this->logger->critical(json_encode($e->getTrace()));
            $this->logger->critical("[Inventory Balance Order Service] Request to inventory balance service fail");
            throw $e;
        }
    }

    private function getInventoryBalanceOrder($items)
    {
        $params = [];
        $params['client_options'] = $this->getCredentialsHeaders();
        $params['client_options']['connection'] = 'keep-alive';
        $params['client_options']['headers'] = [
            'content-type' => 'application/json',
        ];

        $params['body'] = $this->getInventoryBalanceOrderRequestBody($items);
        $params['headers'] = ['Content-Type' => 'application/json', 'Content-Length' => strlen(json_encode($params['body']))];

        $this->logger->debug(json_encode($params));

        return $this->invoke(
            $params,
            InventoryBalanceOrderRestRequest::class,
            InventoryBalanceOrderRestTransformer::class
        );
    }

    private function getInventoryBalanceOrderRequestBody($items)
    {
        $body = [
            'Header' => [
                'FinancialPartyDescription' => 'nutrisa',
                'InventoryBalanceDate' => '<dateTime>',
                'InventoryTransactionDate' => '<dateTime>',
                'OrderReceivedDate' => '<dateTime>'
            ]
        ];

        $productsInfo = [];
        foreach ($items as $item)
        {
            $productsInfo[] = [
                "ItemMasterID" => $item['sku'],
                "RequiredQuantity" => $item['qty']
            ];
        }

        $body['Header']['Detail'] = $productsInfo;

        return $body;
    }
}