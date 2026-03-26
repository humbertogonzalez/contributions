<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Model\ResourceModel\Combos;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'combos_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Empresas\FlujoComboProductos\Model\Combos::class,
            \Empresas\FlujoComboProductos\Model\ResourceModel\Combos::class
        );
    }
}

