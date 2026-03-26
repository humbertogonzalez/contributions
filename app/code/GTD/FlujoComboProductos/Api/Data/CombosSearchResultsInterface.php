<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Api\Data;

interface CombosSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get combos list.
     * @return \Empresas\FlujoComboProductos\Api\Data\CombosInterface[]
     */
    public function getItems();

    /**
     * Set sku_product list.
     * @param \Empresas\FlujoComboProductos\Api\Data\CombosInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

