<?php

declare(strict_types=1);

namespace BalloonGroup\RequestQuote\Helper;

use Amasty\RequestQuote\Model\Quote;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use BalloonGroup\RequestQuote\Model\Pdf\CreatePdf;

class Data extends AbstractHelper
{
    /**
     * Data constructor
     *
     * @param Context $context
     * @param CreatePdf $createPdf
     */
    public function __construct(
        Context $context,
        private readonly CreatePdf $createPdf
    ) {
        parent::__construct($context);
    }

    public function getQuotePdfPath(Quote $quote)
    {
        return $this->createPdf->createQuotePdf($quote);
    }
}
