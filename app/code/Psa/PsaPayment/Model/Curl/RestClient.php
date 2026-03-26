<?php

namespace BalloonGroup\PsaPayment\Model\Curl;

use BalloonGroup\PsaPayment\Model\Config\Config;
use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * Class RestClient - Brief description of class objective
 * @package  BalloonGroup\PsaPayment\Model\Curl
 */
class RestClient
{
    private Curl $curl;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(
        Curl $curl,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param  $uri
     * @param  $method
     * @param  $content_type
     * @param $data
     * @return array
     * @throws Exception
     */
    private function exec($uri, $method, $content_type, $data)
    {
        if (!extension_loaded("curl")) {
            throw new Exception("cURL extension not found. You need to enable cURL in your php.ini or another configuration you have.");
        }
        $this->logger->info("URL: " . $this->config->getGatewayUrl() . $uri);
        $url = $this->config->getGatewayUrl() . $uri;

        $params = [
            "sign" => $this->getSign(),
            "publicKey" => $this->config->getPublicKey()
        ];

        $this->logger->info("Params: " . json_encode($params));
        $url .= "?" . http_build_query($params);

        $this->logger->info("URL with params: " . $url);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_CUSTOMREQUEST, $method);
        $this->curl->setOption(CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $this->curl->addHeader("Content-Type", $content_type);
        $this->curl->addHeader("x-request-id", uniqid());

        if ($method == 'POST') {
            if ($data) {
                if ($content_type == "application/json") {
                    if (gettype($data) == "string") {
                        json_decode($data, true);
                    } else {
                        $data = json_encode($data);
                    }

                    if (function_exists('json_last_error')) {
                        $json_error = json_last_error();
                        if ($json_error != JSON_ERROR_NONE) {
                            throw new Exception("JSON Error [{$json_error}] - Data: {$data}");
                        }
                    }
                }
                $this->logger->info("Antes post");
                $this->curl->post($url, $data);
            }
        } else {
            $this->curl->get($url);
        }
        $this->logger->info("REQUEST: " . $data);
        $api_result = $this->curl->getBody();
        $api_http_code = $this->curl->getStatus();
        //$this->logger->info("PSA Payment API response: " . $api_result);
        //$this->logger->info("PSA Payment API response code: " . $api_http_code);
        return [
            "status" => $api_http_code,
            "response" => json_decode($api_result, true)
        ];
    }

    /**
     * @param $uri
     * @param $content_type
     *
     * @return array
     * @throws Exception
     */
    public function get($uri, $content_type = "application/json")
    {
        return $this->exec($uri, "GET", $content_type, null);
    }

    /**
     * @param $uri
     * @param $data
     * @param $content_type
     *
     * @return array
     * @throws Exception
     */
    public function post($uri, $data, $content_type = "application/json")
    {
        return $this->exec($uri, "POST", $content_type, $data);
    }

    /**
     * @return string
     */
    private function getNonce(): string
    {
        $period = 60;
        $now = time();
        return intdiv($now, $period);
    }

    /**
     * @return string
     */
    public function getSign(): string
    {
        $string = $this->config->getClientId() . $this->getNonce();
        return hash_hmac('sha512', $string, $this->config->getPrivateKey());
    }
}
