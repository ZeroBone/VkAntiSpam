<?php

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

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form method="post" action="https://httpbin.org/post">
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

                                $query = $db->query('SELECT `messages`.*, `vkGroups`.`name` AS `vkGroupName` FROM `messages`, `vkGroups` WHERE `category` = 0 ORDER BY `id` DESC LIMIT 50 OFFSET ' . (int)$offset . ';');

                                while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                    ?>
                                    <tr>
                                        <td><?= $currentRow['id']; ?></td>
                                        <td>
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="selectedMessages" value="<?= $currentRow['id']; ?>">
                                                <div class="custom-control-label"></div>
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
                                <th>#</th>
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
                        <div class="card-body">
                            <div class="form-group">
                                <div class="form-label">Выберите действие</div>
                                <div class="custom-switches-stacked">
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-danger rounded mr-4"></div>
                                        <input type="radio" name="action" value="1" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">
                                            Удалить выбранные сообщения и запомнить их как спам
                                        </span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-primary rounded mr-4"></div>
                                        <input type="radio" name="action" value="2" class="custom-switch-input" checked="">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Удалить выбранные сообщения</span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-warning rounded mr-4"></div>
                                        <input type="radio" name="action" value="2" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Удалить выбранные сообщения и заблокировать пользователей</span>
                                    </label>
                                    <label class="custom-switch">
                                        <div class="w-4 h-4 bg-success rounded mr-4"></div>
                                        <input type="radio" name="action" value="3" class="custom-switch-input">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="custom-switch-description">Запомнить выбранные сообщения как не-спам</span>
                                    </label>
                                </div>
                            </div>
                        </div>
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