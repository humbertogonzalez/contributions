<?php

namespace Psa\PsaPayment\Model\Ui;

use Psa\PsaPayment\Model\Config\Config;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider - Brief description of class objective
 * @package  Psa\PsaPayment\Model\Ui
 */
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    public const CODE = 'atm_psa';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Repository
     */
    private $assetRepo;

    public function __construct(
        Config $config,
        Repository $assetRepo
    ) {
        $this->config = $config;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'title' => $this->config->getTitle(),
                    'logo' => $this->assetRepo->getUrl('BalloonGroup_PsaPayment::images/logo.png'),
                    'actionUrl' => '/atmpayment/checkout/pay',
                ]
            ]
        ];
    }
}
