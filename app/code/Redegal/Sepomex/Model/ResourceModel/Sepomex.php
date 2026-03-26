<?php


namespace Redegal\Sepomex\Model\ResourceModel;

class Sepomex extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('redegal_sepomex_sepomex', 'sepomex_id');
    }
}
