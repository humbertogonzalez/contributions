<?php

namespace Balloon\RestClientErrorReport\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Log extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'log_report_rest';
    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init('Balloon\RestClientErrorReport\Model\ResourceModel\Logs');
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
