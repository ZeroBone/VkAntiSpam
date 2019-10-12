<?php

if (defined('SECURITY_CANARY')) {
    exit(0);
}

define('SECURITY_CANARY', true);

require $_SERVER['DOCUMENT_ROOT'] . '/src/VkAntiSpam.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/account/Account.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/account/GroupRole.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/obscene/ObsceceCensor.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/BanAccessSecurity.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/CommentChangeHandler.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/Reputation.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/TextClassifier.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/config/AccountConfig.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/config/VkAntiSpamConfig.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkEvent.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyDeleteEvent.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyEditEvent.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyNewEvent.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyRestoreEvent.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Captcha.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Paginator.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/StringUtils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Utils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/VkAttachment.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/utils/VkUtils.php';