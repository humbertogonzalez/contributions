<?php


namespace Redegal\Sepomex\Model\ResourceModel\Sepomex;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Redegal\Sepomex\Model\Sepomex',
            'Redegal\Sepomex\Model\ResourceModel\Sepomex'
        );
    }
}
