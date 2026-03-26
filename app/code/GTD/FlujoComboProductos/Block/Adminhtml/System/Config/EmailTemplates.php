<?php

namespace Empresas\FlujoComboProductos\Block\Adminhtml\System\Config;

use Magento\Framework\View\Element\Html\Select;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory as EmailConfig;

class EmailTemplates extends Select
{
    protected $emailConfig;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        EmailConfig                             $emailConfig,
        array                                   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->emailConfig = $emailConfig;
    }

    /**
     * Set Input Name
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render HTML
     */
    protected function _toHtml()
    {
        $templates = $this->emailConfig->create();

        $html = '<select id="' . $this->getInputId() . '" name="' . $this->getName() . '">';
        foreach ($templates as $templateName) {

            $label = $templateName['template_code'];

            if (is_object($label)) {
                $label = (string) $label;
            }

            if (isset($templateName['template_id'])) {
                $html .= '<option value="' . $templateName['template_id'] . '">' . $label . '</option>';
            }
        }

        $html .= '</select>';
        return $html;

    }
}
