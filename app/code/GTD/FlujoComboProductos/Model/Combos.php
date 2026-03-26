<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Model;

use Empresas\FlujoComboProductos\Api\Data\CombosInterface;
use Magento\Framework\Model\AbstractModel;

class Combos extends AbstractModel implements CombosInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Empresas\FlujoComboProductos\Model\ResourceModel\Combos::class);
    }

    /**
     * @inheritDoc
     */
    public function getCombosId()
    {
        return $this->getData(self::COMBOS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCombosId($combosId)
    {
        return $this->setData(self::COMBOS_ID, $combosId);
    }

    /**
     * @inheritDoc
     */
    public function getCombo()
    {
        return $this->getData(self::COMBO);
    }

    /**
     * @inheritDoc
     */
    public function setCombo($combo)
    {
        return $this->setData(self::COMBO, $combo);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSort()
    {
        return $this->getData(self::SORT);
    }

    /**
     * @inheritDoc
     */
    public function setSort($sort)
    {
        return $this->setData(self::SORT, $sort);
    }
}

