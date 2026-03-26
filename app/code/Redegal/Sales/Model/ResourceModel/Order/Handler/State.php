<?php
namespace Redegal\Sales\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class State extends \Magento\Sales\Model\ResourceModel\Order\Handler\State
{
    const ORDER_TABLE = "sales_order";
    const ORDER_GRID_TABLE = "sales_order_grid";

    /** @var OrderCommentSender */
    protected $sender;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var ResourceConnection */
    protected $connection;

    /**
     * @var array
     */
    protected $statusCodes = [
        'por_recoger',
        'complete',
        'canceled',
        'en_camino',
        'recolectado'

    ];

    /**
     * @param OrderCommentSender $sender
     * @param OrderRepositoryInterface $orderRepository
     * @param ResourceConnection $connection
     */
    public function __construct(
        OrderCommentSender $sender,
        OrderRepositoryInterface $orderRepository,
        ResourceConnection $connection
    ) {
        $this->sender = $sender;
        $this->orderRepository = $orderRepository;
        $this->connection = $connection;
    }

    public function check(Order $order)
    {
        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('> Update Status de pedido');
        $currentState = $order->getState();
        $currentDate = date("Y-m-d H:i:s");
        $completeFlag = false;
        $sendFlag = true;
        $shipmentDate = false;

        if ($currentState == Order::STATE_NEW && $order->getIsInProcess()) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $currentState = Order::STATE_PROCESSING;
        }

        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice()) {
            if (in_array($currentState, [Order::STATE_PROCESSING, Order::STATE_COMPLETE])
                && !$order->canCreditmemo()
                && !$order->canShip()
            ) {
                $order->setState(Order::STATE_CLOSED)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CLOSED));
            } elseif ($currentState === Order::STATE_PROCESSING && !$order->canShip()) {
                //$order->setState(Order::STATE_COMPLETE)
                    //->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));
            }
        }

        if($order->getShipmentsCollection()) {
            foreach($order->getShipmentsCollection() as $shipment){
                $shipmentDate = $shipment->getCreatedAt();
            }

            if( (strtotime($currentDate) - strtotime($shipmentDate)) > 10) {
                $completeFlag = true;
            }
        }

        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('> Pedido: ' . $order->getIncrementId());
        \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info('> Status: ' . $order->getStatus());

        if ($order->getEntityId() && in_array($order->getStatus(), $this->statusCodes)) {
            $this->updateOrderStatus($order->getEntityId(), $order->getStatus());

            if($order->getStatus() == "complete" && !$completeFlag) {
                $sendFlag = false;
                //$this->updateOrderStatus($order->getEntityId(), "processing");
                //$this->updateOrderState($order->getEntityId(), "processing");
            }

            // Enviamos correo y actualizamos status, ya que envía el status anterior ¿?
            $orderObj = $this->orderRepository->get($order->getEntityId());

            if($sendFlag) {
                \Magento\Framework\App\ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info("> Enviando correo");
                $this->sender->send($orderObj, true);
            }
        }

        return $this;
    }

    private function updateOrderStatus($orderId, $status) {
        $connection  = $this->connection->getConnection();

        // Update sales_order
        $data = ["status" => $status];
        $where = ["entity_id = ?" => $orderId];
        $tableName = $connection->getTableName(self::ORDER_TABLE);
        $connection->update($tableName, $data, $where);

        // Update sales_order_grid
        $data = ["status" => $status];
        $where = ["entity_id = ?" => $orderId];
        $tableName = $connection->getTableName(self::ORDER_GRID_TABLE);
        $connection->update($tableName, $data, $where);
    }

    private function updateOrderState($orderId, $state) {
        $connection  = $this->connection->getConnection();

        // Update sales_order
        $data = ["state" => $state];
        $where = ["entity_id = ?" => $orderId];
        $tableName = $connection->getTableName(self::ORDER_TABLE);
        $connection->update($tableName, $data, $where);
    }
}
