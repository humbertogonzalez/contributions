<?php

namespace Balloon\RestClientMapErrors\Model\ResourceModel;

use Balloon\RestClientMapErrors\Api\Data\DefaultErrorInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DefaultError extends AbstractDb
{
    protected string $_eventPrefix = 'balloon_default_error_resource_model';

    protected function _construct()
    {
        $this->_init('balloon_rest_client_default_errors', DefaultErrorInterface::ENTITY_ID);
    }
}
