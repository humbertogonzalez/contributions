<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\ValidateException;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

class AddCustomerAttributes implements DataPatchInterface
{
    /** @var string */
    public const IS_FAKE_EMAIL = "is_fake_email";
    public const RELATED_EMAILS = "related_emails";

    /**
     * AddCustomerAttributes constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly CustomerSetupFactory $customerSetupFactory,
        private readonly AttributeSetFactory $attributeSetFactory
    ) {

    }

    /**
     * Get dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Apply patch
     *
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     *
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::IS_FAKE_EMAIL,
            [
                'type'                  => 'int',
                'label'                 => 'Is Fake Email?',
                'input'                 => 'boolean',
                'source'                => Boolean::class,
                'required'              => false,
                'visible'               => true,
                'user_defined'          => true,
                'system'                => false,
                'global'                => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group'                 => 'General',
                'sort_order'            => 900,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                'default'               => Boolean::VALUE_NO,
            ]
        );

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, self::IS_FAKE_EMAIL);

        $attribute->setData(
            'used_in_forms',
            ['adminhtml_customer', 'customer_account_edit', 'customer_account_create']
        );

        $attribute->save();

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::RELATED_EMAILS,
            [
                'type'                  => 'varchar',
                'label'                 => 'Related Emails',
                'input'                 => 'text',
                'required'              => false,
                'visible'               => true,
                'user_defined'          => true,
                'system'                => false,
                'global'                => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group'                 => 'General',
                'sort_order'            => 905,
                'is_used_in_grid'       => true,
                'is_visible_in_grid'    => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true
            ]
        );

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, self::RELATED_EMAILS);

        $attribute->setData(
            'used_in_forms',
            ['adminhtml_customer', 'customer_account_edit', 'customer_account_create']
        );

        $attribute->save();

        $this->moduleDataSetup->endSetup();
    }
}
