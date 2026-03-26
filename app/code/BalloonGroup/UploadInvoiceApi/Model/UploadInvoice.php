<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Model;

use BalloonGroup\UploadInvoiceApi\Api\Data\UploadInvoiceInterface;
use Magento\Framework\Model\AbstractModel;
use BalloonGroup\UploadInvoiceApi\Model\ResourceModel\UploadInvoice as UploadInvoiceResource;

class UploadInvoice extends AbstractModel implements UploadInvoiceInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(UploadInvoiceResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdCustomer(): string
    {
        return $this->getData(self::ID_CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setIdCustomer(string $idCustomer): UploadInvoiceInterface
    {
        return $this->setData(self::ID_CUSTOMER, $idCustomer);
    }

    /**
     * @inheritDoc
     */
    public function getIdInvoice(): string
    {
        return $this->getData(self::ID_INVOICE);
    }

    /**
     * @inheritDoc
     */
    public function setIdInvoice(string $idInvoice): UploadInvoiceInterface
    {
        return $this->setData(self::ID_INVOICE, $idInvoice);
    }

    /**
     * @inheritDoc
     */
    public function getUrlInvoice(): string
    {
        return $this->getData(self::URL_INVOICE);
    }

    /**
     * @inheritDoc
     */
    public function setUrlInvoice(string $urlInvoice): UploadInvoiceInterface
    {
        return $this->setData(self::URL_INVOICE, $urlInvoice);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): UploadInvoiceInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}

