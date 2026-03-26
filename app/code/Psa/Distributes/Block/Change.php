<?php
namespace Psa\Distributes\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Psa\Distributes\Helper\Data;

class Change extends Template
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
            'canje' => true,
            'titleCanje' => $this->dataService->getConfig('distributes/textEdit/titleCanje'),
            'canjeTexto' => $this->dataService->getConfig('distributes/textEdit/canjeTexto'),
            'titleSerie' => $this->dataService->getConfig('distributes/textEdit/titleSerie'),
            'findSerie' => $this->dataService->getConfig('distributes/textEdit/findSerie'),
            'withoutDealer' => $this->dataService->getConfig('distributes/textEdit/withoutDealer'),
            'withoutSerie' => $this->dataService->getConfig('distributes/textEdit/withoutSerie')
        ];
    }

    public function getUlrSerial(): string {
        return $this->_urlBuilder->getUrl('distributes/change/productsbyserial', ['_secure' => true]);
    }

    public function getJsLayout()
    {
        $layout = json_decode(parent::getJsLayout(), true);
        $layout['components']['change_results']['url_serial'] = $this->getUlrSerial();
        $layout['components']['change_products']['title'] = $this->dataService->getConfig('distributes/textEdit/productCanje');
        $layout['components']['change_products']['url_add_product'] = $this->getUrl('amasty_cart/cart/add', ['_secure' => true]);
        $layout['components']['change_products']['discount'] = true;
        return json_encode($layout);
    }
}
