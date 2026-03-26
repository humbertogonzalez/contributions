<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Api\Data;

use Magento\User\Api\Data\UserInterface as MagentoUserInterface;

interface UserInterface extends MagentoUserInterface
{
    /**
     * Get seller_id field
     *
     * @return string|null
     */
    public function getSellerId();

    /**
     * Set seller_id field
     *
     * @param string|null $sellerId
     * @return $this
     */
    public function setSellerId($sellerId);
}
