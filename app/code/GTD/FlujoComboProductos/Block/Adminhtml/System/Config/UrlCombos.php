<?php
namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class UrlCombos extends AbstractFieldArray
{
    protected $_comboRenderer;
    protected $_emailTemplatesRenderer;
    protected $_yesNoRenderer;
    protected $_tipoRenderer;

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('request_path', [
            'label' => __('Request Path'),
            'class' => 'required-entry',
        ]);

        $this->addColumn('combos', [
            'label' => __('Combos'),
            'class' => 'required-entry',
            'renderer' => $this->_getComboRenderer()
        ]);

        $this->addColumn('tipo_producto', [
            'label' => __('Tipo de producto'),
            'class' => 'required-entry',
            'renderer' => $this->_getTipoRenderer()
        ]);

        $this->addColumn('email_enable', [
            'label' => __('Activar Email'),
            'renderer' => $this->_getYesNoRenderer()
        ]);

        $this->addColumn('emails_ejecutivo', [
            'label' => 'Correos Ejecutivo',
            'class' => 'required-entry',
        ]);

        $this->addColumn('email_template', [
            'label' => __('Email Template'),
            'renderer' => $this->_getEmailTemplatesRenderer()
        ]);

        $this->addColumn('title_direction', [
            'label' => __('Titulo dirección checkout'),
        ]);

        $this->addColumn('question_answer', [
            'label' => __('Preguntas frecuentes'),
            'renderer' => $this->_getYesNoRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Nuevo');
    }

    /**
     * Get renderer for combos
     */
    protected function _getComboRenderer()
    {
        if (!$this->_comboRenderer) {
            $this->_comboRenderer = $this->getLayout()->createBlock(
                \Empresas\FlujoComboProductos\Block\Adminhtml\System\Config\Options::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_comboRenderer;
    }

    /**
     * Get renderer for email templates
     */
    protected function _getEmailTemplatesRenderer()
    {
        if (!$this->_emailTemplatesRenderer) {
            $this->_emailTemplatesRenderer = $this->getLayout()->createBlock(
                \Empresas\FlujoComboProductos\Block\Adminhtml\System\Config\EmailTemplates::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_emailTemplatesRenderer;
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

    protected function _getTipoRenderer()
    {
        if (!$this->_tipoRenderer) {
            $this->_tipoRenderer = $this->getLayout()->createBlock(
                \Empresas\FlujoComboProductos\Block\Adminhtml\System\Config\TipoProducto::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_tipoRenderer;
    }
}
