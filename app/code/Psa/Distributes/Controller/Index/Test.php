<?php

declare(strict_types=1);

namespace BalloonGroup\Distributes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use BalloonGroup\PsaPayment\Model\Config\Config;

class Test extends Action
{
    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Config $config
     */
    public function __construct(
        Context $context,
        protected PageFactory $pageFactory,
        protected Config $config
    )
    {
        return parent::__construct($context);
    }

    public function execute()
    {
        $uri = "/api/v1/payment/order/";
        echo "> URL: " . $this->config->getGatewayUrl() . $uri;
        echo "<br><br>";
        $url = $this->config->getGatewayUrl() . $uri;
        echo "> URL 2: " . $url;
        echo "<br><br>";
        $params = [
            "publicKey" => $this->config->getPublicKey()
        ];

        print_r($params);

        echo "> Params: " . json_encode($params);
        echo "<br><br>";
        $url .= "?" . http_build_query($params);
        echo "> url: " . $url;
        echo "<br><br>";

        return $this->pageFactory->create();
    }
}
