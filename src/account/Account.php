<?php

namespace VkAntiSpam\Account;


use mysql_xdevapi\Exception;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\VkAntiSpam;

class Account {

    const NAME_MAX_LENGTH = 16;
    const NAME_MIN_LENGTH = 3;
    const PASSWORD_MIN_LENGTH = 12;
    const PASSWORD_MAX_LENGTH = 50;
    const EMAIL_MAX_LENGTH = 40;

    const ROLE_VISITOR = 100;
    const ROLE_MODERATOR = 200;
    const ROLE_EDITOR = 300;
    const ROLE_ADMIN = 400;

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

    public function getRole() {

        if (!$this->loggedIn) {
            throw new Exception('getRole() called before authorization check');
        }

        return (int)$this->tokenPayload['role'];

    }

    public function getName() {

        if (!$this->loggedIn) {
            throw new Exception('getName() called before authorization check');
        }

        return $this->tokenPayload['name'];

    }

    public function isRole($role) {

        if (!$this->loggedIn) {
            throw new Exception('isRole() called before authorization check');
        }

        if (!isset($this->tokenPayload['role'])) {
            return false;
        }

        return (int)$this->tokenPayload['role'] >= $role;

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