<?php

namespace Balloon\RestClient\Service\Gtd;
use Balloon\RestClient\Service\CurlClient;
use Magento\Framework\Serialize\SerializerInterface;

final class Client
{
    private const HEADERS = [
        "Content-Type" => "application/json"
    ];

     public function __construct(
         private readonly CurlClient $_curl,
         private readonly SerializerInterface $serializer
     )
     {
         $this->_curl->setHeaders(self::HEADERS);
         $this->_curl->setTimeout( 120);
     }

    public function sendRequest(
        string $url,
        array $params = [],
        string $method = 'post'
    ): CurlClient
    {
        $this->_curl->setHeaders(self::HEADERS);
        if ($method === 'post') {
            $serializeParams = $this->serializer->serialize($params);
            $this->_curl->post(
                $url,
                $serializeParams
            );

        } else {
            $this->_curl->get(
                $url
            );
        }
        return $this->_curl;
     }
}
