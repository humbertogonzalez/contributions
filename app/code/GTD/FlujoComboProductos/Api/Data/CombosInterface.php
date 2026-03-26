<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Api\Data;

interface CombosInterface
{

    const SORT = 'sort';
    const COMBO = 'combo';
    const COMBOS_ID = 'combos_id';
    const NAME = 'name';

    /**
     * Get combos_id
     * @return string|null
     */
    public function getCombosId();

    /**
     * Set combos_id
     * @param string $combosId
     * @return \Empresas\FlujoComboProductos\Combos\Api\Data\CombosInterface
     */
    public function setCombosId($combosId);


    /**
     * Get combo
     * @return string|null
     */
    public function getCombo();

    /**
     * Set combo
     * @param string $combo
     * @return \Empresas\FlujoComboProductos\Combos\Api\Data\CombosInterface
     */
    public function setCombo($combo);

    /**
     * Get combo
     * @return string|null
     */
    public function getName();

    /**
     * Set combo
     * @param string $combo
     * @return \Empresas\FlujoComboProductos\Combos\Api\Data\CombosInterface
     */
    public function setName($name);

    /**
     * Get sort
     * @return string|null
     */
    public function getSort();

    /**
     * Set sort
     * @param string $sort
     * @return \Empresas\FlujoComboProductos\Combos\Api\Data\CombosInterface
     */
    public function setSort($sort);
}

