<?php

declare(strict_types=1);

namespace BalloonGroup\ProductImporter\Helper;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;
use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class Data
{
    /**
     * Cache for attribute option IDs
     *
     * @var array
     */
    private array $optionCache = [];

    /**
     * Data constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param EavConfig $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryListInterface $categoryList
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly EavConfig $config,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly CategoryListInterface $categoryList,
        private readonly EavSetupFactory $eavSetupFactory,
        private readonly LoggerInterface $logger
    ) {

    }

    /**
     * Get attribute value ID by label
     *
     * @param string $attributeCode
     * @param string $label
     * @return int|null
     * @throws LocalizedException
     */
    public function getProductAttributeValueFromLabel(string $attributeCode, string $label): ?int
    {
        $label = trim($label);
        $cacheKey = $attributeCode . '|' . strtolower($label);

        if (isset($this->optionCache[$cacheKey])) {
            return $this->optionCache[$cacheKey];
        }

        $attribute = $this->config->getAttribute(Product::ENTITY, $attributeCode);

        if (!$attribute || !$attribute->usesSource()) {
            $this->logger->error("> Attribute " . $attributeCode . " does not exist or does not use a source");
            return null;
        }

        $valueId = $this->findOptionId($attribute, $label);
        if ($valueId !== null) {
            $this->optionCache[$cacheKey] = $valueId;
            return $valueId;
        }

        if ($valueId === null) {
            $eavSetup = $this->eavSetupFactory->create();
            $option = [
                'attribute_id' => $attribute->getId(),
                'value' => [
                    'option_' . md5($label . time()) => [
                        0 => $label,
                    ]
                ]
            ];

            try {
                $eavSetup->addAttributeOption($option);
                $attribute = $this->config->getAttribute(Product::ENTITY, $attributeCode);
                $options = $attribute->getSource()->getAllOptions(false);

                foreach ($options as $option) {
                    if (strcasecmp($option['label'], $label) === 0) {
                        $valueId = (int)$option['value'];
                        break;
                    }
                }
            } catch (Exception $e) {
                $this->logger->error("Failed to create option '{$label}' for attribute {$attributeCode}: " . $e->getMessage());
                return null;
            }
        }

        return $valueId;
    }

    /**
     * Get category id by name
     *
     * @param string $name
     * @return int|null
     */
    public function getCategoryIdByName(string $name): ?int
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('name', $name, 'eq')
            ->create();

        $categories = $this->categoryList->getList($searchCriteria)->getItems();

        if (!empty($categories)) {
            $category = reset($categories);
            return (int)$category->getId();
        }

        return null;
    }

    /**
     * Map status attribute
     *
     * @param string $status
     * @return mixed
     */
    public function mapProductStatus(string $status): int
    {
        return match ($status) {
            'Habilitado' => Status::STATUS_ENABLED,
            'Deshabilitado' => Status::STATUS_DISABLED,
            default => 1
        };
    }

    /**
     * Find option ID for a given attribute and label
     *
     * @param Attribute $attribute
     * @param string $label
     * @return int|null
     * @throws LocalizedException
     */
    private function findOptionId(Attribute $attribute, string $label): ?int
    {
        $this->config->clear();
        $attribute = $this->config->getAttribute(Product::ENTITY, $attribute->getAttributeCode());
        $options = $attribute->getSource()->getAllOptions(false);

        foreach ($options as $option) {
            if (strcasecmp(trim($option['label']), trim($label)) === 0) {
                return (int)$option['value'];
            }
        }

        return null;
    }
}
