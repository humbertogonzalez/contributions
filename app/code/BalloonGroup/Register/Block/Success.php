<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Register\Block;

use Magento\Framework\View\Element\Template;

class Success extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('register/index/index', ['_secure' => true]);
    }
}
