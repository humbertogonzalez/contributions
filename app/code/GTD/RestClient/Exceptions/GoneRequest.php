<?php

namespace Balloon\RestClient\Exceptions;

use Magento\Framework\Exception\LocalizedException;

class GoneRequest extends  LocalizedException
{
    const MALFORMED_MSG = 'Malformed Response';
    const ERROR_MSG = 'Code Response Not Success';
    const UNEXPECTED_ERROR = 'Unexpected Error';
}
