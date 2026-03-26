<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Configurations\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_WHATSAPP_NUMBERS = 'configurations/whatsapp/telephone';

    /**
     * Get the whatsapp number by store
     *
     * @return string
     */
    public function getWhatsappNumberByStore(): string
    {
        return "https://wa.me/" . $this->scopeConfig->getValue(
            self::XML_PATH_WHATSAPP_NUMBERS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
