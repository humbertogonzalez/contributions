<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Api\Data;

interface UploadInvoiceInterface
{
    /** @var string */
    public const ID_CUSTOMER = 'id_customer';
    public const ID_INVOICE = 'id_invoice';
    public const URL_INVOICE = 'url_invoice';
    public const CREATED_AT = 'created_at';

    /**
     * Get id_customer
     *
     * @return string
     */
    public function getIdCustomer(): string;

    /**
     * Set id_customer
     *
     * @param string $idCustomer
     * @return UploadInvoiceInterface
     */
    public function setIdCustomer(string $idCustomer): UploadInvoiceInterface;

    /**
     * Get id_invoice
     *
     * @return string
     */
    public function getIdInvoice(): string;

    /**
     * Set id_invoice
     *
     * @param string $idInvoice
     * @return UploadInvoiceInterface
     */
    public function setIdInvoice(string $idInvoice): UploadInvoiceInterface;

    /**
     * Get url_invoice
     *
     * @return string
     */
    public function getUrlInvoice(): string;

    /**
     * Set url_invoice
     *
     * @param string $urlInvoice
     * @return UploadInvoiceInterface
     */
    public function setUrlInvoice(string $urlInvoice): UploadInvoiceInterface;

    /**
     * Get created_at
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set created_at
     *
     * @param string $createdAt
     * @return UploadInvoiceInterface
     */
    public function setCreatedAt(string $createdAt): UploadInvoiceInterface;
}

