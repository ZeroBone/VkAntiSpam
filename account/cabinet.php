<?php

use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Личный кабинет
            </h1>
        </div>
    </div>
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';

?>