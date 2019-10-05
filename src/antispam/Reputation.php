<?php

namespace VkAntiSpam\System;


class Reputation {

    /**
     * Reputation delta when the system classifies the message as ham.
     */
    const CLASSIFIER_HAM = 1;

    /**
     * Reputation delta when the system classifies the message as spam.
     */
    const CLASSIFIER_SPAM = -10;

    /**
     * Reputation delta when admin marks the message as ham.
     */
    const ADMIN_HAM = 2;

    /**
     * Reputation delta when admin marks the message as spam.
     */
    const ADMIN_SPAM = -10;

    /**
     * Reputation delta when admin delets the message without ban.
     */
    const ADMIN_DELETES = -5;

    /**
     * Reputation delta when admin delets the message and bans the user.
     */
    const ADMIN_DELETES_AND_BANNES = -5;

    private function __construct() {}

}