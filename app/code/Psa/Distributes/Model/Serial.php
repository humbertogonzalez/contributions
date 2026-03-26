<?php

namespace BalloonGroup\Distributes\Model;

class Serial extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'balloongroup_distributes_serial';

	protected $_cacheTag = 'balloongroup_distributes_serial';

	protected $_eventPrefix = 'balloongroup_distributes_serial';

	protected function _construct()
	{
		$this->_init('BalloonGroup\Distributes\Model\ResourceModel\Serial');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
