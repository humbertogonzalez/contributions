<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Api;

use BalloonGroup\Ume\Api\Data\UmeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use BalloonGroup\Ume\Api\Data\UmeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface UmeRepositoryInterface
{
    /**
     * Save Ume
     *
     * @param UmeInterface $ume
     * @return \BalloonGroup\Ume\Api\Data\UmeInterface
     * @throws LocalizedException
     */
    public function save(UmeInterface $ume): \BalloonGroup\Ume\Api\Data\UmeInterface;

    /**
     * Retrieve Ume
     *
     * @param string $umeId
     * @return \BalloonGroup\Ume\Api\Data\UmeInterface
     * @throws LocalizedException
     */
    public function get(string $umeId): \BalloonGroup\Ume\Api\Data\UmeInterface;

    /**
     * Retrieve Ume matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \BalloonGroup\Ume\Api\Data\UmeSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): \BalloonGroup\Ume\Api\Data\UmeSearchResultsInterface;

    /**
     * Delete Ume
     *
     * @param \BalloonGroup\Ume\Api\Data\UmeInterface $ume
     * @return bool
     * @throws LocalizedException
     */
    public function delete(\BalloonGroup\Ume\Api\Data\UmeInterface $ume): bool;

    /**
     * Delete Ume by ID
     *
     * @param string $umeId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $umeId): bool;
}
