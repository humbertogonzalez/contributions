<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use BalloonGroup\UploadInvoiceApi\Model\UploadInvoice;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            UploadInvoice::class,
            ResourceModel::class
        );
    }
}

