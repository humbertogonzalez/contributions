<?php

namespace Psa\Distributes\Setup;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    public function __construct(
        protected readonly EavSetupFactory $eavSetupFactory
    ) {
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'skus_canje',
            [
                'group' => 'Atributos PSA',
                'label' => 'Productos de canje',
                'type' => 'text',
                'input' => 'multiselect',
                'source' => 'Psa\Distributes\Model\Config\Product\Skus',
                'required' => false,
                'sort_order' => 30,
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'used_in_product_listing' => true,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'visible_on_front' => false
            ]
        );
        $setup->endSetup();
    }
}
