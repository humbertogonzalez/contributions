<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Model\ResourceModel\Ume;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use BalloonGroup\Ume\Model\Ume;
use BalloonGroup\Ume\Model\ResourceModel\Ume as UmeResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'ume_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            Ume::class,
            UmeResourceModel::class
        );
    }
}
