<?php

use VkAntiSpam\Account\GroupRole;
use VkAntiSpam\Utils\Paginator;
use VkAntiSpam\Utils\PaginatorClient;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

if (!isset($_GET['g'])) {
    Utils::redirect('/messages/');
    exit(0);
}

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php

                $groupId = (int)$_GET['g'];

                if (!GroupRole::isGroupModerator($groupId, $vas->account->getId())) {

                    ?>
                    <div class="alert alert-danger" role="alert">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        Данная группа отсутствует либо у Вас недостаточно прав для просмотра сообщений данной группы.
                    </div>
                    <?php

                }
                else {

                    $currentPage = 1;

                    if (isset($_GET['p'])) {
                        $currentPage = (int)$_GET['p'];
                    }

                    $db = VkAntiSpam::get()->getDatabaseConnection();

                    $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `messages` WHERE `category` = 0 AND `groupId` = ?;');
                    $query->execute([
                        $groupId
                    ]);

                    $totalItems = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

                    $paginator = new Paginator(
                        $totalItems,
                        50,
                        $currentPage,
                        new class implements PaginatorClient {

                            public function printPage($paginator, $offset) {

                                $db = VkAntiSpam::get()->getDatabaseConnection();

                                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                                $query = $db->prepare('SELECT * FROM `messages` WHERE `category` = 0 AND `groupId` = ? ORDER BY `id` DESC LIMIT 50 OFFSET ?;');

                                $query->execute([
                                    (int)$_GET['g'],
                                    $offset
                                ]);

                                while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                    ?>
                                    <tr>
                                        <td><?= $currentRow['id']; ?></td>
                                        <td class="d-none d-sm-table-cell"><?= date('d.m.Y H:i:s', (int)$currentRow['date']); ?></td>
                                        <td><?= StringUtils::escapeHTML($currentRow['message']); ?></td>
                                        <td class="d-none d-md-table-cell">
                                            <a class="btn btn-secondary btn-sm" href="https://vk.com/wall-<?= $currentRow['groupId']; ?>_<?= $currentRow['vkContext']; ?>?reply=<?= $currentRow['vkId']; ?>" target="_blank">Стена</a>
                                            <a class="btn btn-danger btn-sm" href="javascript:void(0)">Это спам</a>
                                            <a class="btn btn-success btn-sm" href="javascript:void(0)">Это не спам</a>
                                        </td>
                                    </tr>
                                    <?php

                                }

                            }

                            public function getPageUrl($pageNumber) {
                                return '/messages/group?g=' . (int)$_GET['g'] . '&p=' . $pageNumber;
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
                                <th class="d-none d-sm-table-cell">Дата</th>
                                <th>Сообщение</th>
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

                }

                ?>
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';