<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface UploadInvoiceRepositoryInterface
{
    /**
     * Save Invoice
     *
     * @param \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface $uploadInvoice
     * @return \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface
     * @throws LocalizedException
     */
    public function save(
        \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface $uploadInvoice
    ): \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface;

    /**
     * Retrieve invoice
     *
     * @param string $uploadInvoiceId
     * @return \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface
     * @throws LocalizedException
     */
    public function get(string $uploadInvoiceId): \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface;

    /**
     * Retrieve invoice matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ): \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceSearchResultsInterface;

    /**
     * Delete invoice
     *
     * @param \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface $uploadInvoice
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        \BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface $uploadInvoice
    ): bool;

    /**
     * Delete invoice by ID
     *
     * @param string $uploadInvoiceId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(string $uploadInvoiceId): bool;
}

