<?php

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;


class YesNo extends \Magento\Framework\View\Element\Html\Select
{

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        array                                   $data = []
    )
    {
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

        $array = [
            ['value' => 1, 'label' => __('Sí')],
            ['value' => 0, 'label' => __('No')]
        ];
        $html = '<select id="' . $this->getInputId() . '" name="' . $this->getName() . '">';
        foreach ($array as $option) {
            $html .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
        }
        $html .= '</select>';
        return $html;
    }
}
