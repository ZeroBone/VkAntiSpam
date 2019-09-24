<?php

use VkAntiSpam\Config\AccountConfig;
use VkAntiSpam\Config\VkAntiSpamConfig;
use VkAntiSpam\Config\VkAntiSpamGroupConfig;

if (!defined('SECURITY_CANARY')) {
    exit(0);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

return (new VkAntiSpamConfig())
    ->addGroup(new VkAntiSpamGroupConfig(
        $_SERVER['VK_GROUP_ID'],
        $_SERVER['VK_SECRET'],
        $_SERVER['VK_TOKEN'],
        $_SERVER['VK_ADMIN_ID'],
        $_SERVER['VK_ADMIN_TOKEN'],
        $_SERVER['VK_CONFIRMATION_TOKEN']
    ))
    ->dbName('antispam')
    ->dbUser('root')
    ->dbPassword('')
    ->accountConfig(new AccountConfig('<128-byte secret key here>'))
    ->setRecaptchaPublicKey('<public key>')
    ->setRecaptchaPrivateKey('<secret key>');