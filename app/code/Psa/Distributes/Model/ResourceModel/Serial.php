<?php
namespace Psa\Distributes\Model\ResourceModel;


class Serial extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('balloongroup_distributes_serial', 'serial_id');
	}
	
}