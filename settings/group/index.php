<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Utils\Paginator;
use VkAntiSpam\Utils\PaginatorClient;
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
            Укажите группу
        </h1>
    </div>
    <div class="row">
        <div class="col-12">
            <?php

            $currentPage = 1;

            if (isset($_GET['p'])) {
                $currentPage = (int)$_GET['p'];
            }

            $db = VkAntiSpam::get()->getDatabaseConnection();

            $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `vkGroupManagers` WHERE `userId` = ?;');
            $query->execute([
                $vas->account->getId()
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

                        $query = $db->prepare('SELECT `vkGroupManagers`.`vkGroupId`, `vkGroups`.`name` FROM `vkGroupManagers`, `vkGroups` WHERE `vkGroupManagers`.`userId` = ? AND `vkGroupManagers`.`vkGroupId` = `vkGroups`.`vkId` ORDER BY `vkGroupId` ASC LIMIT 50 OFFSET ?;');
                        $query->execute([
                            VkAntiSpam::get()->account->getId(),
                            $offset
                        ]);

                        while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                            ?>
                            <tr>
                                <td><?= $currentRow['name']; ?></td>
                                <td class="d-none d-md-table-cell">
                                    <a class="btn btn-success btn-sm" href="/settings/group/general?g=<?= $currentRow['vkGroupId']; ?>">Общие настройки</a>
                                    <a class="btn btn-secondary btn-sm" href="https://vk.com/public<?= $currentRow['vkGroupId']; ?>" target="_blank">Перейти в группу</a>
                                </td>
                            </tr>
                            <?php

                        }

                    }

                    public function getPageUrl($pageNumber) {
                        return '/settings/groups/?p=' . $pageNumber;
                    }
                }
            );

            $paginator->printPagination();

            ?>
            <div class="card">
                <table class="table card-table table-vcenter">
                    <thead>
                    <tr>
                        <th>Группа</th>
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