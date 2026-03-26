<?php

namespace Balloon\RestClientMapErrors\Model;

use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Magento\Framework\Model\AbstractModel;

class MapError extends AbstractModel implements MapErrorInterface
{
    protected $_eventPrefix = 'balloon_map_error_model';
    const CACHE_TAG = 'balloon_map_error' ;

    protected function _construct()
    {
        $this->_init(ResourceModel\MapError::class);
    }

    public function getIdentities(): array
    {
        return [ self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    public function getTypeMap(): int
    {
        return $this->getData(self::TYPE_MAP);
    }

    public function setTypeMap(int $typeMap): MapErrorInterface
    {
        $this->setData(self::TYPE_MAP, $typeMap);
        return $this;
    }

    public function getCodeResponse(): int
    {
        return $this->getData(self::CODE_RESPONSE);
    }

    public function setCodeResponse(int $codeResponse): MapErrorInterface
    {
        $this->setData(self::CODE_RESPONSE, $codeResponse);
        return $this;
    }

    public function getBlockCode(): string
    {
        return $this->getData(self::BLOCK_CODE);
    }

    public function setBlockCode(string $blockCode): MapErrorInterface
    {
        $this->setData(self::BLOCK_CODE, $blockCode);
        return $this;
    }
}
