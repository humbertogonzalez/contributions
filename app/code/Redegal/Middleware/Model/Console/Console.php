<?php

// -------------------------------------------------------
// include_once 'console.php';
// // ::log method usage
// // -------------------------------------------------------
// Console::log('Im Red!', 'red');
// Console::log('Im Blue on White!', 'white', true, 'blue');
// Console::log('I dont have an EOF', false);
// Console::log("\tThis is where I come in.", 'light_green');
// Console::log('You can swap my variables', 'black', 'yellow');
// Console::log(str_repeat('-', 60));
// // Direct usage
// // -------------------------------------------------------
// echo Console::blue('Blue Text') . "\n";
// echo Console::black('Black Text on Magenta Background', 'magenta') . "\n";
// echo Console::red('Im supposed to be red, but Im reversed!', 'reverse') . "\n";
// echo Console::red('I have an underline', 'underline') . "\n";
// echo Console::blue('I should be blue on light gray but Im reversed too.', 'light_gray', 'reverse') . "\n";
// // Ding!
// // -------------------------------------------------------
// echo Console::bell();
//
/**
 * PHP Colored CLI
 * Used to log strings with custom colors to console using php
 *
 * Copyright (C) 2013 Sallar Kaboli <sallar.kaboli@gmail.com>
 * MIT Liencesed
 * http://opensource.org/licenses/MIT
 *
 * Original colored CLI output script:
 * (C) Jesse Donat https://github.com/donatj
 */

namespace Redegal\Middleware\Model\Console;

class Console
{
    public static $active = false;

    private static $foregroundColors = array(
        'bold'         => '1',    'dim'          => '2',
        'black'        => '0;30', 'dark_gray'    => '1;30',
        'blue'         => '0;34', 'light_blue'   => '1;34',
        'green'        => '0;32', 'light_green'  => '1;32',
        'cyan'         => '0;36', 'light_cyan'   => '1;36',
        'red'          => '0;31', 'light_red'    => '1;31',
        'purple'       => '0;35', 'light_purple' => '1;35',
        'brown'        => '0;33', 'yellow'       => '1;33',
        'light_gray'   => '0;37', 'white'        => '1;37',
        'normal'       => '0;39',
    );

    private static $backgroundColors = array(
        'black'        => '40',   'red'          => '41',
        'green'        => '42',   'yellow'       => '43',
        'blue'         => '44',   'magenta'      => '45',
        'cyan'         => '46',   'light_gray'   => '47',
    );

    private static $options = array(
        'underline'    => '4',    'blink'         => '5',
        'reverse'      => '7',    'hidden'        => '8',
    );

    private static $level = array(
        'debug'     => 'cyan',
        'info'      => 'green',
        'warning'   => 'yellow',
        'error'     => 'red'
    );

    private static $start;
    private static $lapse;

    // private static function rutime($index)
    // {
    //     if (!isset(self::$start)) {
    //         self::$start = getrusage();
    //     }
    //     $time = getrusage();
    //     return ($time["ru_$index.tv_sec"]*1000 + intval($time["ru_$index.tv_usec"]/1000))
    //      -  (self::$start["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    // }

    public static function enable()
    {
        return self::$active = true;
    }

    public static function disable()
    {
        return self::$active = false;
    }

    private static function resetTime()
    {
        self::$start = microtime(true);
        self::$lapse = microtime(true);
    }

    private static function rutime($index)
    {
        if (!isset(self::$start)) {
            self::resetTime();
        }
        $times = [number_format((microtime(true) - self::$lapse), 4, '.', ''), number_format((microtime(true) - self::$start), 4, '.', '')];
        self::$lapse = microtime(true);
        return $times;
    }

    private static function memory()
    {
        return round((memory_get_usage(false)/1024/1024), 2)." Mb / " .round((memory_get_usage(true)/1024/1024), 2)." Mb";
    }

    private static function memorypeak()
    {
        return "Mem ".round((memory_get_peak_usage(false)/1024/1024), 2)." Mb / " .round((memory_get_peak_usage(true)/1024/1024), 2)." Mb";
    }

    /**
     * Logs a string to console.
     * @param  string  $str        Input String
     * @param  string  $color      Text Color
     * @param  boolean $newline    Append EOF?
     * @param  [type]  $background Background Color
     * @return [type]              Formatted output
     */
    public static function log($str = '', $color = 'normal', $background_color = null)
    {
        return self::$color($str, $background_color);
    }

    /**
     * Anything below this point (and its related variables):
     * Colored CLI Output is: (C) Jesse Donat
     * https://gist.github.com/donatj/1315354
     * -------------------------------------------------------------
     */

    /**
     * Catches static calls (Wildcard)
     * @param  string $foregroundColor Text Color
     * @param  array  $args             Options
     * @return string                   Colored string
     */
    public static function __callStatic($foregroundColor, $args)
    {
        if (php_sapi_name() != 'cli') {
            return false;
        }

        $string         = $args[0];
        $inline         = false;
        $time           = true;
        $memory         = false;
        $memorypeak     = false;
        $json           = false;
        $extraString   = "";
        $coloredString = "";

        if (!self::$active || php_sapi_name() != 'cli') {
            return;
        }

        if (isset(self::$level[$foregroundColor])) {
            $foregroundColor = self::$level[$foregroundColor];
        }

        if (isset(self::$foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . self::$foregroundColors[$foregroundColor] . "m";
        } else {
            die($foregroundColor . ' not a valid color');
        }

        array_shift($args);
        foreach ($args as $option) {
            // Check if given background color found
            if (isset(self::$backgroundColors[$option])) {
                $coloredString .= "\033[" . self::$backgroundColors[$option] . "m";
            } elseif (isset(self::$options[$option])) {
                $coloredString .= "\033[" . self::$options[$option] . "m";
            } elseif ($option == 'inline') {
                $inline = true;
            } elseif ($option == 'notime') {
                $time = false;
            } elseif ($option == 'reset') {
                self::resetTime();
            } elseif ($option == 'memory') {
                $memory = true;
            } elseif ($option == 'memorypeak') {
                $memorypeak = true;
            } elseif ($option == 'json') {
                $json = true;
            }
        }

        $json = is_string($string) ? false : true;

        if ($json) {
            $string = json_encode($string, JSON_PRETTY_PRINT);
        }

        if ($time) {
            $times = self::rutime("utime");
            $extraString.= "[".$times[1]."-".$times[0]."] ";
        }
        if ($memory) {
            $extraString.= "[".self::memory()."] ";
        }
        if ($memorypeak) {
            $extraString.= "[".self::memorypeak()."] ";
        }

        $extraString = "\033[0;33m".$extraString."\033[0m";
        $coloredString .= $string . "\033[0m";
        if ($inline == false) {
            $coloredString .= PHP_EOL;
        }
        echo $extraString.$coloredString;
        return $extraString.$coloredString;
    }

    /**
     * Plays a bell sound in console (if available)
     * @param  integer $count Bell play count
     * @return string         Bell play string
     */
    public static function bell($count = 1)
    {
        echo str_repeat("\007", $count);
    }
}
