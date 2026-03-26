<?php

namespace Redegal\Middleware\Model\Helper;

class DateHelper
{
    public static function getNowInTimeZone($timezone)
    {
        return new \DateTime("now", new \DateTimeZone($timezone));
    }

    public static function getNowInMadrid()
    {
        return self::getNowInTimeZone('Europe/Madrid');
    }
}
