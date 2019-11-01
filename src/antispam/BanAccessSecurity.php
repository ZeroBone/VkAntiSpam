<?php

namespace VkAntiSpam\System;

use VkAntiSpam\VkAntiSpam;

class BanAccessSecurity {

    const USER_ID_KEY = 'u';
    const BAN_ID_KEY = 'v';
    const BAN_TIME_KEY = 't';
    const SIGNATURE_KEY = 'i';

    public $userVkId;
    public $banId;
    public $banTime;

    /**
     * BanAccessSecurity constructor.
     * @param $userVkId
     * @param $banId
     * @param $banTime
     */
    private function __construct($userVkId, $banId, $banTime) {
        $this->userVkId = $userVkId;
        $this->banId = $banId;
        $this->banTime = $banTime;
    }

    public static function hashBan($userVkId, $banId, $banTime) {

        return hash('sha256', $banTime . $userVkId .
            VkAntiSpam::get()->config->linkAccessSecretKey
            . $banId);

    }

    public static function getBan() {

        $userVkId = isset($_REQUEST[static::USER_ID_KEY]) ? (int)$_REQUEST[static::USER_ID_KEY] : 0;

        $banId = isset($_REQUEST[static::BAN_ID_KEY]) ? (int)$_REQUEST[static::BAN_ID_KEY] : 0;

        $banTime = isset($_REQUEST[static::BAN_TIME_KEY]) ? (int)$_REQUEST[static::BAN_TIME_KEY] : 0;

        if ($userVkId <= 0 || $banId <= 0 || $banTime <= 0) {
            return null;
        }

        $userSignature = isset($_REQUEST[static::SIGNATURE_KEY]) ? (string)$_REQUEST[static::SIGNATURE_KEY] : '';

        if (strlen($userSignature) !== 64) {
            return null;
        }

        // hm... looks ok
        // check the signature

        $knownSignature = static::hashBan($userVkId, $banId, $banTime);

        if (!hash_equals($knownSignature, $userSignature)) {
            return null;
        }

        // the hash is ok

        return new self($userVkId, $banId, $banTime);

    }

    public function verify($userVkId) {

        return $this->userVkId === $userVkId;

    }

    public static function constructHttpQueryArray($userVkId, $banId, $banTime) {

        $signature = static::hashBan($userVkId, $banId, $banTime);

        return [
            static::USER_ID_KEY => $userVkId,
            static::BAN_ID_KEY => $banId,
            static::BAN_TIME_KEY => $banTime,
            static::SIGNATURE_KEY => $signature
        ];

    }

}