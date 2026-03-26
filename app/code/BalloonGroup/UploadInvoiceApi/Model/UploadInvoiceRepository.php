<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Model;

use BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface;
use BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterfaceFactory;
use BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceSearchResultsInterfaceFactory;
use BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceSearchResultsInterface;
use BalloonGroup\UploadInvoiceApi\Api\UploadInvoiceRepositoryInterface;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice as ResourceUploadInvoice;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice\CollectionFactory as UploadInvoiceCollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;

class UploadInvoiceRepository implements UploadInvoiceRepositoryInterface
{
    /**
     * UploadInvoiceRepository constructor
     *
     * @param ResourceUploadInvoice $resource
     * @param UploadInvoiceInterfaceFactory $uploadInvoiceFactory
     * @param UploadInvoiceCollectionFactory $uploadInvoiceCollectionFactory
     * @param UploadInvoiceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        protected readonly ResourceUploadInvoice $resource,
        protected readonly UploadInvoiceInterfaceFactory $uploadInvoiceFactory,
        protected readonly UploadInvoiceCollectionFactory $uploadInvoiceCollectionFactory,
        protected readonly UploadInvoiceSearchResultsInterfaceFactory $searchResultsFactory,
        protected readonly CollectionProcessorInterface $collectionProcessor,
        protected readonly CompanyRepositoryInterface $companyRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(UploadInvoiceInterface $uploadInvoice) : UploadInvoiceInterface
    {
        try {
            if ($companyId = $uploadInvoice->getIdCustomer()) {
                $company = $this->companyRepository->getByField("reseller_id", $companyId);

                if (!$company->getId()) {
                    throw new CouldNotSaveException(__(
                        'The requested company not exists: %1',
                        $company
                    ));
                }
            }
            $this->resource->save($uploadInvoice);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Invoice: %1',
                $exception->getMessage()
            ));
        }
        return $uploadInvoice;
    }

    /**
     * @inheritDoc
     */
    public function get(string $uploadInvoiceId) : UploadInvoiceInterface
    {
        $uploadInvoice = $this->uploadInvoiceFactory->create();
        $this->resource->load($uploadInvoice, $uploadInvoiceId);
        if (!$uploadInvoice->getId()) {
            throw new NoSuchEntityException(__('Invoice with id "%1" does not exist.', $uploadInvoiceId));
        }
        return $uploadInvoice;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) : UploadInvoiceSearchResultsInterface {
        $collection = $this->uploadInvoiceCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(UploadInvoiceInterface $uploadInvoice) : bool
    {
        try {
            $uploadInvoiceModel = $this->uploadInvoiceFactory->create();
            $this->resource->load($uploadInvoiceModel, $uploadInvoice->getUploadInvoiceId());
            $this->resource->delete($uploadInvoiceModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the invoice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(string $uploadInvoiceId) : bool
    {
        return $this->delete($this->get($uploadInvoiceId));
    }
}

