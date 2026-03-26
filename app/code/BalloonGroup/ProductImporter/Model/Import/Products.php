<?php

declare(strict_types=1);

namespace BalloonGroup\ProductImporter\Model\Import;

use Exception;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use BalloonGroup\ProductImporter\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use BalloonGroup\ProductImporter\Helper\Data as ImporterData;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\Config as EavConfig;

class Products extends AbstractEntity
{
    /** @var string */
    public const ENTITY_CODE ="dimagraf_products";
    public const ENTITY_ID_COLUMN = 'sku';
    public const SKU = 'sku';
    public const NAME = 'nombre';
    public const CATEGORIES = 'categorias';
    public const SUBCATEGORIES = 'subcategorias';
    public const PHOTO = 'foto';
    public const PRICE = 'precio';
    public const SPECIAL_PRICE = 'special_price_general';
    public const WIDTH = 'ancho';
    public const LENGTH = 'largo';
    public const HEIGHT = 'alto';
    public const MIN_ALLOWED = 'minimo_por_carrito';
    public const MAX_ALLOWED = 'maximo_por_carrito';
    public const SKU_UP_SELLING = 'sku_up_selling';
    public const SKU_CROSS_SELLING = 'sku_cross_selling';
    public const SKU_RELATED_PRODUCTS = 'sku_related_products';
    public const STATISTIC_MEASURE = 'medida_estadistica';
    public const PRODUCT = 'producto';
    public const BRAND = 'marca';
    public const THICKNESS = 'espesor';
    public const COLOR = 'color';
    public const WEIGTH = 'peso';
    public const GENERAL_SPECIFICATIONS = 'especificaciones_generales';
    public const FSC_CERTIFICATE = 'certificado_fsc';
    public const CERTIFICATIONS = 'certificaciones_links';
    public const USE = 'usos';
    public const TECHNICAL_FILE = 'ficha_tecnica';
    public const SECURITY_FILE = 'hoja_de_seguridad';
    public const HIERARCHY = 'jerarquias';
    public const STATUS = 'status';

    /**
     * If we should check column names
     */
    protected $needColumnCheck = true;

    /**
     * Need to log in import history
     */
    protected $logInHistory = true;

    /**
     * Permanent entity columns.
     */
    protected $_permanentAttributes = [
        'sku',
        'nombre',
        'categorias',
        'subcategorias',
        'precio',
        'producto',
        'jerarquias',
        'status'
    ];

    /**
     * Valid column names
     */
    protected $validColumnNames = [
        'sku',
        'nombre',
        'categorias',
        'subcategorias',
        'foto',
        'precio',
        'special_price_general',
        'ancho',
        'largo',
        'alto',
        'minimo_por_carrito',
        'maximo_por_carrito',
        'sku_up_selling',
        'sku_cross_selling',
        'sku_related_products',
        'medida_estadistica',
        'producto',
        'marca',
        'espesor',
        'color',
        'peso',
        'especificaciones_generales',
        'certificado_fsc',
        'certificaciones_links',
        'usos',
        'ficha_tecnica',
        'hoja_de_seguridad',
        'jerarquias',
        'status'
    ];

    /** @var array */
    private array $linkTypes = [
        self::SKU_RELATED_PRODUCTS => 'related',
        self::SKU_UP_SELLING => 'upsell',
        self::SKU_CROSS_SELLING => 'crosssell'
    ];

    /** @var array */
    private array $processedSkus = [];

