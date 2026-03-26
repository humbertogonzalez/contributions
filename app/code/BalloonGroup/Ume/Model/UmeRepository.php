<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Model;

use BalloonGroup\Ume\Api\Data\UmeInterface;
use BalloonGroup\Ume\Api\Data\UmeInterfaceFactory;
use BalloonGroup\Ume\Api\Data\UmeSearchResultsInterface;
use BalloonGroup\Ume\Api\Data\UmeSearchResultsInterfaceFactory;
use BalloonGroup\Ume\Api\UmeRepositoryInterface;
use BalloonGroup\Ume\Model\ResourceModel\Ume as ResourceUme;
use BalloonGroup\Ume\Model\ResourceModel\Ume\CollectionFactory as UmeCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class UmeRepository implements UmeRepositoryInterface
{
    /**
     * UmeRepository constructor
     *
     * @param ResourceUme $resource
     * @param UmeInterfaceFactory $umeFactory
     * @param UmeCollectionFactory $umeCollectionFactory
     * @param UmeSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        private readonly ResourceUme $resource,
        private readonly UmeInterfaceFactory $umeFactory,
        private readonly UmeCollectionFactory $umeCollectionFactory,
        private readonly UmeSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {

    }

    /**
     * Save
     *
     * @param UmeInterface $ume
     * @return UmeInterface
     * @throws CouldNotSaveException
     */
    public function save(UmeInterface $ume): UmeInterface
    {
        try {
            $this->resource->save($ume);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ume: %1',
                $exception->getMessage()
            ));
        }
        return $ume;
    }

    /**
     * Get
     *
     * @param string $umeId
     * @return UmeInterface
     * @throws NoSuchEntityException
     */
    public function get(string $umeId): UmeInterface
    {
        $ume = $this->umeFactory->create();
        $this->resource->load($ume, $umeId);
        if (!$ume->getId()) {
            throw new NoSuchEntityException(__('Ume with id "%1" does not exist.', $umeId));
        }
        return $ume;
    }

    /**
     * Get list
     *
     * @param SearchCriteriaInterface $criteria
     * @return UmeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria): UmeSearchResultsInterface {
        $collection = $this->umeCollectionFactory->create();
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
     * Delete
     *
     * @param UmeInterface $ume
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(UmeInterface $ume): bool
    {
        try {
            $umeModel = $this->umeFactory->create();
            $this->resource->load($umeModel, $ume->getUmeId());
            $this->resource->delete($umeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Ume: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete
     *
     * @param string $umeId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(string $umeId): bool
    {
        return $this->delete($this->get($umeId));
    }
}
