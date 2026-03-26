<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Ume\Model;

use BalloonGroup\Ume\Api\Data\UmeInterface;
use Magento\Framework\Model\AbstractModel;

class Ume extends AbstractModel implements UmeInterface
{
    /**
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(ResourceModel\Ume::class);
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->getData(self::SKU);
    }

    /**
     * Set sku
     *
     * @param string $sku
     * @return UmeInterface
     */
    public function setSku(string$sku): UmeInterface
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Get ume
     *
     * @return string|null
     */
    public function getUme(): ?string
    {
        return $this->getData(self::UME);
    }

    /**
     * Set Ume
     *
     * @param string $ume
     * @return UmeInterface
     */
    public function setUme(string $ume): UmeInterface
    {
        return $this->setData(self::UME, $ume);
    }
}
