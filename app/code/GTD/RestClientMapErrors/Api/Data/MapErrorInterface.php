<?php

namespace Balloon\RestClientMapErrors\Api\Data;

use Magento\Framework\DataObject\IdentityInterface;

interface MapErrorInterface extends IdentityInterface
{
    const ENTITY_ID = 'entity_id';
    const TYPE_MAP = 'type_map';
    const CODE_RESPONSE = 'code_response';
    const BLOCK_CODE = 'block_code';

    public function getTypeMap(): int;
    public function setTypeMap(int $typeMap): MapErrorInterface;

    public function getCodeResponse(): int;
    public function setCodeResponse(int $codeResponse): MapErrorInterface;

    public function getBlockCode(): string;
    public function setBlockCode(string $blockCode): MapErrorInterface;
}
