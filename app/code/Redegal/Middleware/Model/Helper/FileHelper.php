<?php

namespace Redegal\Middleware\Model\Helper;

/**
 * FileHelper class
 */
class FileHelper
{
    /**
     * Join an Array with directory separator
     * @param  array $path
     * @return string       The joined path
     */
    public static function joinPath($path)
    {
        if (is_string($path)) {
            return $path;
        }
        return join(DIRECTORY_SEPARATOR, $path);
    }
}
