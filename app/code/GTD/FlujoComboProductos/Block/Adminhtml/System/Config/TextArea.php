<?php

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;


class TextArea extends \Magento\Framework\View\Element\Html\Select
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
        return '<textarea name="' . $this->getName() . '" id="' . $this->getInputId() . '" cols="30" rows="10"></textarea>';
    }
}
