<?php

namespace VkAntiSpam\System;

use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\VkAntiSpam;
use PDO;

class TextClassifier {

    const CATEGORY_INVALID = 0;

    const CATEGORY_HAM = 1;

    const CATEGORY_SPAM = 2;

    const CATEGORY_DELETED = 3;

    /**
     * AntiSpamSystem constructor;
     */
    public function __construct() {}

    private function tokenize($text) {

        $neutralTokens = ['и', 'но', 'или', 'да', 'нет', 'за', 'что', 'как', 'это', 'эти', 'те', 'то', 'кто', 'так'];

        $text = preg_replace('/[^a-zA-Z0-9А-ЯЁа-яё ]+/u', '', $text);
        $text = mb_strtolower($text, 'utf-8');

        $keywords = [];

        $token = strtok($text, ' ');

        while ($token !== false) {

            if (mb_strlen($token, 'utf-8') > 2 && !in_array($token, $neutralTokens)) {
                $keywords[] = $token;
            }

            $token = strtok(' ');

        }

        return $keywords;

    }

    public function learn($text, $category) {

        $text = static::cleanText($text);

        if (static::textInvalid($text)) {
            return false;
        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('INSERT INTO `trainingSet` (`text`, `category`) VALUES (?, ?);');
        $query->execute([
            $text,
            $category
        ]);

        $keywords = $this->tokenize($text);

        $presenceCheckQuery = $db->prepare('SELECT COUNT(*) AS `total` FROM `words` WHERE `word` = ? AND `category` = ?;');

        $insertQuery = $db->prepare('INSERT INTO `words` (`word`, `category`, `count`) VALUES (?, ?, 1);');

        $updateQuery = $db->prepare('UPDATE `words` SET `count` = `count` + 1 WHERE `word` = ?;');

        foreach ($keywords as $keyword) {

            // if the word is already in the database, increment counter
            // otherwise insert the word in the database

            $presenceCheckQuery->execute([$keyword, $category]);

            $count = (int)$presenceCheckQuery->fetch(PDO::FETCH_ASSOC)['total'];

            if ($count === 0) {

                $insertQuery->execute([$keyword, $category]);

            }
            else {

                $updateQuery->execute([$keyword]);

            }

        }

        return true;

    }

    private function analyze($keywords) {

        if (count($keywords) === 0) {
            return TextClassifier::CATEGORY_HAM;
        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `trainingSet`;');

        $query->execute();

        $totalCount = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        $spamChance = .5;
        $hamChance = .5;

        $spamCount = floor($totalCount * $spamChance);
        $hamCount = floor($totalCount * $hamChance);

        if ($spamCount === 0 || $hamCount === 0) {
            // not enouph information
            return TextClassifier::CATEGORY_HAM;
        }

        // get the number of distinct word
        // it is required by the laplace smoothing algo

        $query = $db->query('SELECT COUNT(*) AS `count` FROM `words`;');
        $distinctWords = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        $spammieness = log($spamChance);

        $countLookupQuery = $db->prepare('SELECT `count` FROM `words` WHERE `word` = ? AND `category` = ?;');

        foreach ($keywords as $keyword) {

            $countLookupQuery->execute([
                $keyword,
                TextClassifier::CATEGORY_SPAM
            ]);

            $wordCount = (int)$countLookupQuery->fetch(PDO::FETCH_ASSOC)['count'];

            $spammieness += log(($wordCount + 1) / ($spamCount + $distinctWords));

        }

        $hammieness = log($hamChance);

        foreach ($keywords as $keyword) {

            $countLookupQuery->execute([
                $keyword,
                TextClassifier::CATEGORY_HAM
            ]);

            $wordCount = (int)$countLookupQuery->fetch(PDO::FETCH_ASSOC)['count'];

            $hammieness += log(($wordCount + 1) / ($hamCount + $distinctWords));

        }

        if ($hammieness >= $spammieness) {
            return TextClassifier::CATEGORY_HAM;
        }

        return TextClassifier::CATEGORY_SPAM;

    }

    public function classify($text) {

        $text = static::cleanText($text);

        if (static::textInvalid($text)) {
            return static::CATEGORY_INVALID;
        }

        $keywords = $this->tokenize($text);

        return $this->analyze($keywords);

    }

    public static function cleanText($text) {

        return StringUtils::removeEmoji($text);

    }

    public static function textInvalid($text) {

        if (preg_match('/[^\.\,\-\_\'\"\@\?\!\:\$\+ a-zA-Z0-9А-ЯЁа-яё()]/u', $text)) {
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