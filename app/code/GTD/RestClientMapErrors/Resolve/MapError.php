<?php

namespace Balloon\RestClientMapErrors\Resolve;

use Balloon\RestClientMapErrors\Api\Data\TypeMapErrorInterface;
use Balloon\RestClientMapErrors\Model\ResourceModel\MapError\CollectionFactory as MapErrorCollectionFactory;
use Balloon\RestClientMapErrors\Model\ResourceModel\DefaultError\CollectionFactory as DefaultErrorCollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;

class MapError
{
    public function __construct(
        protected readonly MapErrorCollectionFactory $mapErrorCollectionFactory,
        protected readonly DefaultErrorCollectionFactory $defaultErrorCollectionFactory,
        protected readonly FilterProvider $filterProvider,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly BlockFactory $blockFactory,
    )
    {
    }

    public function resolveBlock(int $typeError, int $codeResponse): ?string {
        if (!in_array($typeError,array_keys(TypeMapErrorInterface::MAP))) {
            return null;
        }
        $mapErrorCollection = $this->mapErrorCollectionFactory->create();
        $mapErrorCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'type_map', ['eq' => $typeError]
            )
            ->addFieldToFilter(
                'code_response', ['eq' => $codeResponse]
            );
        $mapError = $mapErrorCollection->getFirstItem();
        if ($mapError->getId()) {
            return $this->getBlock($mapError->getBlockCode());
        }
        $defaultErrorCollection = $this->defaultErrorCollectionFactory->create();
        $defaultErrorCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'type_map', ['eq' => $typeError]
            );
        $defaultError = $defaultErrorCollection->getFirstItem();
        if ($defaultError->getId()) {
            return $this->getBlock($defaultError->getBlockCode());
        }
        return false;
    }

    protected function getBlock(string $blockCode): string {
        $html = '';
        if ($blockCode) {
            $storeId = $this->storeManager->getStore()->getId();
            $block = $this->blockFactory->create();
            $block->setStoreId($storeId)->load($blockCode, 'identifier');
            $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
        }
        return $html;
    }
}
