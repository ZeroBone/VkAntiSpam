<?php

namespace VkAntiSpam\Config;


class VkAntiSpamConfig {

    public $dbHost = 'localhost';

    public $dbName;

    public $dbCharset = 'utf8';

    public $dbUser = 'root';

    public $dbPassword = '';

    /**
     * @var AccountConfig
     */
    public $account;

    public $recaptchaPublicKey;

    public $recaptchaPrivateKey;

    public $linkAccessSecretKey;

    public function __construct() {}

    /**
     * @param string $dbHost
     * @return VkAntiSpamConfig
     */
    public function dbHost($dbHost) {
        $this->dbHost = $dbHost;
        return $this;
    }

    /**
     * @param mixed $dbDbName
     * @return VkAntiSpamConfig
     */
    public function dbName($dbDbName) {
        $this->dbName = $dbDbName;
        return $this;
    }

    /**
     * @param string $dbDbCharset
     * @return VkAntiSpamConfig
     */
    public function dbCharset($dbDbCharset) {
        $this->dbCharset = $dbDbCharset;
        return $this;
    }

    /**
     * @param string $dbUser
     * @return VkAntiSpamConfig
     */
    public function dbUser($dbUser) {
        $this->dbUser = $dbUser;
        return $this;
    }

    /**
     * @param string $dbPassword
     * @return VkAntiSpamConfig
     */
    public function dbPassword($dbPassword) {
        $this->dbPassword = $dbPassword;
        return $this;
    }

    /**
     * @param AccountConfig $account
     * @return VkAntiSpamConfig
     */
    public function accountConfig(AccountConfig $account): VkAntiSpamConfig {
        $this->account = $account;
        return $this;
    }

    /**
     * @param mixed $recaptchaPublicKey
     * @return VkAntiSpamConfig
     */
    public function setRecaptchaPublicKey($recaptchaPublicKey) {
        $this->recaptchaPublicKey = $recaptchaPublicKey;
        return $this;
    }

    /**
     * @param mixed $recaptchaPrivateKey
     * @return VkAntiSpamConfig
     */
    public function setRecaptchaPrivateKey($recaptchaPrivateKey) {
        $this->recaptchaPrivateKey = $recaptchaPrivateKey;
        return $this;
    }

    /**
     * @param mixed $banAccessSecretKey
     * @return VkAntiSpamConfig
     */
    public function setLinkAccessSecretKey($banAccessSecretKey) {
        $this->linkAccessSecretKey = $banAccessSecretKey;
        return $this;
    }

}