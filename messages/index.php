<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\Utils\Paginator;
use VkAntiSpam\Utils\PaginatorClient;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\Utils\VkUtils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

define('ACTION_LEARN_SPAM', 3);
define('ACTION_DELELE', 2);
define('ACTION_DELELE_AND_BAN', 4);
define('ACTION_LEARN_HAM', 1);

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <?php

        if (isset($_POST['selectedMessages'])) {

            $toLearnSpam = [];
            $toLearnHam = [];
            $toDelete = [];
            $toDeleteAndBan = [];

            $i = 0;

            foreach ($_POST['selectedMessages'] as $selectedMessage => $category) {

                if ($i > 50) { // limit
                    break;
                }

                $i++;

                switch ((int)$category) {

                    case ACTION_LEARN_HAM:
                        $toLearnHam[] = (int)$selectedMessage;
                        break;

                    case ACTION_DELELE:
                        $toDelete[] = (int)$selectedMessage;
                        break;

                    case ACTION_LEARN_SPAM:
                        $toLearnSpam[] = (int)$selectedMessage;
                        break;

                    case ACTION_DELELE_AND_BAN:
                        $toDeleteAndBan[] = (int)$selectedMessage;
                        break;

                    default:
                        break;

                }

            }

            if (!empty($toLearnHam)) {

                $gluedMessages = implode(',', $toLearnHam);

                $query = $db->query('SELECT `message` FROM `messages` WHERE `id` IN('.$gluedMessages.');');

                $antispam = new TextClassifier();

                while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                    $antispam->learn($row['message'], TextClassifier::CATEGORY_HAM);

                }

                // we are 100% sure is is ham

                $query = $db->prepare(
                    'UPDATE `messages` SET `category` = ? WHERE `id` IN('.$gluedMessages.');');

                $query->execute([
                    TextClassifier::CATEGORY_HAM
                ]);

                ?>
                <div class="alert alert-success" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <?= count($toLearnHam); ?> сообщений были сохранены как не-спам.
                </div>
                <?php

            }

            if (!empty($toDelete)) {

                // we just have to delete the comment

                $gluedMessages = implode(',', $toDelete);

                $query = $db->query(
                    'SELECT `id`, `groupId`, `vkContext`, `vkGroups`.`adminVkToken` FROM `messages`, `vkGroups` WHERE `messages`.`id` IN('.$gluedMessages.') AND `messages`.`groupId` = `vkGroups`.`vkId`;');

                // delete comments from vk
                while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                    VkUtils::deleteGroupComment($row['adminVkToken'], (int)$row['groupId'], (int)$row['vkContext']);

                }

                // now delete the comments from the database

                $query = $db->query('DELETE FROM `messages` WHERE `messages`.`id` IN('.$gluedMessages.') LIMIT 50;');

                ?>
                <div class="alert alert-success" role="alert">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <?= count($toDelete); ?> сообщений были удалены.
                </div>
                <?php

            }

            if (!empty($toLearnSpam)) {
                // TODO
            }

            if (!empty($toDeleteAndBan)) {
                // TODO
            }

            /*echo json_encode([
                'spam' => $toLearnSpam,
                'ham' => $toLearnHam,
                'delete' => $toDelete,
                'deleteAndBan' => $toDeleteAndBan,
            ]);*/

        }

        ?>
        <div class="row">
            <div class="col-12">
                <form method="post">
                    <?php

                    // action="https://httpbin.org/post"

                    $currentPage = 1;

                    if (isset($_GET['p'])) {
                        $currentPage = (int)$_GET['p'];
                    }

                    $db = VkAntiSpam::get()->getDatabaseConnection();

                    $query = $db->query('SELECT COUNT(*) AS `count` FROM `messages` WHERE `category` = 0;');
                    $totalItems = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

                    $paginator = new Paginator(
                        $totalItems,
                        50,
                        $currentPage,
                        new class implements PaginatorClient {

                            public function printPage($paginator, $offset) {

                                $db = VkAntiSpam::get()->getDatabaseConnection();

                                $query = $db->query('SELECT `messages`.*, `vkGroups`.`name` AS `vkGroupName` FROM `messages`, `vkGroups` WHERE `category` = 0 ORDER BY `id` DESC LIMIT 50 OFFSET ' . (int)$offset . ';');

                                while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                    ?>
                                    <tr>
                                        <td class="d-none d-sm-table-cell"><?= $currentRow['id']; ?></td>
                                        <td>
                                            <label class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="selectedMessages[<?= $currentRow['id']; ?>]" value="<?= ACTION_LEARN_SPAM; ?>">
                                                <div class="custom-control-label">Спам</div>
                                            </label>
                                            <label class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="selectedMessages[<?= $currentRow['id']; ?>]" value="<?= ACTION_LEARN_HAM; ?>">
                                                <div class="custom-control-label">Не&nbsp;спам</div>
                                            </label>
                                            <label class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="selectedMessages[<?= $currentRow['id']; ?>]" value="<?= ACTION_DELELE; ?>">
                                                <div class="custom-control-label">Удалить</div>
                                            </label>
                                            <label class="custom-control custom-radio">
                                                <input type="radio" class="custom-control-input" name="selectedMessages[<?= $currentRow['id']; ?>]" value="<?= ACTION_DELELE_AND_BAN; ?>">
                                                <div class="custom-control-label">Удалить&nbsp;+&nbsp;бан</div>
                                            </label>
                                        </td>
                                        <td class="d-none d-sm-table-cell"><?= date('d.m.Y H:i:s', (int)$currentRow['date']); ?></td>
                                        <td><?= StringUtils::escapeHTML($currentRow['message']); ?></td>
                                        <td class="d-none d-sm-table-cell"><a href="https://vk.com/public<?= $currentRow['groupId']; ?>" target="_blank"><?= $currentRow['vkGroupName']; ?></a></td>
                                        <td class="d-none d-sm-table-cell">
                                            <a class="btn btn-secondary btn-sm" href="https://vk.com/wall-<?= $currentRow['groupId']; ?>_<?= $currentRow['vkContext']; ?>?reply=<?= $currentRow['vkId']; ?>" target="_blank">Стена</a>
                                        </td>
                                    </tr>
                                    <?php

                                }

                            }

                            public function getPageUrl($pageNumber) {
                                return '/messages/?p=' . $pageNumber;
                            }
                        }
                    );

                    $paginator->printPagination();

                    ?>
                    <div class="card">
                        <table class="table card-table table-vcenter">
                            <thead>
                            <tr>
                                <th class="d-none d-sm-table-cell">#</th>
                                <th class="w-1"></th>
                                <th class="d-none d-sm-table-cell">Дата</th>
                                <th>Сообщение</th>
                                <th class="d-none d-sm-table-cell">Группа</th>
                                <th class="d-none d-sm-table-cell">Действия</th>
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">С выбранными сообщениями</h3>
                        </div>
                        <!--<div class="card-body">
                            <div class="form-group">
                                <div class="form-label">Выберите действие</div>
                                <div class="custom-switches-stacked">
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-danger rounded mr-4"></div>
                                        <input type="radio" name="action" value="<?= ACTION_LEARN_SPAM; ?>" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">
                                            Удалить выбранные сообщения, запомнить их как спам и, если необходимо, заблокировать пользователей
                                        </span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-primary rounded mr-4"></div>
                                        <input type="radio" name="action" value="<?= ACTION_DELELE; ?>" class="custom-switch-input" checked="">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Удалить выбранные сообщения</span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-warning rounded mr-4"></div>
                                        <input type="radio" name="action" value="<?= ACTION_DELELE_AND_BAN; ?>" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Удалить выбранные сообщения и заблокировать пользователей</span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-success rounded mr-4"></div>
                                        <input type="radio" name="action" value="<?= ACTION_LEARN_HAM; ?>" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Запомнить выбранные сообщения как не-спам</span>
                                    </label>
                                </div>
                            </div>
                        </div>-->
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">
                                <i class="fe fe-check mr-2"></i>Применить
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12">

            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';