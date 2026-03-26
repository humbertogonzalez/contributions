<?php

namespace BalloonGroup\Configurations\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class UpdateAdminUserLocale implements DataPatchInterface
{
    private $moduleDataSetup;
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->update(
            $this->moduleDataSetup->getTable('admin_user'),
            ['interface_locale' => 'es_AR'],
            ['1 = ?' => 1] // Updates all admin users
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
