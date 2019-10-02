<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Account\GroupRole;
use VkAntiSpam\Utils\Paginator;
use VkAntiSpam\Utils\PaginatorClient;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

if (!$vas->account->isRole(Account::ROLE_ADMIN)) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                Группы
            </h1>
        </div>
        <div class="row">
            <div class="col-12">
                <?php

                // form handling

                if (
                    isset($_POST['vkId']) &&
                    isset($_POST['name']) &&
                    isset($_POST['secret']) &&
                    isset($_POST['token']) &&
                    isset($_POST['adminVkId']) &&
                    isset($_POST['adminVkToken']) &&
                    isset($_POST['confirmationToken'])
                ) {

                    $vkId = (int)$_POST['vkId'];
                    $name = StringUtils::escapeHTML((string)$_POST['name']);
                    $secret = (string)$_POST['secret'];
                    $token = (string)$_POST['token'];
                    $adminVkId = (int)$_POST['adminVkId'];
                    $adminVkToken = (string)$_POST['adminVkToken'];
                    $confirmationToken = (string)$_POST['confirmationToken'];

                    if ($vkId <= 0) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Некорректный ID группы.
                        </div>
                        <?php
                    }
                    elseif ($adminVkId <= 0) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Некорректный ID администратора.
                        </div>
                        <?php
                    }
                    elseif (StringUtils::getStringLength($name) > 32 || StringUtils::getStringLength($name) === 0) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Название группы пустое или слишком длинное.
                        </div>
                        <?php
                    }
                    elseif (StringUtils::getStringLength($secret) > 50) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Секретный ключ слишком длинный.
                        </div>
                        <?php
                    }
                    elseif (strlen($token) !== 85) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Токен неверной длины.
                        </div>
                        <?php
                    }
                    elseif (strlen($adminVkToken) !== 85) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Токен администратора неверной длины.
                        </div>
                        <?php
                    }
                    elseif (strlen($confirmationToken) !== 8) {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            Токен подтверждения неверной длины.
                        </div>
                        <?php
                    }
                    else {

                        $db = VkAntiSpam::get()->getDatabaseConnection();

                        $query = $db->prepare('SELECT `vkId` FROM `vkGroups` WHERE `vkId` = ? LIMIT 1;');
                        $query->execute([
                            $vkId
                        ]);

                        if (isset($query->fetch(PDO::FETCH_ASSOC)['vkId'])) {
                            ?>
                            <div class="alert alert-danger" role="alert">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                Данная группа уже существует в системе.
                            </div>
                            <?php
                        }
                        else {

                            $query = $db->prepare('INSERT INTO `vkGroups` (vkId, `name`, secret, token, adminVkId, adminVkToken, confirmationToken) VALUES (?,?,?,?,?,?,?);');
                            $query->execute([
                                $vkId,
                                $name,
                                $secret,
                                $token,
                                $adminVkId,
                                $adminVkToken,
                                $confirmationToken
                            ]);

                            $query = $db->prepare('INSERT INTO `vkGroupManagers` (`vkGroupId`, `userId`, `role`) VALUES (?,?,?);');
                            $query->execute([
                                $vkId,
                                $vas->account->getId(),
                                GroupRole::ADMIN // assign the creator of the group admin priveleges
                            ]);

                            ?>
                            <div class="alert alert-success" role="alert">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                Группа успешно добавлена.
                            </div>
                            <?php
                        }

                    }

                }

                // table

                $currentPage = 1;

                if (isset($_GET['p'])) {
                    $currentPage = (int)$_GET['p'];
                }

                $db = VkAntiSpam::get()->getDatabaseConnection();

                $query = $db->query('SELECT COUNT(*) AS `count` FROM `vkGroups`;');
                $totalItems = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

                $paginator = new Paginator(
                    $totalItems,
                    50,
                    $currentPage,
                    new class implements PaginatorClient {

                        public function printPage($paginator, $offset) {

                            $db = VkAntiSpam::get()->getDatabaseConnection();

                            $query = $db->query('SELECT `vkId`, `name`, `secret`, `token`, `confirmationToken`, `adminVkId` FROM `vkGroups` ORDER BY `vkId` ASC LIMIT 50 OFFSET ' . (int)$offset . ';');

                            while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                ?>
                                <tr>
                                    <td><?= $currentRow['vkId']; ?></td>
                                    <td class="d-none d-sm-table-cell"><?= StringUtils::escapeHTML($currentRow['name']); ?></td>
                                    <td><?= StringUtils::escapeHTML(substr($currentRow['secret'], 0, 6)) . '...'; ?></td>
                                    <td><?= StringUtils::escapeHTML(substr($currentRow['token'], 0, 6)) . '...'; ?></td>
                                    <td><?= StringUtils::escapeHTML(substr($currentRow['confirmationToken'], 0, 6)) . '...'; ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <a class="btn btn-secondary btn-sm" href="https://vk.com/public<?= $currentRow['vkId']; ?>" target="_blank">Перейти</a>
                                        <a class="btn btn-success btn-sm" href="https://vk.com/id<?= $currentRow['adminVkId']; ?>" target="_blank">Администратор</a>
                                        <a class="btn btn-danger btn-sm" href="javascript:alert('В разработке...')">Удалить</a>
                                    </td>
                                </tr>
                                <?php

                            }

                        }

                        public function getPageUrl($pageNumber) {
                            return '/settings/groups?p=' . $pageNumber;
                        }
                    }
                );

                $paginator->printPagination();

                ?>
                <div class="card">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th class="d-none d-sm-table-cell">Название</th>
                            <th>Секретный ключ</th>
                            <th>Токен</th>
                            <th>Токен подтверждения</th>
                            <th class="d-none d-md-table-cell">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        $paginator->printContent();

                        ?>
                        </tbody>
                    </table>
                </div>
                <?php

                $paginator->printPagination();

                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form class="card" method="post">
                    <div class="card-header">
                        <h3 class="card-title">Добавить новую группу</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">
                                ID группы
                                <input type="number" class="form-control" name="vkId" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Название
                                <input type="text" class="form-control" name="name" maxlength="32" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Секретный ключ
                                <input type="password" class="form-control" name="secret" maxlength="50" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Токен группы
                                <input type="password" class="form-control" name="token" minlength="85" maxlength="85" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                ID администратора
                                <input type="number" class="form-control" name="adminVkId" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Токен администратора
                                <input type="password" class="form-control" name="adminVkToken" minlength="85" maxlength="85" required="">
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                Токен подтверждения
                                <input type="password" class="form-control" name="confirmationToken" minlength="8" maxlength="8" required="">
                            </label>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Добавить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';