<?php
namespace BalloonGroup\OrdersByDealer\Model\ResourceModel;

use Magento\Framework\Math\Random;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\ResourceModel\EntityAbstract as SalesResource;
use Magento\Sales\Model\ResourceModel\Order\Handler\State as StateHandler;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Flat sales order resource
 */
class Order extends SalesResource implements OrderResourceInterface
{

    protected $_eventPrefix = 'sales_order_resource';
    protected $_eventObject = 'resource';
    protected StateHandler $stateHandler;

    protected function _construct(): void
    {
        $this->_init('sales_order', 'entity_id');
    }

    public function __construct(
        Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Attribute $attribute,
        Manager $sequenceManager,
        StateHandler $stateHandler,
        $connectionName = null
    ) {
        $this->stateHandler = $stateHandler;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $connectionName
        );
    }

    /**
     * Count existent products of order items by specified product types
     */
    public function aggregateProductsByTypes($orderId, $productTypeIds = [], $isProductTypeIn = false): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['o' => $this->getTable('sales_order_item')],
                ['o.product_type', new \Zend_Db_Expr('COUNT(*)')]
            )
            ->where('o.order_id=?', $orderId)
            ->where('o.product_id IS NOT NULL')
            ->group('o.product_type');
        if ($productTypeIds) {
            $select->where(
                sprintf(
                    '(o.product_type %s (?))',
                    $isProductTypeIn ? 'IN' : 'NOT IN'
                ),
                $productTypeIds
            );
        }
        return $connection->fetchPairs($select);
    }

    /**
     * Process items dependency for new order, returns qty of affected items;
     *
     * @param \Magento\Sales\Model\Order $object
     * @return int
     */
    protected function calculateItems(\Magento\Sales\Model\Order $object)
    {
        $itemsCount = 0;
        if (!$object->getId()) {
            foreach ($object->getAllItems() as $item) {
                /** @var  \Magento\Sales\Model\Order\Item $item */
                $parent = $item->getQuoteParentItemId();
                if ($parent && !$item->getParentItem()) {
                    $item->setParentItem($object->getItemByQuoteItemId($parent));
                }
                $childItems = $item->getChildrenItems();
                if (empty($childItems)) {
                    $itemsCount++;
                }
            }
        }
        return $itemsCount;
    }

    /**
     * Before save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            /** @var \Magento\Store\Model\Store $store */
            $store = $object->getStore();
            $name = [
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName(),
            ];
            $object->setStoreName(implode(PHP_EOL, $name));
            $object->setTotalItemCount($this->calculateItems($object));
            $object->setData(
                'protect_code',
                substr(
                    hash('sha256', uniqid(Random::getRandomNumber(), true) . ':' . microtime(true)),
                    5,
                    32
                )
            );
        }
        $isNewCustomer = !$object->getCustomerId() || $object->getCustomerId() === true;
        if ($isNewCustomer && $object->getCustomer()) {
            $object->setCustomerId($object->getCustomer()->getId());
        }
        return parent::_beforeSave($object);
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order $object */
        $this->stateHandler->check($object);
        return parent::save($object);
    }
}

