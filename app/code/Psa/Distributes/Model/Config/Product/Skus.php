<?php

namespace Psa\Distributes\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;


class Skus extends AbstractSource
{
    public function __construct(
        protected readonly CollectionFactory $productCollectionFactory,
        protected readonly RequestInterface $request
    )
    {
    }

    protected ?array $optionFactory;
    public function getAllOptions(): ?array
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect([
            'sku',
            'name'
        ]);
        $this->_options = [];
        foreach ($productCollection->getItems() as $product) {
            $this->_options[] = [
                'label' => $product->getSku(),
                'value' => $product->getSku()
            ];
        }
        return $this->_options;
    }
}
