<?php

namespace VkAntiSpam\Config;


class VkAntiSpamGroupConfig {

    public $vkId;

    public $secret;

    public $token;

    public $adminId;

    public $adminToken;

    public $confirmationToken;

    /**
     * VkAntiSpamGroupConfig constructor.
     * @param $vkId
     * @param $secret
     * @param $token
     * @param $adminId
     * @param $adminToken
     * @param $confirmationToken
     */
    public function __construct($vkId, $secret, $token, $adminId, $adminToken, $confirmationToken) {
        $this->vkId = $vkId;
        $this->secret = $secret;
        $this->token = $token;
        $this->adminId = $adminId;
        $this->adminToken = $adminToken;
        $this->confirmationToken = $confirmationToken;
    }


}