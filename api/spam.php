<?php

use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    exit(0);
}