<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;
use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice\CollectionFactory;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice\Collection;
use Magento\Framework\DataObject;

class Data extends AbstractHelper
{
    /**
     * Config constructor
     *
     * @param Context $context
     * @param Session $customerSession
     * @param CompanyRepositoryInterface $companyRepository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        private readonly Session $customerSession,
        private readonly CompanyRepositoryInterface $companyRepository,
        private readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Get customer/company reseller id
     *
     * @return mixed
     */
    public function getCustomerResellerId(): mixed
    {
        if ($this->customerSession->isLoggedIn()) {
            $company = $this->companyRepository->getByField(
                'super_user_id',
                $this->customerSession->getCustomerId()
            );

            if ($resellerId = $company->getResellerId()) {
                return $resellerId;
            }
        }

        return null;
    }

    /**
     * Get customer invoices
     *
     * @return Collection|null
     */
    public function getCustomerInvoices(): ?Collection
    {
        if ($this->getCustomerResellerId()) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('id_customer', $this->getCustomerResellerId());
            return $collection;
        }

        return null;
    }
}
