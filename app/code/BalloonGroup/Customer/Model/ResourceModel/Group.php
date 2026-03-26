<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Model\ResourceModel;

use Magento\Customer\Model\ResourceModel\Group as MagentoResourceModelGroup;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use BalloonGroup\Customer\Model\Group as CustomerGroup;

class Group extends MagentoResourceModelGroup
{

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customersFactory,
        $connectionName = null
    )
    {
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $groupManagement,
            $customersFactory
        );
    }

    /**
     * Prepare data before save
     *
     * @param AbstractModel $group
     * @return $this
     */
    protected function _beforeSave(AbstractModel $group)
    {
        /** @var CustomerGroup $group */
        $group->setCode(substr($group->getCode(), 0, CustomerGroup::GROUP_CODE_MAX_LENGTH));
        return parent::_beforeSave($group);
    }
}
