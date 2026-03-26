<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CombosRepositoryInterface
{

    /**
     * Save combos
     * @param \Empresas\FlujoComboProductos\Api\Data\CombosInterface $combos
     * @return \Empresas\FlujoComboProductos\Api\Data\CombosInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Empresas\FlujoComboProductos\Api\Data\CombosInterface $combos
    );

    /**
     * Retrieve combos
     * @param string $combosId
     * @return \Empresas\FlujoComboProductos\Api\Data\CombosInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($combosId);

    /**
     * Retrieve combos matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Empresas\FlujoComboProductos\Api\Data\CombosSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete combos
     * @param \Empresas\FlujoComboProductos\Api\Data\CombosInterface $combos
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Empresas\FlujoComboProductos\Api\Data\CombosInterface $combos
    );

    /**
     * Delete combos by ID
     * @param string $combosId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($combosId);
}

