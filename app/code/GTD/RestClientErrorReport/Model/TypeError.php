<?php

namespace Balloon\RestClientErrorReport\Model;

use Magento\Framework\Data\OptionSourceInterface;

class TypeError implements OptionSourceInterface
{
    const ERRORS = [
        0 => "Info",
        1 => "Debug",
        2 => "Warning",
        3 => "Error"
    ];

    const DEFAULT_ERROR = "Unknown Error";

    public function toOptionArray(): array
    {
        $options = [];
        foreach (self::ERRORS as $error => $errorName) {
            $options[] = [
                "label" => $errorName,
                "value" => $error
            ];
        }
        return $options;
    }
}
