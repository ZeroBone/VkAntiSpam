<?php

use VkAntiSpam\VkAntiSpam;

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

VkAntiSpam::vkCallback()->run();