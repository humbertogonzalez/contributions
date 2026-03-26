<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class UploadInvoice extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('dimagraf_invoice_management', 'entity_id');
    }
}

