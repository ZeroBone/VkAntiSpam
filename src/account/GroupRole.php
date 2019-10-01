<?php

namespace VkAntiSpam\Account;


class GroupRole {

    // can only view group messages and stats, without any modifications
    const USER = 100;

    // can only view group messages and stats and specify, which messages are spam or ham
    const MODERATOR = 250;

    // can modify only group general settings
    const EDITOR = 500;

    // can modify all group settings
    const ADMIN = 1000;

    private function __construct() {}

}