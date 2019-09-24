<?php

namespace VkAntiSpam\Utils;


use VkAntiSpam\VkAntiSpam;

class Captcha {

    public function __construct() {}

    public function isSubmitting() {

        return isset($_POST['g-recaptcha-response']);

    }

    public function isHuman() {

        $verifyResponse = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?' .
            http_build_query([
                'secret' => VkAntiSpam::get()->config->recaptchaPrivateKey,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => Utils::getUserIpAddress()
            ])
        );

        $responseData = json_decode($verifyResponse, true);

        return $responseData && $responseData['success'] === true;

    }

    public function printScript() {
        ?><script src="https://www.google.com/recaptcha/api.js"></script><?php
    }

    public function printBox() {
        ?>
        <div class="g-recaptcha" data-sitekey="<?= VkAntiSpam::get()->config->recaptchaPublicKey ?>"></div>
        <?php
    }

}