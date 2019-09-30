<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\Utils\VkAttachment;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

if (!isset($_GET['g'])) {
    Utils::redirect('/');
    exit(0);
}

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

$db = VkAntiSpam::get()->getDatabaseConnection();

$query = $db->prepare('SELECT `vkId`, `name`, `minMessageLength`, `maxMessageLength`, `restrictedAttachments`, `spamBanDuration` FROM `vkGroups` WHERE `vkId` = ? LIMIT 1;');
$query->execute([
    (int)$_GET['g']
]);

$vkGroup = $query->fetch(PDO::FETCH_ASSOC);

if (!isset($vkGroup['vkId'])) {

    ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    Указанная Вами группа удалена, либо у Вас недостаточно прав для изменения её настроек.
                </div>
            </div>
        </div>
    </div>
    <?php

}
else {

    ?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Общие настройки
            </h1>
        </div>
        <div class="row">
            <div class="col-12">
                <?php

                if (
                    isset($_POST['minMessageLength']) &&
                    isset($_POST['maxMessageLength']) &&
                    isset($_POST['spamBanDuration'])
                ) {

                    $sMinMessageLength = max((int)$_POST['minMessageLength'], 0);
                    $sMaxMessageLength = max((int)$_POST['maxMessageLength'], 0);
                    $sSpamBanDuration = max((int)$_POST['spamBanDuration'], 0);

                    $restrictedAttachments = [];

                    if (isset($_POST['restrictedAttachments'])) {
                        $restrictedAttachments = (array)$_POST['restrictedAttachments'];
                    }

                    $newRestrictedAttachments = 0;

                    foreach ($restrictedAttachments as $restrictedAttachment) {
                        $newRestrictedAttachments |= max((int)$restrictedAttachment, 0);
                    }

                    // echo $newRestrictedAttachments;

                    $query = $db->prepare('UPDATE `vkGroups` SET `minMessageLength` = ?, `maxMessageLength` = ?, `restrictedAttachments` = ?, `spamBanDuration` = ? WHERE `vkId` = ? LIMIT 1;');
                    $query->execute([
                        $sMinMessageLength, // min message length
                        $sMaxMessageLength, // max message length
                        $newRestrictedAttachments,
                        $sSpamBanDuration, // ban duration
                        (int)$_GET['g'] // group vk id
                    ]);

                    $vkGroup['minMessageLength'] = $sMinMessageLength;
                    $vkGroup['maxMessageLength'] = $sMaxMessageLength;
                    $vkGroup['spamBanDuration'] = $sSpamBanDuration;
                    $vkGroup['restrictedAttachments'] = $newRestrictedAttachments;

                    ?>
                    <div class="alert alert-success" role="alert">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        Данные успешно обновлены.
                    </div>
                    <?php

                }

                // action="https://httpbin.org/post"

                ?>
                <form class="card" method="post">
                    <div class="card-header">
                        <h3 class="card-title">Группа &quot;<?= StringUtils::escapeHTML($vkGroup['name']); ?>&quot;</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-label">Запрещённые медиавложения</div>
                            <div class="custom-controls-stacked">
                                <?php

                                $attachments = VkAttachment::getCommentAttachments();

                                foreach ($attachments as $bit => $attachment) {

                                    $checked = ((int)$bit & (int)$vkGroup['restrictedAttachments']) !== 0;

                                    ?>
                                    <label class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="restrictedAttachments[]"
                                               value="<?= $bit ?>"<?= $checked ? 'checked="checked"' : '' ?>>
                                        <span class="custom-control-label"><?= $attachment->title ?></span>
                                    </label>
                                    <?php

                                }

                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Минимальная длина сообщений
                                <input type="number" class="form-control" name="minMessageLength" value="<?= $vkGroup['minMessageLength']; ?>" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Максимальная длина сообщений
                                <input type="number" class="form-control" name="maxMessageLength" value="<?= $vkGroup['maxMessageLength']; ?>" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Длительность бана за спам в секундах или 0, чтобы отключить блокировку пользователей.
                                <input type="number" class="form-control" name="spamBanDuration" value="<?= $vkGroup['spamBanDuration']; ?>" required="">
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
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';