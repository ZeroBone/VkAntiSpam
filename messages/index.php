<?php

use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\Utils\Paginator;
use VkAntiSpam\Utils\PaginatorClient;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

VkAntiSpam::web();

if (!VkAntiSpam::get()->account->loggedIn()) {

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

                $query = $db->query('SELECT COUNT(*) AS `count` FROM `messages`;');
                $totalItems = (int)$query->fetch(PDO::FETCH_ASSOC)['count'];

                $paginator = new Paginator(
                    $totalItems,
                    50,
                    $currentPage,
                    new class implements PaginatorClient {

                        public function printPage($paginator, $offset) {

                            $db = VkAntiSpam::get()->getDatabaseConnection();

                            $query = $db->query('SELECT * FROM `messages` ORDER BY `id` LIMIT 50 OFFSET ' . (int)$offset . ';');

                            while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                ?>
                                <tr>
                                    <td>
                                        <label class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="" value="">
                                            <div class="custom-control-label"></div>
                                        </label>
                                    </td>
                                    <td><?= $currentRow['id']; ?></td>
                                    <td><?= $currentRow['message']; ?></td>
                                    <td class="d-none d-sm-table-cell">February 10, 1994</td>
                                    <td class="d-none d-md-table-cell">$1616.70</td>
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
                                <th class="w-1"></th>
                                <th class="w-1"></th>
                                <th>Name</th>
                                <th class="d-none d-sm-table-cell">Date</th>
                                <th class="d-none d-md-table-cell">Amount</th>
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
                <!--<ul class="pagination">
                    <li class="page-item page-prev disabled">
                        <a class="page-link" href="#" tabindex="-1">
                            Prev
                        </a>
                    </li>
                    <li class="page-item"><a class="page-link" href="#">1</a></li>
                    <li class="page-item active"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">4</a></li>
                    <li class="page-item"><a class="page-link" href="#">5</a></li>
                    <li class="page-item page-next">
                        <a class="page-link" href="#">
                            Next
                        </a>
                    </li>
                </ul>-->
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';