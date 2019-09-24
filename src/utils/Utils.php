<?php

namespace VkAntiSpam\Utils;


class Utils {

    public static function getUserIpAddress() {

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        else {
            return $_SERVER['REMOTE_ADDR'];
        }

    }

    public static function redirect($url) {

        header('Location: ' . $url);

    }

}