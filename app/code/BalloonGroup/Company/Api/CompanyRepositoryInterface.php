<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Api;

/**
 * @api
 */
interface CompanyRepositoryInterface
{
    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \BalloonGroup\Company\Api\Data\CompanySearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
