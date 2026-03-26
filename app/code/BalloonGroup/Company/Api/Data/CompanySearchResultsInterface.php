<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Api\Data;

/**
 * Interface for company account search results.
 * @api
 */
interface CompanySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer groups list.
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface[]
     */
    public function getItems();

    /**
     * Set customer groups list.
     *
     * @param \Amasty\CompanyAccount\Api\Data\CompanyInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
