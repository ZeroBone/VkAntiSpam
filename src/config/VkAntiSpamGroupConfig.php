<?php

namespace VkAntiSpam\Config;


class VkAntiSpamGroupConfig {

    public $vkId;

    public $secret;

    public $token;

    public $adminToken;

    public $confirmationToken;

    /**
     * VkAntiSpamGroupConfig constructor.
     * @param $vkId
     * @param $secret
     * @param $token
     * @param $adminToken
     * @param $confirmationToken
     */
    public function __construct($vkId, $secret, $token, $adminToken, $confirmationToken) {
        $this->vkId = $vkId;
        $this->secret = $secret;
        $this->token = $token;
        $this->adminToken = $adminToken;
        $this->confirmationToken = $confirmationToken;
    }


}