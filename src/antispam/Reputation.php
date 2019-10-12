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
    const ADMIN_HAM = 1;

    /**
     * Reputation delta when admin marks the message as spam.
     */
    const ADMIN_SPAM = -20;

    /**
     * Reputation delta when admin delets the message without ban.
     */
    const ADMIN_DELETES = -5;

    /**
     * Reputation delta when admin delets the message and bans the user.
     */
    const ADMIN_DELETES_AND_BANNES = -10;

    /**
     * Reputation delta when the user writes one comment many times.
     */
    const DUPLICATING_COMMENT = -2;

    /**
     * Reputation delta when the user writes bad words.
     */
    const CENSORED = -3 - self::ADMIN_HAM;

    private function __construct() {}

}