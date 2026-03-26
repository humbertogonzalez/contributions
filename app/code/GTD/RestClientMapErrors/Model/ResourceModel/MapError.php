<?php

namespace Balloon\RestClientMapErrors\Model\ResourceModel;

use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MapError extends AbstractDb
{
    protected string $_eventPrefix = 'balloon_map_error_resource_model';

    protected function _construct()
    {
        $this->_init('balloon_rest_client_map_errors', MapErrorInterface::ENTITY_ID);
    }
}