    /**
     * Courses constructor.
     *
     * @param JsonHelper $jsonHelper
     * @param ImportHelper $importExportData
     * @param Data $importData
     * @param Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Logger $logger
     * @param ImporterData $importerData
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param StoreManagerInterface $storeManager
     * @param LinkFactory $linkFactory
     * @param Filesystem $filesystem
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductAction $productAction
     * @param EavConfig $eavConfig
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        private readonly Logger $logger,
        private readonly ImporterData $importerData,
        private readonly WebsiteRepositoryInterface $websiteRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductInterfaceFactory $productInterfaceFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LinkFactory $linkFactory,
        private readonly Filesystem $filesystem,
        private readonly StoreRepositoryInterface $storeRepository,
        private readonly ProductAction $productAction,
        private readonly EavConfig $eavConfig
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->errorAggregator = $errorAggregator;
    }


        /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode(): string
    {
        return self::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Row validation
     *
     * @param array $rowData
     * @param int $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum): bool
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }

        $this->_validatedRows[$rowNum] = true;

        $sku = $rowData[self::SKU] ?? '';

        if (isset($this->processedSkus[$sku])) {
            $this->addRowError("Duplicate SKU " . $sku, $rowNum);
            return false;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Import data
     *
     * @return bool
     *
     * @throws Exception
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_APPEND:
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            default:
                $result = false;
                break;
        }

        $this->logger->info(sprintf(
            "Import completed. Total products created: %d, Total products updated: %d",
            $this->countItemsCreated,
            $this->countItemsUpdated
        ));

        return true;
    }

    /**
     * Delete entities
     *
     * @return bool
     */
    private function deleteEntity(): bool
    {
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish(array_unique($rows));
        }

