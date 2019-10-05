<?php

define('SECURITY_CANARY', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/VkAntiSpam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/account/Account.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/account/GroupRole.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/CommentChangeHandler.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/Reputation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/TextClassifier.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/config/AccountConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/config/VkAntiSpamConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyDeleteEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyEditEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyNewEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyRestoreEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Captcha.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Paginator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/StringUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/Utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/VkAttachment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/VkUtils.php';