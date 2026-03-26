<?php


namespace Redegal\Sepomex\Model;

use Redegal\Sepomex\Api\SepomexRepositoryInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Redegal\Sepomex\Api\Data\SepomexInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Redegal\Sepomex\Model\ResourceModel\Sepomex as ResourceSepomex;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Redegal\Sepomex\Model\ResourceModel\Sepomex\CollectionFactory as SepomexCollectionFactory;
use Redegal\Sepomex\Api\Data\SepomexSearchResultsInterfaceFactory;

class SepomexRepository implements sepomexRepositoryInterface
{

    protected $resource;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $sepomexFactory;

    protected $dataSepomexFactory;

    protected $searchResultsFactory;

    protected $sepomexCollectionFactory;

    protected $dataObjectHelper;


    /**
     * @param ResourceSepomex $resource
     * @param SepomexFactory $sepomexFactory
     * @param SepomexInterfaceFactory $dataSepomexFactory
     * @param SepomexCollectionFactory $sepomexCollectionFactory
     * @param SepomexSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSepomex $resource,
        SepomexFactory $sepomexFactory,
        SepomexInterfaceFactory $dataSepomexFactory,
        SepomexCollectionFactory $sepomexCollectionFactory,
        SepomexSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->sepomexFactory = $sepomexFactory;
        $this->sepomexCollectionFactory = $sepomexCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSepomexFactory = $dataSepomexFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
    ) {
        /* if (empty($sepomex->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $sepomex->setStoreId($storeId);
        } */
        try {
            $sepomex->getResource()->save($sepomex);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the sepomex: %1',
                $exception->getMessage()
            ));
        }
        return $sepomex;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($sepomexId)
    {
        $sepomex = $this->sepomexFactory->create();
        $sepomex->getResource()->load($sepomex, $sepomexId);
        if (!$sepomex->getId()) {
            throw new NoSuchEntityException(__('sepomex with id "%1" does not exist.', $sepomexId));
        }
        return $sepomex;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->sepomexCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Redegal\Sepomex\Api\Data\SepomexInterface $sepomex
    ) {
        try {
            $sepomex->getResource()->delete($sepomex);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the sepomex: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($sepomexId)
    {
        return $this->delete($this->getById($sepomexId));
    }
}
