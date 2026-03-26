<?php

declare(strict_types=1);

namespace BalloonGroup\UpdateOrders\Setup\Patch\Data;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order\Status;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;

class CreateNewOrderStatus implements DataPatchInterface
{
    /**
     * CreateNewOrderStatus constructor
     *
     * @param StatusFactory $statusFactory
     * @param StatusResource $statusResource
     */
    public function __construct(
        private StatusFactory $statusFactory,
        private StatusResource $statusResource
    ) {

    }

    /**
     * Apply Data Patch
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function apply()
    {
        $status = $this->statusFactory->create();
        $status->setData([
            'status' => 'finalized',
            'label' => 'Finalized'
        ]);

        $this->statusResource->save($status);

        $status->assignState('pending', false, true);
    }

    /**
     * Get Aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get Dependencies
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
