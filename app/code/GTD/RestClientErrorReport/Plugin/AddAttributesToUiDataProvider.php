<?php
namespace Balloon\RestClientErrorReport\Plugin;

use Balloon\RestClientErrorReport\Ui\DataProvider\Logs\ListingDataProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AddAttributesToUiDataProvider
{
    private AttributeRepositoryInterface $attributeRepository;
    private ProductMetadataInterface $productMetadata;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
    }

    public function afterGetSearchResult(ListingDataProvider $subject, SearchResult $result)
    {
        if ($result->isLoaded()) {
            return $result;
        }

        return $result;
    }
}
