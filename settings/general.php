<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

if (!$vas->account->isRole(Account::ROLE_MODERATOR)) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Общие настройки
            </h1>
        </div>
        <div class="row">
            <div class="col-12">
                <form class="card" method="post">
                    <div class="card-header">
                        <h3 class="card-title">Предварительная фильтрация</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-label">Запрещённые медиавложения</div>
                            <div class="custom-controls-stacked">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="photo">
                                    <span class="custom-control-label">Фотографии</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="video">
                                    <span class="custom-control-label">Видеозаписи</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="audio">
                                    <span class="custom-control-label">Аудиозаписи</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="doc">
                                    <span class="custom-control-label">Документы</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="graffity">
                                    <span class="custom-control-label">Граффити</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="link">
                                    <span class="custom-control-label">Ссылки</span>
                                </label>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="restrictedAttachments" value="sticker">
                                    <span class="custom-control-label">Стикеры</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Минимальная длина сообщений
                                <input type="number" class="form-control" name="minMessageLength">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Максимальная длина сообщений
                                <input type="number" class="form-control" name="maxMessageLength">
                            </label>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Сохранить</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';