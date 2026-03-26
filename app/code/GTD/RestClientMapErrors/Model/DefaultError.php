<?php

namespace Balloon\RestClientMapErrors\Model;

use Balloon\RestClientMapErrors\Api\Data\DefaultErrorInterface;
use Magento\Framework\Model\AbstractModel;

class DefaultError extends AbstractModel implements DefaultErrorInterface
{
    protected $_eventPrefix = 'balloon_default_error_model';
    const CACHE_TAG = 'balloon_default_error' ;

    protected function _construct()
    {
        $this->_init(ResourceModel\DefaultError::class);
    }

    public function getIdentities(): array
    {
        return [ self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    public function getTypeMap(): int
    {
        return $this->getData(self::TYPE_MAP);
    }

    public function setTypeMap(int $typeMap): DefaultErrorInterface
    {
        $this->setData(self::TYPE_MAP, $typeMap);
        return $this;
    }

    public function getBlockCode(): string
    {
        return $this->getData(self::BLOCK_CODE);
    }

    public function setBlockCode(string $blockCode): DefaultErrorInterface
    {
        $this->setData(self::BLOCK_CODE, $blockCode);
        return $this;
    }
}
