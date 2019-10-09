<?php

namespace VkAntiSpam\System;

class BanAccessSecurity {

    private function __construct() {}

    public static function hashBan($userVkId, $messageId, $banId, $banTime) {

        return hash('sha256', $userVkId . $messageId . $banId . $banTime);

    }

}