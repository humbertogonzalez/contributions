<?php

namespace Empresas\FlujoComboProductos\Model\Config\Backend;
class UrlCombos extends \Magento\Framework\App\Config\Value
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
            foreach ($arr as &$row) {
                if (!isset($row['tipo_producto'])) {
                    $row['tipo_producto'] = '';
                }
                if (!isset($row['title_direction'])) {
                    $row['title_direction'] = '';
                }
            }
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
