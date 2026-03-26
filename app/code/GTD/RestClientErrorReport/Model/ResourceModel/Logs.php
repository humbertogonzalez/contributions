<?php

namespace Balloon\RestClientErrorReport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Logs extends AbstractDb
{
    const TABLE_NAME = 'balloon_rest_request_report';

    public function _construct(): void
    {
        $this->_init(static::TABLE_NAME, 'entity_id');
    }
}
