<?php
namespace BalloonGroup\PsaSpareParts\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use BalloonGroup\Distributes\Helper\Data;

class Repuestos extends Template
{
    public function __construct(
        Context $context,
        protected readonly Data $dataService,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getDataText(): array
    {
        return [
            'canje' => false,
            'titleCanje' => $this->dataService->getConfig('psa_repuestos/general/title_repuestos'),
            'canjeTexto' => $this->dataService->getConfig('psa_repuestos/general/repuestosTexto'),
            'titleSerie' => $this->dataService->getConfig('psa_repuestos/general/titleSerie'),
            'findSerie' => $this->dataService->getConfig('psa_repuestos/general/findSerie')
        ];
    }

    public function getUlrSerial(): string {
        return $this->_urlBuilder->getUrl('repuestos/productsbyserial/index', ['_secure' => true]);
    }

    public function getJsLayout()
    {
        $layout = json_decode(parent::getJsLayout(), true);
        $layout['components']['change_results']['url_serial'] = $this->getUlrSerial();
        $layout['components']['change_products']['title'] = $this->dataService->getConfig('psa_repuestos/general/productRepuestos');
        $layout['components']['change_products']['url_add_product'] = $this->getUrl('amasty_cart/cart/add', ['_secure' => true]);
        $layout['components']['change_products']['discount'] = false;
        return json_encode($layout);
    }
}
