<?php
namespace Redegal\Middleware\Model\ResourceModel\InventoryReservations;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\InventoryReservationsApi\Model\CleanupReservationsInterface;

class CustomCleanupReservations implements CleanupReservationsInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Products skus
     *
     * @var array
     */
    private $skus;

    /**
     * @param ResourceConnection $resource
     * @param int $groupConcatMaxLen
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        if (empty($this->skus)) {
            throw new \Exception('[Custom Clean Reservations] Its necessary set skus');
        }

        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');
        $condition = [ReservationInterface::SKU . ' IN (?)' => $this->skus];
        $connection->delete($reservationTable, $condition);
    }

    public function setSkus(array $skus)
    {
        $this->skus = $skus;
    }
}