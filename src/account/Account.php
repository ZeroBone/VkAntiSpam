<?php

namespace VkAntiSpam\Account;


use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\VkAntiSpam;

class Account {

    const NAME_MAX_LENGTH = 16;
    const PASSWORD_MAX_LENGTH = 50;
    const EMAIL_MAX_LENGTH = 40;

    private $checked = false;

    private $loggedIn;

    public $tokenPayload;

    public function loggedIn() {

        if ($this->checked) {
            return $this->loggedIn;
        }

        if (!isset($_COOKIE['zl'])) {
            $this->checked = true;
            $this->loggedIn = false;
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $_COOKIE['zl'], 3);

        $userSignature = StringUtils::base64UrlDecode($signatureEncoded);

        $realSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            VkAntiSpam::get()->config->account->jwtSecret,
            true
        );

        $this->checked = true;
        $this->loggedIn = hash_equals($realSignature, $userSignature);

        if ($this->loggedIn) {
            $this->tokenPayload = json_decode(StringUtils::base64UrlDecode($payloadEncoded), true);
        }

        return $this->loggedIn;

    }

    public static function generateToken($payload) {

        $header = json_encode([
            'algo' => 'HS256',
            'c' => time()
        ]);

        // $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlHeader = StringUtils::base64UrlEncode($header);

        // $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $base64UrlPayload = StringUtils::base64UrlEncode($payload);

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . '.' . $base64UrlPayload,
            VkAntiSpam::get()->config->account->jwtSecret,
            true
        );

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . StringUtils::base64UrlEncode($signature);

    }

    public static function hashPassword($password, $salt) {
        return hash('sha512', $password . $salt);
    }

}