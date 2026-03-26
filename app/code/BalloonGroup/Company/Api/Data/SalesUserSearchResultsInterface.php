<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Api\Data;

/**
 * Interface for company account search results.
 * @api
 */
interface SalesUserSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get customer groups list.
     *
     * @return \Magento\User\Api\Data\UserInterface[]
     */
    public function getItems();

    /**
     * Set customer groups list.
     *
     * @param \Magento\User\Api\Data\UserInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
