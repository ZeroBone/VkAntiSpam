<?php

namespace VkAntiSpam\System;

use VkAntiSpam\VkAntiSpam;
use PDO;

class TextClassifier {

    const CATEGORY_INVALID = 0;

    const CATEGORY_HAM = 1;

    const CATEGORY_SPAM = 2;

    /**
     * AntiSpamSystem constructor;
     */
    public function __construct() {}

    private function tokenize($text) {

        $filteredWords = ['и', 'но', 'или', 'да', 'нет', 'за', 'что', 'как', 'это', 'эти', 'те', 'то', 'кто'];
        // TODO: get filtered word from config

        $text = preg_replace('/[^a-zA-Z0-9А-ЯЁа-яё ]+/u', '', $text);
        $text = mb_strtolower($text, 'utf-8');

        $keywords = [];

        $token = strtok($text, ' ');

        while ($token !== false) {

            if (mb_strlen($token, 'utf-8') > 2 && !in_array($token, $filteredWords)) {
                $keywords[] = $token;
            }

            $token = strtok(' ');

        }

        return $keywords;

    }

    public function learn($text, $category) {

        if (static::textInvalid($text)) {
            return false;
        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('INSERT INTO `trainingSet` (`document`, `category`) VALUES (?, ?);');
        $query->execute([
            $text,
            $category
        ]);

        // $sql = mysqli_query($conn, "INSERT into trainingSet (document, category) values('$sentence', '$category')");

        $keywords = $this->tokenize($text);

        $presenceCheckQuery = $db->prepare('SELECT COUNT(*) AS `total` FROM `wordFrequency` WHERE `word` = ? and `category` = ?;');

        $insertQuery = $db->prepare('INSERT INTO `wordFrequency` (`word`, `category`, `count`) VALUES (?, ?, 1);');

        $updateQuery = $db->prepare('UPDATE wordFrequency SET `count` = `count` + 1 WHERE `word` = ?;');

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

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `trainingSet` WHERE `category` = ?;');

        $query->execute([TextClassifier::CATEGORY_SPAM]);
        $spamCount = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        $query->execute([TextClassifier::CATEGORY_HAM]);
        $hamCount = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        $query = $db->query('SELECT COUNT(*) AS `count` FROM `trainingSet`;');
        $totalCount = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        if ($totalCount === 0) {
            // not enouph information
            return TextClassifier::CATEGORY_HAM;
        }

        $fpSpam = $spamCount / $totalCount;

        $fpHam = $hamCount / $totalCount;

        // get the number of distinct word
        // it is required by the laplace smoothing algo

        $query = $db->query('SELECT COUNT(*) AS `count` FROM `wordFrequency`;');
        $distinctWords = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

        $spammieness = log($fpSpam);

        $countLookupQuery = $db->prepare('SELECT `count` FROM `wordFrequency` WHERE `word` = ? AND `category` = ?;');

        foreach ($keywords as $keyword) {

            $countLookupQuery->execute([
                $keyword,
                TextClassifier::CATEGORY_SPAM
            ]);

            $wordCount = (int)$countLookupQuery->fetch(PDO::FETCH_ASSOC)['count'];

            $spammieness += log(($wordCount + 1) / ($spamCount + $distinctWords));

        }

        $hammieness = log($fpHam);

        foreach ($keywords as $keyword) {

            $countLookupQuery->execute([
                $keyword,
                TextClassifier::CATEGORY_HAM
            ]);

            $wordCount = (int)$countLookupQuery->fetch(PDO::FETCH_ASSOC)['count'];

            $hammieness += log(($wordCount + 1) / ($hamCount + $distinctWords));

        }

        $category = ($hammieness >= $spammieness) ? TextClassifier::CATEGORY_HAM : TextClassifier::CATEGORY_SPAM;

        return $category;

    }

    public function classify($text) {

        if (static::textInvalid($text)) {
            return static::CATEGORY_INVALID;
        }

        $keywords = $this->tokenize($text);

        return $this->analyze($keywords);

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