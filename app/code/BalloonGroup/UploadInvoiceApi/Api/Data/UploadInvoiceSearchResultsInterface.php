<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface UploadInvoiceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get invoice list
     *
     * @return \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface[]
     */
    public function getItems();

    /**
     * Set invoice list.
     *
     * @param \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

