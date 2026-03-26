<?php

declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Empresas\FlujoComboProductos\Helper\Data;
class TipoProducto extends Select
{

    public function __construct(
        Context $context,
       readonly protected Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $options = $this->helper->getTipoProductoOptions();

        $html = '<select id="' . $this->getInputId() . '" name="' . $this->getName() . '">';
        if($options){
            foreach ($options as $option) {
                $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
            }
        }
        $html .= '</select>';
        return $html;
    }
}
