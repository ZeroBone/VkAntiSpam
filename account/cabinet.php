<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/autoload.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/structure/header.php";

if (\VkAntiSpam\VkAntiSpam::get()->account->loggedIn()) {
    ?>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
    <div class="center">
        <p class="text">Ваши сокращённые ссылки:</p>
        <table>
            <tr>
                <th>Ссылка</th>
                <th>Переходов</th>
                <th>Исходная ссылка</th>
            </tr>
            <?php

            $db = Utils::getDatabase();

            if (isset($_POST['id']) && isset($_POST['link'])) {

                $linkId = (int)$_POST['id'];
                $link = trim((string)$_POST['link']);

                if (Utils::rstrlen($link) > 1024) {

                    Utils::headerError("Ошибка! Слишком длинный URL.");

                }
                elseif (!Utils::validateAndCleanUrl($link)) {

                    Utils::headerError("Ошибка! Некорректный URL.");

                }
                else {

                    $query = $db->prepare('SELECT `id`, `user` FROM `links` WHERE `id` = ?;');
                    $query->execute([
                        $linkId
                    ]);

                    $linkData = $query->fetch(PDO::FETCH_ASSOC);

                    // var_dump($linkData['user']);
                    // var_dump($lzz->account->tokenPayload['id']);

                    if (!isset($linkData['id']) || !isset($linkData['user'])) {

                        Utils::headerError("Ошибка! Данной ссылки не существует.");

                    }
                    elseif ((int)$linkData['user'] !== (int)$lzz->account->tokenPayload['id']) {

                        Utils::headerError("Ошибка! Вы не можете редактировать данную ссылку.");

                    }
                    else {

                        $query = $db->prepare('UPDATE `links` SET `link` = ?, `edited` = 1 WHERE `id` = ? LIMIT 1;');

                        $query->execute([
                            $link,
                            (int)$linkData['id']
                        ]);

                        Utils::headerSuccess('Ссылка успешно обновлена.');

                    }

                }

            }

            $query = $db->prepare('SELECT `id`, `accessed`, `link` FROM `links` WHERE `user` = ? LIMIT 1000;');

            $query->execute([
                $lzz->account->tokenPayload['id']
            ]);

            while ($link = $query->fetch(PDO::FETCH_ASSOC)) {

                $linkShortCode = \Lzz\Utils\UrlUtils::linkIdToShortCode((int)$link['id']);

                ?>
                <form method="post">
                    <tr>
                        <td><a href="http://lllz.ru/<?= $linkShortCode ?>" target="_blank">lllz.ru/<?= $linkShortCode ?></a></td>
                        <td><?= $link['accessed']; ?></td>
                        <td>
                            <input type="hidden" name="id" value="<?= (int)$link['id']; ?>">
                            <input type="url" name="link" placeholder="<?= Utils::escapeHTML($link['link']) ?>" maxlength="1024" required="" value="<?= Utils::escapeHTML($link['link']) ?>" style="width: 70%;float:left;">
                            <button type="submit" class="btn no-border" style="padding: 2px;font-size: inherit;">Обновить</button>
                        </td>
                    </tr>
                </form>

                <?php

            }

            ?>
        </table>
        <br>
        <a href="/account/logout" class="btn no-border">Выход</a>
    </div>
    <?php
}
else {
    Utils::headerError("Ошибка! Вы не авторизовались на сайте!");
}

require_once $_SERVER['DOCUMENT_ROOT'] . "/structure/footer.php";

?>