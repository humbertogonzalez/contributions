<?php

declare(strict_types=1);

namespace BalloonGroup\Customer\Model;

use Magento\Customer\Model\GroupManagement as MagentoGroupManagement;

class GroupManagement extends MagentoGroupManagement
{
    const GROUP_CODE_MAX_LENGTH = 100;
}
