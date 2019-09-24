<?php

namespace VkAntiSpam\Config;


class AccountConfig {

    /**
     * @var string
     */
    public $jwtSecret;

    /**
     * AccountConfig constructor.
     * @param string $jwtSecret
     */
    public function __construct($jwtSecret) {
        $this->jwtSecret = $jwtSecret;
        return $this;
    }

}