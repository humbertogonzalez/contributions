<?php

namespace Redegal\Catalog\Block\Product\View;

use Magento\Catalog\Model\Product;

class ProductCertifications extends \Magento\Framework\View\Element\Template
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


    public function getProductCertifications()
    {
        $product = $this->getProduct();
        $certificationsInt = $product->getCertification();
        $certifications = $product->getResource()->getAttribute('certification')->getFrontend()->getValue($product);
        if (empty($certifications)) {
            return null;
        }

        $certificationsArray = explode(",", $certifications);

        return empty($certificationsArray) ? null : $certificationsArray; 
    }

    public function getProductCertificationsImages()
    {
        $certifications = $this->getProductCertifications();

        if (empty($certifications)) {
            return null;
        }

        foreach($certifications as &$certification) {
            $certification = $this->formatCertificationName($certification);
        }

        return $certifications;
    }


    private function formatCertificationName($certification)
    {
        $certification = trim(strtolower($certification));
        $certification = str_replace(' ', '_', $certification);
        $certification = $certification.'.svg';
       
        return $certification;
    }
}
