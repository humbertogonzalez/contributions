<?php
namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class SuccessPage extends AbstractFieldArray
{
    protected $_comboRenderer;
    protected $_textAreaRenderer;
    protected $_yesNoRenderer;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
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
            'label' => __('Enable'),
            'class' => 'required-entry',
            'renderer' => $this->_getYesNoRenderer()
        ]);

        $this->addColumn('title', [
            'label' => __('Title'),
            'class' => 'required-entry',
            'renderer' => $this->_getTextAreaRenderer()
        ]);

        $this->addColumn('subtitle', [
            'label' => __('Sub Title'),
            'class' => 'required-entry',
            'renderer' => $this->_getTextAreaRenderer()
        ]);

        $this->addColumn('description', [
            'label' => __('Description'),
            'class' => 'required-entry',
            'renderer' => $this->_getTextAreaRenderer()
        ]);

        $this->addColumn('description2', [
            'label' => __('Description two'),
            'class' => 'required-entry',
            'renderer' => $this->_getTextAreaRenderer()
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
     * Get renderer for combos
     */
    protected function _getTextAreaRenderer()
    {
        if (!$this->_textAreaRenderer) {
            $this->_textAreaRenderer = $this->getLayout()->createBlock(
                \Empresas\FlujoComboProductos\Block\Adminhtml\System\Config\TextArea::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_textAreaRenderer;
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
