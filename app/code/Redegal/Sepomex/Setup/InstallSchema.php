<?php


namespace Redegal\Sepomex\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $table_redegal_sepomex_sepomex = $setup->getConnection()->newTable($setup->getTable('redegal_sepomex_sepomex'));

        
        $table_redegal_sepomex_sepomex->addColumn(
            'sepomex_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            array('identity' => true,'nullable' => false,'primary' => true,'unsigned' => true,),
            'Entity ID'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_codigo',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'd_codigo'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_asenta',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'd_asenta'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_tipo_asenta',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => False],
            'd_tipo_asenta'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'D_mnpio',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'D_mnpio'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_estado',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'd_estado'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_ciudad',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => '1'],
            'd_ciudad'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'd_CP',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'd_CP'
        );
        

        
        $table_redegal_sepomex_sepomex->addColumn(
            'c_estado',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'c_estado'
        );
        

        $setup->getConnection()->createTable($table_redegal_sepomex_sepomex);

        $setup->endSetup();
    }
}
