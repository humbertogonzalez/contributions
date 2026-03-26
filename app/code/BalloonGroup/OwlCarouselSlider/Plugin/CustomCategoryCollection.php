<?php

declare(strict_types=1);

namespace BalloonGroup\OwlCarouselSlider\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use WeltPixel\OwlCarouselSlider\Block\Slider\Products;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class CustomCategoryCollection
{
    /**
     * CustomCategoryCollection constructor
     *
     * @param Resolver $layerResolver
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        private readonly Resolver $layerResolver,
        private readonly CategoryFactory $categoryFactory,
        private readonly CollectionFactory $productCollectionFactory
    )
    {
    }

    /**
     * @param Products $subject
     * @param array|Collection $result
     * @return array|Collection
     */
    public function afterGetProductCollection(Products $subject, array|Collection $result): array|Collection
    {
        $currentCategoryId = $this->layerResolver->get()->getCurrentCategory()->getId();

        if ($subject->getData('products_type') == "category_products") {
            $categoryId = $this->_getCategoryIdFrom($subject->getData('category'));
            $category = $this->categoryFactory->create()->load($categoryId);

            if ($category->getId() != 90 && $category->getId() != 91) {
                if ($categoryId != $currentCategoryId) {
                    // Load child category products to exclude
                    $excludedCategory = $this->categoryFactory->create()->load($currentCategoryId);
                    $excludedProductIds = $excludedCategory->getProductCollection()->getAllIds();

                    // Load product collection for parent category
                    $result = $this->productCollectionFactory->create();
                    $result->addCategoryFilter($category)
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('entity_id', ['nin' => $excludedProductIds]);
                }
            }
        }

        return $result;
    }


    /**
     * @param $category
     * @return false|string
     */
    protected function _getCategoryIdFrom($category)
    {
        $value = explode('/', $category);
        $categoryId = false;

        if (isset($value[0]) && isset($value[1]) && $value[0] == 'category') {
            $categoryId = $value[1];
        }

        return $categoryId;
    }
}
