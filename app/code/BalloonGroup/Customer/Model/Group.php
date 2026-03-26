<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Model;

use Magento\Customer\Model\Group as MagentoGroup;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoresConfig;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Tax\Model\ClassModelFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Group extends MagentoGroup
{
    const GROUP_CODE_MAX_LENGTH = 100;

    /**
     * Group constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoresConfig $storesConfig
     * @param DataObjectProcessor $dataObjectProcessor
     * @param ClassModelFactory $classModelFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoresConfig $storesConfig,
        DataObjectProcessor $dataObjectProcessor,
        ClassModelFactory $classModelFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $registry,
            $storesConfig,
            $dataObjectProcessor,
            $classModelFactory
        );
    }

    /**
     * Prepare customer group data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        $this->setCode(substr($this->getCode(), 0, self::GROUP_CODE_MAX_LENGTH));
        return $this;
    }
}
