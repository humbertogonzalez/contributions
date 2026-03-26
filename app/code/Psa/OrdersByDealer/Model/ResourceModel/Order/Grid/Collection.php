<?php
namespace BalloonGroup\OrdersByDealer\Model\ResourceModel\Order\Grid;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use BalloonGroup\OrdersByDealer\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface as Logger;
use Magento\Backend\Model\Auth\Session;

/**
 * Order grid collection
 */
class Collection extends SearchResult
{
    public const STATUSES_NOT_AVAILABLES = ['pending','pay_cancel'];
    /**
     * @var TimezoneInterface
     */
    private $timeZone;
    protected Session $authSession;

    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param TimezoneInterface|null $timeZone
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Session $authSession,
                      $mainTable = 'sales_order_grid',
                      $resourceModel = Order::class,
        TimezoneInterface $timeZone = null
    ) {
        $this->authSession = $authSession;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
        $this->timeZone = $timeZone ?: ObjectManager::getInstance()
            ->get(TimezoneInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $user = $this->getCurrentUser();
        $isDealer = $user->getIsDealer();
        if ($isDealer) {
            $allowedStoreByDealer = $user->getAllowedStoreByDealer();
            $select->join(
                ["aso" => "amasty_storepickup_order"],
                'main_table.entity_id = aso.order_id AND aso.store_id IN ('. $allowedStoreByDealer .')'
            );
        } else {
            $select->where('1=2');
        }
        $select->where('status NOT IN (?)', self::STATUSES_NOT_AVAILABLES);

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }

        return $this;
    }

    public function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'created_at') {
            if (is_array($condition)) {
                foreach ($condition as $key => $value) {
                    $condition[$key] = $this->timeZone->convertConfigTimeToUtc($value);
                }
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
