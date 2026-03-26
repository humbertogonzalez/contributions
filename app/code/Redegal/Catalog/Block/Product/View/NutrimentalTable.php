<?php

namespace Redegal\Catalog\Block\Product\View;

use Magento\Catalog\Model\Product;

class NutrimentalTable extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->_coreRegistry->registry('product');
        }
        return $this->product;
    }


    public function getNutrimentalTable()
    {
        $product = $this->getProduct();
        $nutrimentalTable = json_decode($product->getNutrimentalTable(), true);
        if (empty($nutrimentalTable)) {
            return null;
        }

        $nutrimentalData = $nutrimentalTable['Tabla Nutrimental'];

        try {
            unset($nutrimentalData['Datos Nutricionales']);
            unset($nutrimentalData['Alérgenos']);
            unset($nutrimentalData['Ingredientes']);
        } catch(\Exception $e) {}


        $matrixNutrimentalTable = [];
        $matrixNutrimentalTable['headers'][] = "";
        foreach ($nutrimentalData ?? [] as $header => $fields) {
            $matrixNutrimentalTable['headers'][] = $header;
            foreach ($fields as $label => $data) {
                $matrixNutrimentalTable['table'][$label][] = $data;
            }
        }

        return $matrixNutrimentalTable; 
    }
}
