<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/VkAntiSpam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/VkEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/antispam/AntiSpamSystem.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/config/VkAntiSpamConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/config/VkAntiSpamGroupConfig.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/events/VkWallReplyNewEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/StringUtils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/VkUtils.php';