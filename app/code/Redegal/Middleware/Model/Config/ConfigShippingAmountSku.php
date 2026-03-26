<?php

namespace Redegal\Middleware\Model\Config;

class ConfigShippingAmountSku extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Enable the "Add after" button or not
     *
     * @var bool
     */
    protected $_addAfter = true;

     /**
     * Label of add button
     *
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * Check if columns are defined, set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('carrier', array(
            'label' => __('Carrier'),
            'style' => 'width:200px',
            'class' => 'input-text required-entry'
        ));
        $this->addColumn('amount', array(
            'label' => __('Shipping amount'),
            'style' => 'width:200px',
            'class' => 'input-text required-entry'
        ));
        $this->addColumn('sku', array(
            'label' => __('Sku'),
            'style' => 'width:200px',
            'class' => 'input-text required-entry'
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
