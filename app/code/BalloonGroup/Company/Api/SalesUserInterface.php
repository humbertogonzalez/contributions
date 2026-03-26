<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\User\Api\Data\UserInterface;

interface SalesUserInterface
{
    /**
     * Create a sales user.
     *
     * @param string $username
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $password
     * @param string $roleIds
     * @param string $sellerId
     * @return \Magento\User\Api\Data\UserInterface
     */
    public function save(
        string $username,
        string $firstname,
        string $lastname,
        string $email,
        string $password,
        string $roleIds,
        string $sellerId
    ): \Magento\User\Api\Data\UserInterface;

    /**
     * Get by id
     *
     * @param int $userId
     * @return \Magento\User\Api\Data\UserInterface
     * @throws NoSuchEntityException
     */
    public function getById($userId): \Magento\User\Api\Data\UserInterface;

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \BalloonGroup\Company\Api\Data\SalesUserSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
