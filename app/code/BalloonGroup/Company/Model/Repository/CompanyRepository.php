<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Model\Repository;

use BalloonGroup\Company\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\ResourceModel\Company\Collection;
use Amasty\CompanyAccount\Model\ResourceModel\Company\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Amasty\CompanyAccount\Model\Repository\CompanyRepository as AmastyCompany;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompanyRepository implements CompanyRepositoryInterface
{
    /**
     * CompanyRepository constructor
     *
     * @param BookmarkSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $companyCollectionFactory
     * @param AmastyCompany $amastyCompany
     */
    public function __construct(
        private BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        private CollectionFactory $companyCollectionFactory,
        private AmastyCompany $amastyCompany
    ) {

    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $companyCollection */
        $companyCollection = $this->companyCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $companyCollection);
        }

        $searchResults->setTotalCount($companyCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $companyCollection);
        }

        $companyCollection->setCurPage($searchCriteria->getCurrentPage());
        $companyCollection->setPageSize($searchCriteria->getPageSize());

        $companys = [];
        /** @var CompanyInterface $company */
        foreach ($companyCollection->getItems() as $company) {
            $data = $this->amastyCompany->getById($company->getCompanyId(), true);
            $companys[] = $data;
        }

        return $searchResults->setItems($companys);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $companyCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $companyCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $companyCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $companyCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $companyCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $companyCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
