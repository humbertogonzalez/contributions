<?php
/**
 * Created by PhpStorm.
 * User: miguel
 * Date: 3/05/18
 * Time: 08:55 AM
 */
namespace Redegal\Sepomex\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('redegal_sepomex_sepomex'),
                'D_mnpio',
                'd_mnpio',
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "size" =>255,
                ]
            );

            $setup->getConnection()->changeColumn(
                $setup->getTable("redegal_sepomex_sepomex"),
                'd_CP',
                'd_cp',
                [
                    "type" => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    "size" => 5,
                ]
            );
        }

        $setup->endSetup();
    }
}
