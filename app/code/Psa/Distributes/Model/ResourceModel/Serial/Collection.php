<?php
namespace BalloonGroup\Distributes\Model\ResourceModel\Serial;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'serial_id';
	protected $_eventPrefix = 'balloongroup_distributes_serial_collection';
	protected $_eventObject = 'serial_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('BalloonGroup\Distributes\Model\Serial', 'BalloonGroup\Distributes\Model\ResourceModel\Serial');
	}

}
