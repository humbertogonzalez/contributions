<?php
namespace BalloonGroup\Distributes\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Helper\Image;

class Canje extends Template
{

    private Image $_image;
    private CollectionFactory $_productCollectionFactory;
    private ListProduct $listProductBlock;

    public function __construct(
        Context $context,
        Image $imageHelper,
        CollectionFactory $productCollectionFactory,
        ListProduct $listProductBlock,
        
         array $data = []
         )
    {
        $this->_image = $imageHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->listProductBlock = $listProductBlock;
        
        parent::__construct($context, $data);
    }

    public function getProductCollection(): Collection
    {

        return $this->_productCollectionFactory->create()->addAttributeToSelect('*')->load();
    }

    public function getAddToCartPostParams($product):array
    {   
        

        return $this->listProductBlock->getAddToCartPostParams($product);
    }

    
}
