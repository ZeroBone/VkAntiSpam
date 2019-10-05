<?php

use VkAntiSpam\Account\GroupRole;
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

/*if (!$vas->account->isRole(Account::ROLE_SUPER_MODERATOR)) {
    Utils::redirect('/account/login');
    exit(0);
}*/

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

$db = VkAntiSpam::get()->getDatabaseConnection();

$query = $db->prepare('SELECT `vkId`, `name`, `minMessageLength`, `maxMessageLength`, `restrictedAttachments`, `spamBanDuration`, `adminBanDuration`, `learnFromOutcomingComments`, `learnFromDeletedComments`, `deleteMessagesFromGroups`, `neutralWords` FROM `vkGroups` WHERE `vkId` = ? LIMIT 1;');
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
                    Указанная Вами группа отсутствует - скорее всего Вы перешли по сломанной ссылке.
                </div>
            </div>
        </div>
    </div>
    <?php

}
// verify that user is editor in group or editor as account privelege
elseif (!GroupRole::isGroupEditor((int)$vkGroup['vkId'], $vas->account->getId())) {

    ?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    У Вас недостаточно прав для просмотра и изменения настроек данной группы.
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
                    isset($_POST['spamBanDuration']) &&
                    isset($_POST['adminBanDuration'])
                ) {

                    $sMinMessageLength = min(max((int)$_POST['minMessageLength'], 0), 10000);
                    $sMaxMessageLength = min(max((int)$_POST['maxMessageLength'], 0), 10000);
                    $sSpamBanDuration = max((int)$_POST['spamBanDuration'], 0);
                    $sAdminBanDuration = max((int)$_POST['adminBanDuration'], 0);

                    $sNeutralWords = isset($_POST['neutralWords']) ? (string)$_POST['neutralWords'] : '';

                    $sNeutralWords = substr($sNeutralWords, 0, 255);

                    $neutralWords = [];

                    {

                        foreach (explode(',', $sNeutralWords) as $nw) {

                            if ($nw === '') {
                                continue;
                            }

                            if (preg_match('/[^a-zA-Z0-9А-ЯЁа-яё()]/u', trim($nw))) {
                                // odd characters
                                continue;
                            }

                            $newNw = preg_replace('/[^a-zA-Z0-9А-ЯЁа-яё]/u', '', trim($nw));

                            if ($newNw !== '') {
                                $neutralWords[] = $newNw;
                            }

                        }

                    }

                    $neutralWords = implode(',', $neutralWords);

                    $restrictedAttachments = [];

                    if (isset($_POST['restrictedAttachments'])) {
                        $restrictedAttachments = (array)$_POST['restrictedAttachments'];
                    }

                    $newRestrictedAttachments = 0;

                    foreach ($restrictedAttachments as $restrictedAttachment) {
                        $newRestrictedAttachments |= max((int)$restrictedAttachment, 0);
                    }

                    // echo $newRestrictedAttachments;

                    $learnFromOutcomingComments = isset($_POST['learnFromOutcomingComments']) ? 1 : 0;

                    $learnFromDeletedComments = isset($_POST['learnFromDeletedComments']) ? 1 : 0;

                    $deleteMessagesFromGroups = isset($_POST['deleteMessagesFromGroups']) ? 1 : 0;

                    // neutral words

                    $query = $db->prepare('UPDATE `vkGroups` SET `minMessageLength` = ?, `maxMessageLength` = ?, `restrictedAttachments` = ?, `spamBanDuration` = ?, `adminBanDuration` = ?, `learnFromOutcomingComments` = ?, `learnFromDeletedComments` = ?, `deleteMessagesFromGroups` = ?, `neutralWords` = ? WHERE `vkId` = ? LIMIT 1;');

                    $query->execute([
                        $sMinMessageLength, // min message length
                        $sMaxMessageLength, // max message length
                        $newRestrictedAttachments,
                        $sSpamBanDuration, // ban duration
                        $sAdminBanDuration, // admin ban duration
                        $learnFromOutcomingComments, // learn from outcoming comments
                        $learnFromDeletedComments, // learn from deleted comments
                        $deleteMessagesFromGroups, // delete messages from groups
                        $neutralWords, // neutral words
                        (int)$_GET['g'] // group vk id
                    ]);

                    $vkGroup['minMessageLength'] = $sMinMessageLength;
                    $vkGroup['maxMessageLength'] = $sMaxMessageLength;
                    $vkGroup['spamBanDuration'] = $sSpamBanDuration;
                    $vkGroup['adminBanDuration'] = $sAdminBanDuration;
                    $vkGroup['restrictedAttachments'] = $newRestrictedAttachments;
                    $vkGroup['learnFromOutcomingComments'] = $learnFromOutcomingComments;
                    $vkGroup['learnFromDeletedComments'] = $learnFromDeletedComments;
                    $vkGroup['deleteMessagesFromGroups'] = $deleteMessagesFromGroups;
                    $vkGroup['neutralWords'] = $neutralWords;

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
                            <label class="form-label">
                                Минимальная длина сообщений
                                <input type="number" class="form-control" name="minMessageLength" value="<?= $vkGroup['minMessageLength']; ?>" required="" min="0" max="10000">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Максимальная длина сообщений
                                <input type="number" class="form-control" name="maxMessageLength" value="<?= $vkGroup['maxMessageLength']; ?>" required="" min="0" max="10000">
                            </label>
                        </div>
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
                                               value="<?= $bit ?>"<?= $checked ? ' checked="checked"' : '' ?>>
                                        <span class="custom-control-label"><?= $attachment->title ?></span>
                                    </label>
                                    <?php

                                }

                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Длительность автоматического бана за спам в секундах или 0, чтобы отключить блокировку пользователей.
                                <input type="number" class="form-control" name="spamBanDuration" value="<?= $vkGroup['spamBanDuration']; ?>" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Длительность бана в секундах или 0, чтобы отключить блокировку пользователей по запросу.
                                <input type="number" class="form-control" name="adminBanDuration" value="<?= $vkGroup['adminBanDuration']; ?>" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <div class="custom-controls-stacked">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="learnFromOutcomingComments"<?= ((int)$vkGroup['learnFromOutcomingComments'] === 1) ? ' checked="checked"' : '' ?>>
                                    <span class="custom-control-label">Обучаться на комментариях от имени группы</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-controls-stacked">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="learnFromDeletedComments"<?= ((int)$vkGroup['learnFromDeletedComments'] === 1) ? ' checked="checked"' : '' ?>>
                                    <span class="custom-control-label">Обучаться на удалённых администрацией комментариях</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="custom-controls-stacked">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="deleteMessagesFromGroups"<?= ((int)$vkGroup['deleteMessagesFromGroups'] === 1) ? ' checked="checked"' : '' ?>>
                                    <span class="custom-control-label">Удалять сообщения от других групп</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Нейтральные слова, разделённые запятыми
                                <input type="text" class="form-control" name="neutralWords" value="<?= $vkGroup['neutralWords']; ?>" minlength="0" maxlength="255">
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