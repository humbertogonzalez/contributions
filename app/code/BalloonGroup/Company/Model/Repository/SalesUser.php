<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Model\Repository;

use BalloonGroup\Company\Api\SalesUserInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Model\UserFactory;
use Magento\User\Model\User;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Escaper;
use Magento\User\Helper\Data as UserHelper;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

class SalesUser implements SalesUserInterface
{
    /**
     * SalesUser constructor
     *
     * @param UserFactory $userFactory
     * @param User $user
     * @param UrlInterface $url
     * @param TransportBuilder $transportBuilder
     * @param Escaper $escaper
     * @param UserHelper $userHelper
     * @param CollectionFactory $collectionFactory
     * @param BookmarkSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        private UserFactory $userFactory,
        private User $user,
        private UrlInterface $url,
        private TransportBuilder $transportBuilder,
        private Escaper $escaper,
        private UserHelper $userHelper,
        private CollectionFactory $collectionFactory,
        private BookmarkSearchResultsInterfaceFactory $searchResultsFactory
    ) {

    }

    /**
     * @inheritDoc
     */
    public function save(
        string $username,
        string $firstname,
        string $lastname,
        string $email,
        string $password,
        string $roleIds,
        string $sellerId
    ): UserInterface {
        $adminUser = $this->userFactory->create();
        $adminUser->setData([
            'username' => $username,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password,
            'is_active' => 1,
            'seller_id' => $sellerId
        ]);
        $userRoles = [$roleIds];
        if (count($userRoles)) {
            $adminUser->setRoleId($userRoles[0]);
        }

        $adminUser->save($adminUser);
        $token = $this->userHelper->generateResetPasswordLinkToken();

        // Generate password reset link
        /** @var User $user */
        $user = $this->user->loadByUsername($username);
        $user->changeResetPasswordLinkToken($token);
        $user->sendNotificationEmailsIfRequired();

        return $this->getAdminUserByEmail($email);
    }

    /**
     * Load admin user collection by email.
     *
     * @param string $email
     * @return mixed
     */
    public function getAdminUserByEmail(string $email): mixed
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('email', $email);
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToSelect(['user_id','firstname','lastname', 'email', 'username','is_active', 'seller_id']);

        return $collection->getFirstItem();
    }

    /**
     * @inheritdoc
     */
    public function getById($userId): UserInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('user_id', $userId);
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToSelect(['user_id','firstname','lastname', 'email', 'username','is_active', 'seller_id']);

        return $collection->getFirstItem();
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $usersCollection */
        $usersCollection = $this->collectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $usersCollection);
        }

        $searchResults->setTotalCount($usersCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $usersCollection);
        }

        $usersCollection->setCurPage($searchCriteria->getCurrentPage());
        $usersCollection->setPageSize($searchCriteria->getPageSize());

        $users = [];
        /** @var SalesUserInterface $user */
        foreach ($usersCollection->getItems() as $user) {
            $users[] = $this->getById($user->getUserId());
        }

        return $searchResults->setItems($users);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $usersCollection
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $usersCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $usersCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $usersCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $usersCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $usersCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
