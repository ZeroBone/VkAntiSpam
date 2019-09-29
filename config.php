<?php

use VkAntiSpam\Config\AccountConfig;
use VkAntiSpam\Config\VkAntiSpamConfig;

if (!defined('SECURITY_CANARY')) {
    exit(0);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

return (new VkAntiSpamConfig())
    ->dbName('antispam')
    ->dbUser('root')
    ->dbPassword('')
    ->accountConfig(new AccountConfig('<128-byte secret key here>'))
    ->setRecaptchaPublicKey('<public key>')
    ->setRecaptchaPrivateKey('<secret key>');