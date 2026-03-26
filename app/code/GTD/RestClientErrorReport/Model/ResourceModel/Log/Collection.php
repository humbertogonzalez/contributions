<?php

namespace Balloon\RestClientErrorReport\Model\ResourceModel\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'rest_logs_collection_collection';
    protected $_eventObject = 'logs_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Balloon\RestClientErrorReport\Model\Log', 'Balloon\RestClientErrorReport\Model\ResourceModel\logs');
    }

}
