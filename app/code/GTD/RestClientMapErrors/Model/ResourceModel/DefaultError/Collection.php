<?php

namespace Balloon\RestClientMapErrors\Model\ResourceModel\DefaultError;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Balloon\RestClientMapErrors\Model\{
    DefaultError as Model,
    ResourceModel\DefaultError as Resource
};

class Collection extends AbstractCollection
{
    protected $_eventPrefix = 'balloon_default_error_collection';

    protected function _construct()
    {
        $this->_init(Model::class, Resource::class);
    }
}
