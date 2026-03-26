<?php

declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Eav\Model\Config;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\RequestInterface;

class Data extends AbstractHelper
{


    protected $categoryRepository;
    protected $categoryCollectionFactory;
    protected $productCollectionFactory;
    protected $scopeConfig;
    protected $product;
    protected $storeManager;
    protected $filterProvider;
    protected $eavConfig;
    protected $customOptionRepository;
    protected $productsAvailable = [];
    protected $stockRegistry;
    protected $request;

    public function __construct(
        Context                                $context,
        CategoryRepositoryInterface            $categoryRepository,
        CategoryCollectionFactory              $categoryCollectionFactory,
        ProductCollectionFactory               $productCollectionFactory,
        ProductRepositoryInterface             $product,
        StoreManagerInterface                  $storeManager,
        FilterProvider                         $filterProvider,
        Config                                 $eavConfig,
        ProductCustomOptionRepositoryInterface $customOptionRepository,
        StockRegistryInterface                 $stockRegistry,
        RequestInterface $request
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->product = $product;
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
        $this->eavConfig = $eavConfig;
        $this->customOptionRepository = $customOptionRepository;
        $this->stockRegistry = $stockRegistry;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Cache\ConfigInterface
     */
    public function getConfig($config_text, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($config_text, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    public function isEnable()
    {
        return $this->getConfig('segmento_empresas_configuracion/general/activar');
    }

    public function getPrepareJsonPaqueteArmado($data)
    {
        $result = [];
        if (!empty($data)) {
            foreach ($data as $item) {
                $objProduct = $this->getProductBySku(trim($item->getSku()));
                $this->validateProduct($objProduct, trim($item->getSku()));
                $result[] = ['sku' => $item->getSku(), 'precio' => $item->getPrice(), 'qty' => $item->getQty()];
            }
        }
        return ['products_pack' => $result, 'products_unavailable' => $this->productsAvailable];
    }

    /**
     * Obtener la categoría por ID y luego las subcategorías y productos de cada subcategoría
     *
     * @param int $categoryId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPrepareJsonProducts($data)
    {
        $result = [];
        if (!empty($data)) {
            $combos = json_decode($data->getCombo());
            foreach ($combos as $item) {

                $result[] = ['parent' => $this->getProductArray($item->sku), 'children' => $this->searchInTree($item->children)];
            }
        }
        return ['products_pack' => $result, 'products_unavailable' => $this->productsAvailable];
    }

    public function getProductArray($sku)
    {
        $data = [];

        if (!empty($sku)) {
            $objProduct = $this->getProductBySku(trim($sku));
            $this->validateProduct($objProduct, trim($sku));
            if ($objProduct) {
                $formattedPrice = $this->formatPrice($objProduct->getPrice());

                $data = [
                    'id' => $objProduct->getId(),
                    'name' => $objProduct->getName(),
                    'sku' => $objProduct->getSku(),
                    'url' => $objProduct->getProductUrl(),
                    'precio' => $formattedPrice,
                ];

                $data['imagen'] = [
                    'thumbnail' => $objProduct->getThumbnail() && $objProduct->getThumbnail() != 'no_selection' ? $this->getUrlPathImages() . $objProduct->getThumbnail() : '',
                    'big_image' => $objProduct->getEmpresasBigImage() && $objProduct->getEmpresasBigImage() != 'no_selection' ? $this->getUrlPathImages() . $objProduct->getEmpresasBigImage() : '',
                    'icon_image' => $objProduct->getEmpresasIconImage() && $objProduct->getEmpresasIconImage() != 'no_selection'  ? $this->getUrlPathImages() . $objProduct->getEmpresasIconImage() : '',
                ];

                $objProduct->getVelocidadNacional() ? $data['velocidad_nacional'] = $objProduct->getVelocidadNacional() : '';
                $objProduct->getAliasProductName() ? $data['alias_name'] = $objProduct->getAliasProductName() : '';
                $objProduct->getVelocidadInternacional() ? $data['velocidad_internacional'] = $objProduct->getVelocidadInternacional() : '';
                $objProduct->getConexionExclusiva() ? $data['conexion_exclusiva'] = $objProduct->getConexionExclusiva() : '';
                $objProduct->getAnchoDeBanda() ? $data['ancho_de_banda'] = $objProduct->getAnchoDeBanda() : '';
                $objProduct->getWatchguard() ? $data['watchguard'] = $objProduct->getWatchguard() : '';
                $objProduct->getUsuariosRecomendados() ? $data['usuarios_recomendados'] = $objProduct->getUsuariosRecomendados() : '';
                $objProduct->getRendimientoUtm() ? $data['rendimiento_utm'] = $objProduct->getRendimientoUtm() : '';
                $objProduct->getPoolIpDisponible() ? $data['pool_ip_disponible'] = $objProduct->getPoolIpDisponible() : '';
                $objProduct->getAccessPointExterior() ? $data['access_point_exterior'] = $objProduct->getAccessPointExterior() : '';
                $objProduct->getAccessPointInterior() ? $data['access_point_interior'] = $objProduct->getAccessPointInterior() : '';
                $objProduct->getAliasIpsName() ? $data['alias_ips_name'] = $objProduct->getAliasIpsName() : '';
                $objProduct->getAliasIpsDescription() ? $data['alias_ips_description'] = $objProduct->getAliasIpsDescription() : '';
                $objProduct->getAliasVelocidadesName() ? $data['alias_velocidades_name'] = $objProduct->getAliasVelocidadesName() : '';
                $objProduct->getAliasVelocidadesDescription() ? $data['alias_velocidades_description'] = $objProduct->getAliasVelocidadesDescription() : '';
                $objProduct->getCompatibilidad() ? $data['compatibilidad'] = $objProduct->getCompatibilidad() : '';
                $objProduct->getEstabilidad() ? $data['estabilidad'] = $objProduct->getEstabilidad() : '';
                $objProduct->getIpsDisponibles() ? $data['ips_disponibles'] = $objProduct->getResource()->getAttribute('ips_disponibles')->getFrontend()->getValue($objProduct) : '';
                $objProduct->getCapacidadDeTrafico() ? $data['capacidad_de_trafico'] = $objProduct->getResource()->getAttribute('capacidad_de_trafico')->getFrontend()->getValue($objProduct) : '';
                $objProduct->getMeraki() ? $data['meraki'] = $objProduct->getMeraki() : '';
                $objProduct->getRentaMensualGtd36Meses() ? $data['renta_mensual_gtd_36_meses'] = $objProduct->getRentaMensualGtd36Meses() : '';
                $objProduct->getRentaMensualTelsur36Meses() ? $data['renta_mensual_telsur_36_meses'] = $objProduct->getRentaMensualTelsur36Meses() : '';
                $objProduct->getUso() ? $data['uso'] = $objProduct->getResource()->getAttribute('uso')->getFrontend()->getValue($objProduct) : '';
                $objProduct->getFormulaAccessPoint() ? $data['formula_access_point'] = $objProduct->getFormulaAccessPoint() : '';
                $objProduct->getDesicionFirewall() ? $data['desicion_firewall'] = $this->getDesicionOptions($objProduct->getDesicionFirewall(), 'desicion_firewall') : '';
                $objProduct->getNoAgregableAlCarrito() ? $data['no_agregable_al_carrito'] = $objProduct->getNoAgregableAlCarrito() : '';
                $objProduct->getModelo() ? $data['modelo'] = $objProduct->getModelo() : '';
                $objProduct->getMasInformacion() ? $data['mas_informacion'] = $this->renderContent($objProduct->getMasInformacion()) : '';
                $objProduct->getCuotas() ? $data['cuotas'] = $objProduct->getResource()->getAttribute('cuotas')->getFrontend()->getValue($objProduct) : '';
                $objProduct->getOcultarPrecioSummary() ? $data['ocultar_precio_summary'] = $objProduct->getOcultarPrecioSummary() : '';
                $objProduct->getContentTagStarlink() ? $data['content_tag_starlink'] = $this->renderContent($objProduct->getContentTagStarlink()) : '';
                $objProduct->getDesicionMicrosoftTeams() ? $data['desicion_microsoft_teams'] = $this->getDesicionOptions($objProduct->getDesicionMicrosoftTeams(), 'desicion_microsoft_teams') : '';
                $objProduct->getContentCompareModal() ? $data['content_compare_modal'] = $this->renderContent($objProduct->getContentCompareModal()) : '';
                $objProduct->getLicenseSiteModal() ? $data['license_site_modal'] = $this->renderContent($objProduct->getLicenseSiteModal()) : '';
                $objProduct->getIncrementalCard() ? $data['incremental_card'] = $objProduct->getResource()->getAttribute('incremental_card')->getFrontend()->getValue($objProduct) : '';

                if ($objProduct->getSpecialPrice()) {
                    $data['special_price'] = $this->formatPrice($objProduct->getSpecialPrice());
                    $porcentaje_descuento = (($objProduct->getPrice() - $objProduct->getSpecialPrice()) / $objProduct->getPrice()) * 100;
                    $porcentaje_descuento = round($porcentaje_descuento);
                    $data['special_price_porcentage'] = $porcentaje_descuento;
                }

                if (isset($data['incremental_card'])) {
                    $Stock = $this->getStockItems($objProduct);
                    $data['min_qty_card'] = $Stock->getMinSaleQty();
                    $data['max_qty_card'] = $Stock->getMaxSaleQty();
                }
            }
        }

        return $data;
    }


    public function searchInTree($items)
    {
        $result = [];

        if (!empty($items)) {
            foreach ($items as $item) {
                $result[] = ['parent' => $this->getProductArray($item->sku), 'children' => $this->searchInTree($item->children)];
            }
        }

        return $result;
    }

    public function getDesicionOptions($value, $attribute_code)
    {
        $options = [];
        $attribute = $this->getAttribute($attribute_code, \Magento\Catalog\Model\Product::ENTITY);

        if ($attribute && $attribute->usesSource()) {
            $optionLabels = $attribute->getSource()->getAllOptions(false);
            $values = explode(',', $value); // El valor puede ser múltiple si es un atributo multiselect

            foreach ($values as $val) {
                foreach ($optionLabels as $option) {
                    if ($option['value'] == $val) {
                        preg_match('/\(([^)]*)\)/', $option['label'], $matches);
                        $contenido = $matches[1] ?? '';
                        $resultado = preg_replace('/\s*\([^)]*\)\s*/', '', $option['label']);
                        $resultado = trim(preg_replace('/\s+/', ' ', $resultado));
                        $options[] = [
                            'texto' => $resultado,
                            'value' => $contenido
                        ];
                    }
                }
            }
        }
        return $options;
    }

    protected function getAttribute($attributeCode, $entityType = null)
    {
        try {
            return $this->eavConfig->getAttribute($entityType, $attributeCode);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductBySku($sku)
    {
        try {
            return $this->product->get($sku);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getProductById($id)
    {
        return $this->product->getById($id);
    }

    public function getUrlPathImages()
    {
        return str_replace($this->storeManager->getStore()->getCode(), '', $this->storeManager->getStore()->getBaseUrl() . 'media/catalog/product');
    }

    public function renderContent($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }

    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    public function getStockItems($product)
    {
        return $this->stockRegistry->getStockItem($product->getId());
    }

    public function getProductConfigurableCuotas($product)
    {

        $options = [];

        if ($product->getTypeId() == 'configurable') {
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child) {
                $options[] = [
                    'label' => $child->getName(),
                    'sku' => $child->getSku(),
                    'type' => $child->getTypeId(),
                    'price' => number_format((float)$child->getPrice(), 2, '.', ''),
                ];
            }
        }
        return $options;
    }

    public function validateProduct($product, $sku)
    {
        if ($product) {
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $isInStock = $stockItem ? $stockItem->getIsInStock() : false;

            $websiteIds = $product->getWebsiteIds();

            $stockStatus = $product->getQuantityAndStockStatus();
            $isStockStatusOutOfStock = isset($stockStatus['is_in_stock']) && !$stockStatus['is_in_stock'];
            $qtyStock = $stockStatus['qty'] > 0 ? true : false;

            if (!$isInStock || empty($websiteIds) || $isStockStatusOutOfStock || !$qtyStock) {
                $this->productsAvailable[$product->getSku()] = [
                    'is_in_stock' => $isInStock,
                    'web_site_ids' => $websiteIds,
                    'is_stock_status_out_of_stock' => $isStockStatusOutOfStock,
                    'qty_stock' => $qtyStock,
                ];
            }
        } else {
            $this->productsAvailable[$sku] = [
                'no existe',
            ];
        }
    }

    public function getCartRule($request_path)
    {
        $arrayConfig = [];

        $urls_params = $this->getConfig('segmento_empresas_configuracion/validate_qty_phone/value');
        if ($urls_params) {
            $urls_paramsArray = unserialize($urls_params);
            $path = $request_path;
            foreach ($urls_paramsArray as $key => $value) {
                if ($path == $value['request_path'] && $value['enable'] == 1) {
                    $arrayConfig[] = $value;
                }
            }
        }

        return $arrayConfig;
    }

    public function getAbandonedCartConfig($request_path, $type)
    {
        $arrayConfig = [];

        $urls_params = $this->getConfig('segmento_empresas_configuracion/abandoned_cart/value');
        if ($urls_params) {
            $urls_paramsArray = unserialize($urls_params);
            $path = $request_path;
            foreach ($urls_paramsArray as $key => $value) {
                if ($path == $value['request_path'] && $value['enable'] == 1 && $value['area'] == $type) {
                    $arrayConfig = [
                        'area' => $value['area'],
                        'tiempo_en_pantalla' => $value['tiempo_en_pantalla']
                    ];
                }
            }
        }

        return $arrayConfig;
    }

    public function formatPrice($price)
    {
        return number_format(floatval($price), 2, '.', '');
    }

    /**
     * Retorna las opciones de tipo de producto
     * @return array
     */
    public function getTipoProductoOptions(): array
    {
        $arrayConfig = [];
        $urls_params = $this->getConfig('segmento_empresas_configuracion/tipo_producto/value', $this->request->getParam('store'));
        if ($urls_params) {
            $urls_paramsArray = unserialize($urls_params);
            foreach ($urls_paramsArray as $key => $value) {
                $arrayConfig[] = [
                    'value' => $value['tipo_producto'],
                    'label' => $value['tipo_producto']
                ];
            }
        }

        return $arrayConfig;
    }
}
