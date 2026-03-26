<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Combos extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('empresas_flujocomboproductos_combos', 'combos_id');
    }
}

