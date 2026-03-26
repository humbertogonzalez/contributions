<?php

declare(strict_types=1);

namespace BalloonGroup\UploadInvoiceApi\Block\Adminhtml\Upload\Invoice\Edit;

use Magento\Backend\Block\Widget\Context;

abstract class GenericButton
{
    /**
     * GenericButton constructor
     *
     * @param Context $context
     */
    public function __construct(
        protected readonly Context $context
    ) {
    }

    /**
     * Return model ID
     *
     * @return string|null
     */
    public function getModelId(): ?string
    {
        return $this->context->getRequest()->getParam('entity_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

