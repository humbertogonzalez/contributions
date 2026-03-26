<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Helper;

use Amasty\RequestQuote\Helper\Data as AmastyHelper;

class Data extends AmastyHelper
{
    /**
     * @return bool
     */
    public function isAllowedCustomerGroup(): bool
    {
        return true;
    }
}
