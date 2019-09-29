<?php

use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\Utils\StringUtils;
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
        <div class="page-header">
            <h1 class="page-title">
                Панель управления
            </h1>
        </div>
        <div class="row row-cards">
            <?php

            $db = VkAntiSpam::get()->getDatabaseConnection();

            $query = $db->prepare('
SELECT COUNT(*) AS `result` FROM `messages` WHERE `date` > ?
UNION
SELECT COUNT(*) AS `result` FROM `bans` WHERE `date` > ?
UNION
SELECT COUNT(*) AS `result` FROM `trainingSet`
UNION
SELECT COUNT(*) AS `result` FROM `trainingSet` WHERE `category` = ?
UNION
SELECT COUNT(*) AS `result` FROM `words`
UNION
SELECT COUNT(*) AS `result` FROM `users`;
');

            $query->execute([
                time() - 86400,
                time() - 86400,
                TextClassifier::CATEGORY_HAM
            ]);

            $messagesToday = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $bansToday = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $totalTrained = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $hamTrained = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $wordsCount = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            $usersCount = (int)$query->fetch(PDO::FETCH_ASSOC)['result'];

            ?>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($messagesToday, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Сообщений за последние сутки</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($bansToday, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Блокировок за последние сутки</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($totalTrained - $hamTrained, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Спам-сообщений в базе</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($hamTrained, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Не-спам сообщений в базе</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($wordsCount, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Слов проанализировано</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                    <div class="card-body p-3 text-center">
                        <div class="h1 m-0"><?= number_format($usersCount, 0, '.', ' ') ?></div>
                        <div class="text-muted mb-4">Пользователей платформы</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cards">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Последние обработанные сообщения</h3>
                    </div>
                    <div id="chart-comments-processed" style="height: 15rem; max-height: 220px; position: relative;" class="c3"></div>
                    <div class="table-responsive">
                        <table class="table card-table table-striped table-vcenter">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th class="d-none d-sm-table-cell">Дата</th>
                                <th>Сообщение</th>
                                <th class="d-none d-md-table-cell">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            $query = $db->query('SELECT `id`, `date`, `message`, `category` FROM `messages` ORDER BY `id` DESC LIMIT 20;');

                            while (($currentRow = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                                ?>
                                <tr>
                                    <td><?= $currentRow['id']; ?></td>
                                    <td class="d-none d-sm-table-cell"><?= date('d.m.Y H:i:s', (int)$currentRow['date']); ?></td>
                                    <td><?= StringUtils::escapeHTML($currentRow['message']); ?></td>
                                    <td class="w-1">
                                        <?php if ((int)$currentRow['category'] === TextClassifier::CATEGORY_INVALID): ?>
                                            <span class="status-icon bg-secondary"></span> Спама не обнаружено
                                        <?php elseif ((int)$currentRow['category'] === TextClassifier::CATEGORY_HAM): ?>
                                            <span class="status-icon bg-success"></span> Не-спам
                                        <?php else: ?>
                                            <span class="status-icon bg-danger"></span> Спам
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php

                            }

                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    <?php

                        $commentCountValues = [];
                        // $commentTimeValues = [];

                        $query = $db->query('SELECT FLOOR(`date` / 3600) * 3600 AS `ts`, COUNT(1) AS `count` FROM `messages` GROUP BY `ts` ORDER BY `ts` DESC LIMIT 24;');

                        while (($result = $query->fetch(PDO::FETCH_ASSOC)) !== false) {

                            $commentCountValues[] = (int)$result['count'];
                            // $commentTimeValues[] = '\'' . date('d.m.Y', (int)$result['ts']) . '\'';

                        }

                        $commentCountValues = array_reverse($commentCountValues);
                        // $commentTimeValues = array_reverse($commentTimeValues);

                    ?>
                    require(['c3', 'jquery'], function(c3, $) {
                        $(document).ready(function(){
                            var chart = c3.generate({
                                bindto: '#chart-comments-processed', // id of chart wrapper
                                data: {
                                    columns: [
                                        // each columns data
                                        ['comments', <?= implode(',', $commentCountValues); ?>],
                                    ],
                                    type: 'area',
                                    groups: [
                                        [ 'comments', 'comments_time', 'data3']
                                    ],
                                    colors: {
                                        'comments': tabler.colors["blue"]
                                    },
                                    names: {
                                        // name of each serie
                                        'comments': "Количество сообщений",
                                        // 'comments_time': "Время",
                                    }
                                },
                                axis: {
                                    y: {
                                        padding: {
                                            bottom: 0,
                                        },
                                        show: false,
                                        tick: {
                                            outer: false
                                        }
                                    },
                                    x: {
                                        padding: {
                                            left: 0,
                                            right: 0
                                        },
                                        show: false
                                    }
                                },
                                legend: {
                                    position: 'inset',
                                    padding: 0,
                                    inset: {
                                        anchor: 'top-left',
                                        x: 20,
                                        y: 8,
                                        step: 10
                                    }
                                },
                                tooltip: {
                                    format: {
                                        title: function (x) {
                                            return "";
                                        }
                                    }
                                },
                                padding: {
                                    bottom: 0,
                                    left: -1,
                                    right: -1
                                },
                                point: {
                                    show: false
                                }
                            });
                        });
                    });
                </script>
            </div>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';