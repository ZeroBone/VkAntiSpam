<?php

namespace VkAntiSpam\Config;


class VkAntiSpamGroupConfig {

    public $vkId;

    public $secret;

    public $token;

    public $confirmationToken;

    /**
     * VkAntiSpamGroupConfig constructor.
     * @param $vkId
     * @param $secret
     * @param $token
     * @param $confirmationToken
     */
    public function __construct($vkId, $secret, $token, $confirmationToken) {
        $this->vkId = $vkId;
        $this->secret = $secret;
        $this->token = $token;
        $this->confirmationToken = $confirmationToken;
    }


}