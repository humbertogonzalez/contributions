<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Model;

use BalloonGroup\Company\Api\Data\UserInterface;
use Magento\User\Model\User as MagentoUser;

class User extends MagentoUser implements UserInterface
{
    /**
     * Get custom field
     *
     * @return string|null
     */
    public function getSellerId()
    {
        return $this->_getData('seller_id');
    }

    /**
     * Set custom field
     *
     * @param string|null $sellerId
     * @return $this
     */
    public function setSellerId($sellerId)
    {
        return $this->setData('seller_id', $sellerId);
    }
}
