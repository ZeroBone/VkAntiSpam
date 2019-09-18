<?php

namespace VkAntiSpam\System;

use VkAntiSpam\VkAntiSpam;

class AntiSpamSystem {

    const HAM = 1;

    const SPAM = 2;

    /**
     * AntiSpamSystem constructor;
     */
    public function __construct() {}

    public function learn($text, $category) {



    }

    public function classify($text) {

        return 0;

    }

    public static function textInvalid($text) {

        if (preg_match('/[^\.\,\-\_\'\"\@\?\!\:\$\+ a-zA-Z0-9А-Яа-я()]/u', $text)) {
            // odd characters
            return true;
        }

        $words = array_filter(explode(' ', mb_strtolower($text, 'utf-8')), function ($el) {
            return $el !== '';
        });

        foreach ($words as $word) {

            preg_match_all( '/[а-яё]/ui', $word, $matches);
            $cyrillicCharacters = count($matches[0]);

            preg_match_all( '/[a-z]/ui', $word, $matches);
            $englishCharacters = count($matches[0]);

            preg_match_all( '/[0-9]/ui', $word, $matches);
            $digitCharacters = count($matches[0]);

            $sum = $cyrillicCharacters + $englishCharacters + $digitCharacters;

            if ($sum !== $cyrillicCharacters && $sum !== $englishCharacters && $sum !== $digitCharacters) {
                return true;
            }

        }

        return false;

    }

}