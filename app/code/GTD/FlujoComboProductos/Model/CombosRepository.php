<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Model;

use Empresas\FlujoComboProductos\Api\CombosRepositoryInterface;
use Empresas\FlujoComboProductos\Api\Data\CombosInterface;
use Empresas\FlujoComboProductos\Api\Data\CombosInterfaceFactory;
use Empresas\FlujoComboProductos\Api\Data\CombosSearchResultsInterfaceFactory;
use Empresas\FlujoComboProductos\Model\ResourceModel\Combos as ResourceCombos;
use Empresas\FlujoComboProductos\Model\ResourceModel\Combos\CollectionFactory as CombosCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class CombosRepository implements CombosRepositoryInterface
{

    /**
     * @var ResourceCombos
     */
    protected $resource;

    /**
     * @var CombosInterfaceFactory
     */
    protected $combosFactory;

    /**
     * @var Combos
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var CombosCollectionFactory
     */
    protected $combosCollectionFactory;


    /**
     * @param ResourceCombos $resource
     * @param CombosInterfaceFactory $combosFactory
     * @param CombosCollectionFactory $combosCollectionFactory
     * @param CombosSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCombos $resource,
        CombosInterfaceFactory $combosFactory,
        CombosCollectionFactory $combosCollectionFactory,
        CombosSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->combosFactory = $combosFactory;
        $this->combosCollectionFactory = $combosCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(CombosInterface $combos)
    {
        try {
            $this->resource->save($combos);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the combos: %1',
                $exception->getMessage()
            ));
        }
        return $combos;
    }

    /**
     * @inheritDoc
     */
    public function get($combosId)
    {
        $combos = $this->combosFactory->create();
        $this->resource->load($combos, $combosId);
        if (!$combos->getId()) {
            throw new NoSuchEntityException(__('combos with id "%1" does not exist.', $combosId));
        }
        return $combos;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->combosCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CombosInterface $combos)
    {
        try {
            $combosModel = $this->combosFactory->create();
            $this->resource->load($combosModel, $combos->getCombosId());
            $this->resource->delete($combosModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the combos: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($combosId)
    {
        return $this->delete($this->get($combosId));
    }
}

