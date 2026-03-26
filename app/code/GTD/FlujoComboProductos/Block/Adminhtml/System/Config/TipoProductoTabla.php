<?php

declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class TipoProductoTabla extends AbstractFieldArray
{
    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('tipo_producto', [
            'label' => __('Product Type'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('New');
    }
}
