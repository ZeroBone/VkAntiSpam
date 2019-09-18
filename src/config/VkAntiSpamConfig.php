<?php

namespace VkAntiSpam\Config;


class VkAntiSpamConfig {

    public $groups;

    public $dbHost = 'localhost';

    public $dbName;

    public $dbCharset = 'utf8';

    public $dbUser = 'root';

    public $dbPassword = '';

    public function __construct() {}

    /**
     * @param $vkGroupConfig VkAntiSpamGroupConfig
     * @return $this
     */
    public function addGroup($vkGroupConfig) {

        $this->groups[$vkGroupConfig->vkId] = $vkGroupConfig;
        
        return $this;

    }

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

}