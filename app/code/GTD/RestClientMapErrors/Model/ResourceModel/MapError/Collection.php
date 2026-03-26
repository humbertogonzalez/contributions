<?php
namespace Balloon\RestClientMapErrors\Model\ResourceModel\MapError;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Balloon\RestClientMapErrors\Model\{
    MapError as Model,
    ResourceModel\MapError as Resource
};

class Collection extends AbstractCollection
{
    protected $_eventPrefix = 'balloon_map_error_collection';

    protected function _construct()
    {
        $this->_init(Model::class, Resource::class);
    }
}
