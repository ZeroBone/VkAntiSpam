<?php

use VkAntiSpam\Config\VkAntiSpamConfig;
use VkAntiSpam\Config\VkAntiSpamGroupConfig;
use VkAntiSpam\VkAntiSpam;

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$antispam = new VkAntiSpam(
    (new VkAntiSpamConfig())
    ->addGroup(new VkAntiSpamGroupConfig(
        $_SERVER['VK_GROUP_ID'],
        $_SERVER['VK_SECRET'],
        $_SERVER['VK_TOKEN'],
        $_SERVER['VK_ADMIN_TOKEN'],
        $_SERVER['VK_CONFIRMATION_TOKEN']
    ))
    ->dbName('antispam')
    ->dbUser('root')
    ->dbPassword('')
);

$antispam->run();