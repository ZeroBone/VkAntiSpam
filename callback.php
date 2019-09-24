<?php

use VkAntiSpam\VkAntiSpam;

define('SECURITY_CANARY', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

VkAntiSpam::vkCallback()->run();