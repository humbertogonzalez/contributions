<?php
namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ValidateQtyPhone extends AbstractFieldArray
{
    protected $_yesNoRenderer;

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('request_path', [
            'label' => __('Request Path'),
            'class' => 'required-entry',
        ]);

        $this->addColumn('enable', [
            'label' => __('Activar'),
            'class' => 'required-entry',
            'renderer' => $this->_getYesNoRenderer()
        ]);

        $this->addColumn('qty', [
            'label' => __('Cantidad'),
            'class' => 'required-entry',
        ]);

        $this->addColumn('paso', [
            'label' => 'Paso',
            'class' => 'required-entry',
        ]);

        $this->addColumn('rule', [
            'label' => 'Regla',
            'class' => 'required-entry',
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Nuevo');
    }

    /**
     * Get renderer for Yes/No dropdown
     */
    protected function _getYesNoRenderer()
    {
        if (!$this->_yesNoRenderer) {
            $this->_yesNoRenderer = $this->getLayout()->createBlock(
                \Empresas\FlujoComboProductos\Block\Adminhtml\System\Config\YesNo::class,
            '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_yesNoRenderer;
    }
}
