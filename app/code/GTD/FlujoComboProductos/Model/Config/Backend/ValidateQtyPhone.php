<?php

namespace Empresas\FlujoComboProductos\Model\Config\Backend;
class ValidateQtyPhone extends \Magento\Framework\App\Config\Value
{

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $arr = unserialize($value);
            $this->setValue($arr);
        }

    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        unset($value['__empty']);
        $arr = serialize($value);

        $this->setValue($arr);
    }
}
