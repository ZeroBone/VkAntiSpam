<?php

namespace VkAntiSpam\Utils;


class StringUtils {

    public static function timeToString($timestamp, $format='Y.m.d H:i:s') {

        return date($format, $timestamp);
    }

    public static function getStringLength($line) {

        return mb_strlen($line, 'utf-8');
    }

    public static function stringStartsWith($haystack, $needle) {

        return (
            mb_substr($haystack, 0, static::getStringLength($needle), 'utf-8') === $needle
        );
    }

    public static function getStringSubString($line, $start=0, $length=null) {

        return mb_substr($line, $start, $length, 'utf-8');
    }

    public static function stringHasSubstring($line, $subString) {

        return mb_strpos($line, $subString, 0, 'utf-8') !== false;

    }

    public static function fillStringTo($line, $length, $fillChar=' ', $direction='right') {

        if ($direction === 'left') {

            $direction = STR_PAD_LEFT;
        }
        else {

            $direction = STR_PAD_RIGHT;
        }

        return str_pad($line, $length, $fillChar, $direction);

    }

    public static function stringSplit($str, $len = 1) {

        $fragments = [];

        $strLen = static::getStringLength($str);

        for ($i = 0; $i < $strLen; $i++) {

            $fragments[] = static::getStringSubString($str, $i, $len);

        }

        return $fragments;

    }

}