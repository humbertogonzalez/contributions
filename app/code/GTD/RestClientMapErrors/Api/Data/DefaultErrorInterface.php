<?php

namespace Balloon\RestClientMapErrors\Api\Data;

use Magento\Framework\DataObject\IdentityInterface;

interface DefaultErrorInterface extends IdentityInterface
{
    const ENTITY_ID = 'entity_id';
    const TYPE_MAP = 'type_map';
    const BLOCK_CODE = 'block_code';

    public function getTypeMap(): int;
    public function setTypeMap(int $typeMap): DefaultErrorInterface;

    public function getBlockCode(): string;
    public function setBlockCode(string $blockCode): DefaultErrorInterface;
}