        return false;
    }

    /**
     * Save and replace entities
     *
     * @return void
     */
    private function saveAndReplaceEntity()
    {
        $behavior = $this->getBehavior();
        $rows = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    $this->logger->info("> Skipping row " . $rowNum . " due to validation failure");
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }

                $rowSku = $row[self::ENTITY_ID_COLUMN];

                if (isset($this->processedSkus[$rowSku])) {
                    $this->logger->info("> Skipping duplicate SKU " . $rowSku . " at row " . $rowNum);
                    continue;
                }

                $this->processedSkus[$rowSku] = true;
                $entityList[$rowSku][] = $row;
            }

            if (Import::BEHAVIOR_REPLACE === $behavior) {
                if ($rows && $this->deleteEntityFinish(array_unique($rows))) {
                    $this->saveEntityFinish($entityList);
                }
            } elseif (Import::BEHAVIOR_APPEND === $behavior) {
                $this->saveEntityFinish($entityList);
            }
        }
    }

    /**
     * Save entities
     *
     * @param array $entityData
     * @return bool
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException|LocalizedException
     */
    private function saveEntityFinish(array $entityData): bool
    {
        foreach ($entityData as $sku => $rows) {
            $isNewProduct = false;
            $row = reset($rows);
            $this->logger->info(print_r($row, true));

            try {
                try {
                    /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                    $product = $this->productRepository->get($sku);
                    $this->countItemsUpdated++;
                } catch (NoSuchEntityException $e) {
                    /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
                    $product = $this->productInterfaceFactory->create();
                    $product->setName($row[self::NAME] ?? '');
                    $product->setSku($sku);
                    $product->setTypeId('simple');
                    $product->setAttributeSetId(16);
                    $product->setVisibility(4);
                    $product->setPrice($row[self::PRICE] ?? 0);
                    $product->setSpecialPrice($row[self::SPECIAL_PRICE] ?? null);
                    $product->setWeight($row[self::WEIGTH] ?? 1);
                    $isNewProduct = true;
                    $this->countItemsCreated++;
                }

                $websiteIds = [];

                foreach ($this->websiteRepository->getList() as $website) {
                    $websiteIds[] = $website->getId();
                }

                $product->setWebsiteIds($websiteIds);

                $storeIds = [];
                foreach ($websiteIds as $websiteId) {
                    $website = $this->websiteRepository->getById($websiteId);
                    $storeIds = array_merge($storeIds, $website->getStoreIds());
                }

                $storeIds = array_filter($storeIds, fn($storeId) => $storeId != 0);

                $attributesToUpdate = [
                    'name' => $row[self::NAME] ?? '',
                    'price' => $row[self::PRICE] ?? 0,
                    'special_price' => $row[self::SPECIAL_PRICE] ?? null,
                    'weight' => $row[self::WEIGTH] ?? 1,
                    'certificaciones' => $row[self::CERTIFICATIONS] ?? null,
                    'jerarquia' => $row[self::HIERARCHY] ?? null,
                    self::WEIGTH => $row[self::WEIGTH] ?? 1,
                    self::PRODUCT => $row[self::PRODUCT] ?? null,
                    self::COLOR => $row[self::COLOR] ?? null,
                    self::STATISTIC_MEASURE => $row[self::STATISTIC_MEASURE] ?? null,
                    self::GENERAL_SPECIFICATIONS => $row[self::GENERAL_SPECIFICATIONS] ?? null,
                    self::USE => $row[self::USE] ?? null,
                    self::TECHNICAL_FILE => $row[self::TECHNICAL_FILE] ?? null,
                    self::SECURITY_FILE => $row[self::SECURITY_FILE] ?? null,
                    self::STATUS => isset($row[self::STATUS]) ? $this->importerData->mapProductStatus($row[self::STATUS]) : null,
                ];

                $customAttributes = [
                    self::HEIGHT => $row[self::HEIGHT] ?? null,
                    self::WIDTH => $row[self::WIDTH] ?? null,
                    self::LENGTH => $row[self::LENGTH] ?? null,
                    self::BRAND => $row[self::BRAND] ?? null,
                    self::THICKNESS => $row[self::THICKNESS] ?? null,
                    self::FSC_CERTIFICATE => $row[self::FSC_CERTIFICATE] ?? null,
                ];

                foreach ($customAttributes as $attributeCode => $value) {
                    if ($value && $value != 'N/A') {
                        $attributesToUpdate[$attributeCode] = $this->importerData->getProductAttributeValueFromLabel($attributeCode, $value)
                            ?? $product->getData($attributeCode);
                    }
                }

                $attributesToUpdate = array_filter($attributesToUpdate, fn($value) => !is_null($value) && $value !== '');

                $product->setStoreId(0);
                foreach ($attributesToUpdate as $attributeCode => $value) {
                    if ($this->isAttributeGlobal($attributeCode)) {
                        $product->setData($attributeCode, $value);
                    }
                }

                $categoryIds = [];

                if (isset($row[self::CATEGORIES])) {
                    try {
                        $categoryId = $this->importerData->getCategoryIdByName(trim($row[self::CATEGORIES]));
                        $categoryIds[] = $categoryId;
                    } catch (Exception $e) {
                        $this->logger->error("[saveEntityFinish::Categories] ERROR:: " . $e->getMessage());
                        $this->logger->error($e->getTraceAsString());
                        $this->addRowError(
                            "Failed to set categories for SKU " . $sku . " || " . $e->getMessage(),
                            $row
                        );
                    }
                }

                if (isset($row[self::SUBCATEGORIES])) {
                    try {
                        $categoryId = $this->importerData->getCategoryIdByName(trim($row[self::SUBCATEGORIES]));
                        $categoryIds[] = $categoryId;
                    } catch (Exception $e) {
                        $this->logger->error("[saveEntityFinish::SubCategories] ERROR:: " . $e->getMessage());
                        $this->logger->error($e->getTraceAsString());
                        $this->addRowError(
                            "Failed to set SubCategories for SKU " . $sku . " || " . $e->getMessage(),
                            $row
                        );
                    }
                }

                $product->setCategoryIds($categoryIds);

                if (isset($row[self::PHOTO]) && $row[self::PHOTO]) {
                    $this->assignImagesToProduct($product, $sku, $row);
                }

                foreach ($this->linkTypes as $field => $type) {
                    if (isset($row[$field]) && $row[$field]) {
                        $linkedSkus = explode(',', $row[$field]);
                        $links = [];
                        foreach ($linkedSkus as $linkedSku) {
                            try {
                                $linkedProduct = $this->productRepository->get(trim($linkedSku));
                                $link = $this->linkFactory->create();
                                $link->setProductId($product->getId());
                                $link->setLinkedProductId($linkedProduct->getId());
                                $link->setLinkType($type);
                                $links[] = $link;
                            } catch (Exception $e) {
                                $this->logger->error("[saveEntityFinish::Link] ERROR:: " . $e->getMessage());
                                $this->addRowError(
                                    "Failed to set Link Products for SKU " . $sku . " || " . $e->getMessage(),
                                    $row
                                );
                            }
                        }
                        $product->setProductLinks($links);
                    }
                }

                if (isset($row[self::STATUS])) {
                    $product->setData(self::STATUS, $this->importerData->mapProductStatus($row[self::STATUS]));
                }

                $this->productRepository->save($product);

                $product = $this->productRepository->get($sku);
                $productId = $product->getId();
                if (!$productId) {
                    $this->logger->error("Skipping product SKU " . $sku . ": No valid entity_id after save");
                    $this->addRowError("No valid entity_id for SKU " . $sku . " after save", $rows);
                    if ($isNewProduct) {
                        $this->countItemsCreated--;
                    } else {
                        $this->countItemsUpdated--;
                    }
                    continue;
                }

                foreach ($storeIds as $storeId) {
                    $storeAttributes = [];
                    foreach ($attributesToUpdate as $attributeCode => $value) {
                        if (!$this->isAttributeGlobal($attributeCode)) {
                            $storeAttributes[$attributeCode] = $value;
                        }
                    }
                    if (!empty($storeAttributes)) {
                        try {
                            $this->productAction->updateAttributes(
                                [$productId],
                                $storeAttributes,
                                $storeId
                            );
                        } catch (Exception $e) {
                            $this->logger->error("[saveEntityFinish::updateAttributes] ERROR:: " . $e->getMessage());
                            $this->addRowError(
                                "Failed to update attributes for SKU " . $sku . " on store ID " . $storeId . ": " . $e->getMessage(),
                                $row
                            );
                        }
                    }
                }
            } catch (LocalizedException $e) {
                $this->logger->error("[saveEntityFinish] ERROR:: " . $e->getMessage());
                $this->addRowError($e->getMessage(), $row);
                if ($isNewProduct) {
                    $this->countItemsCreated--;
                } else {
                    $this->countItemsUpdated--;
                }
                continue;
            }
        }
        return true;
    }

    /**
     * Delete entities
     *
     * @param array $skus
     *
     * @return bool
     */
    private function deleteEntityFinish(array $skus): bool
    {
        if ($skus) {
            foreach ($skus as $sku) {
                try {
                    $product = $this->productRepository->get($sku);
                    $this->productRepository->delete($product);
                } catch (Exception $e) {
                    $this->logger->info("[deleteEntityFinish] ERROR:: " . $e->getMessage());
                    $this->logger->info($e->getTraceAsString());
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }

    protected function assignImagesToProduct($product, $sku, $rowNum)
    {
        $imageDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog/product');
        $imagePattern = $imageDir . '/' . $sku . '*';
        $images = glob($imagePattern);

        if (empty($images)) {
            $this->logger->info("> No Image in pub/media/catalog/product/ for sku: " . $sku);
            return null;
        }

        $isFirstImage = true;

        foreach ($images as $imagePath) {
            if (!file_exists($imagePath) || !in_array(strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
                $this->addRowError('Invalid image file: ' . basename($imagePath), $rowNum);
                $this->logger->warning("Invalid image file '" . $imagePath . "' for SKU " . $sku  . " at row " . $rowNum);
                continue;
            }

            try {
                $roles = $isFirstImage ? ['image', 'small_image', 'thumbnail'] : [];
                $product->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], true, true);
                $this->logger->info("Added image '" . $imagePath . "' to SKU " . $sku . " media gallery");
                $isFirstImage = false;
            } catch (Exception $e) {
                $this->logger->error("Failed to add image '" . $imagePath . "' to SKU " . $sku . " : " . $e->getMessage());
                $this->addRowError("Failed to add image '" . $imagePath . "' to SKU " . $sku . " : " . $e->getMessage(), $rowNum);
            }
        }
    }

    /**
     * Check if an attribute is Global-scoped
     *
     * @param string $attributeCode
     * @return bool
     */
    private function isAttributeGlobal(string $attributeCode): bool
    {
        try {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
            return $attribute->getScope() === ScopedAttributeInterface::SCOPE_GLOBAL;
        } catch (Exception $e) {
            $this->logger->error("[isAttributeGlobal] ERROR:: " . $e->getMessage());
            return false;
        }
    }
}
