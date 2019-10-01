<?php

use VkAntiSpam\Account\Account;
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

if (!$vas->account->isRole(Account::ROLE_SUPER_MODERATOR)) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php

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

                            $query = $db->query('SELECT * FROM `messages` WHERE `category` = 0 ORDER BY `id` DESC LIMIT 50 OFFSET ' . (int)$offset . ';');

                            while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                ?>
                                <tr>
                                    <td><?= $currentRow['id']; ?></td>
                                    <td class="d-none d-sm-table-cell"><?= date('d.m.Y H:i:s', (int)$currentRow['date']); ?></td>
                                    <td><?= StringUtils::escapeHTML($currentRow['message']); ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <a class="btn btn-secondary btn-sm" href="https://vk.com/wall-<?= $currentRow['groupId']; ?>_<?= $currentRow['context']; ?>?reply=<?= $currentRow['vkId']; ?>" target="_blank">Стена</a>
                                        <a class="btn btn-danger btn-sm" href="javascript:void(0)">Это спам</a>
                                        <a class="btn btn-success btn-sm" href="javascript:void(0)">Это не спам</a>
                                    </td>
                                </tr>
                                <?php

                            }

                        }

                        public function getPageUrl($pageNumber) {
                            return '/messages/all?p=' . $pageNumber;
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

                ?>
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';