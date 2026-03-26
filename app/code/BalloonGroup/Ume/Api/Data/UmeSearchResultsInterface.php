<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use BalloonGroup\Ume\Api\Data\UmeInterface;

interface UmeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Ume list.
     *
     * @return \BalloonGroup\Ume\Api\Data\UmeInterface[]
     */
    public function getItems(): array;

    /**
     * Set sku list.
     *
     * @param \BalloonGroup\Ume\Api\Data\UmeInterface[] $items
     * @return UmeSearchResultsInterface
     */
    public function setItems(array $items): UmeSearchResultsInterface;
}
