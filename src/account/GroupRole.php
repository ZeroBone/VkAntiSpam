<?php

namespace VkAntiSpam\Account;

use \Exception;
use PDO;
use VkAntiSpam\VkAntiSpam;

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

    public static function isGroupModerator($vkGroupId, $userId) {

        if (!VkAntiSpam::get()->account->loggedIn()) {
            throw new Exception('isGroupModerator() without authentication check');
        }

        if (VkAntiSpam::get()->account->isRole(Account::ROLE_SUPER_MODERATOR)) {
            return true;
        }

        $db = VkAntiSpam::get()->getDatabaseConnection();

        $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `vkGroupManagers` WHERE `vkGroupId` = ? AND `userId` = ? AND `role` >= ?;');

        $query->execute([
            $vkGroupId,
            $userId,
            static::MODERATOR
        ]);

        return (int)$query->fetch(PDO::FETCH_ASSOC)['count'] >= 1;

    }

}