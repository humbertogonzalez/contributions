<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Empresas\FlujoComboProductos\Api\CombosRepositoryInterface;

class Combos extends Template
{
    protected $productCollectionFactory;
    private $searchCriteriaBuilder;
    private $combos;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */

    public function __construct(
        Context                   $context,
        CollectionFactory         $productCollectionFactory,
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        CombosRepositoryInterface $combos,
        array                     $data = []
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->combos = $combos;
        parent::__construct($context, $data);
    }

    public function getComboById($id)
    {
       return $this->combos->get($id);
    }

    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        return $collection;
    }
}
