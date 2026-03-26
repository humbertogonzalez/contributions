<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Api\Data;

interface UmeInterface
{
    /** @var string */
    public const UME = 'ume';
    public const SKU = 'sku';
    public const UME_ID = 'ume_id';

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Set sku
     *
     * @param string $sku
     * @return UmeInterface
     */
    public function setSku(string $sku): UmeInterface;

    /**
     * Get ume
     *
     * @return string|null
     */
    public function getUme(): ?string;

    /**
     * Set ume
     *
     * @param string $ume
     * @return UmeInterface
     */
    public function setUme(string $ume): UmeInterface;
}
