<?php

use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\VkAntiSpam;

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#4188c9">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <link rel="icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" type="image/x-icon" href="./favicon.ico" />
    <title>Топ комментаторов</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <script src="/assets/js/require.min.js"></script>
    <script>
        requirejs.config({
            baseUrl: '/'
        });
    </script>
    <!-- Dashboard Core -->
    <link href="/assets/css/dashboard.css" rel="stylesheet" />
    <script src="/assets/js/dashboard.js"></script>
    <!-- c3.js Charts Plugin -->
    <link href="/assets/plugins/charts-c3/plugin.css" rel="stylesheet" />
    <script src="/assets/plugins/charts-c3/plugin.js"></script>
    <!-- Google Maps Plugin -->
    <link href="/assets/plugins/maps-google/plugin.css" rel="stylesheet" />
    <script src="/assets/plugins/maps-google/plugin.js"></script>
    <!-- Input Mask Plugin -->
    <script src="/assets/plugins/input-mask/plugin.js"></script>
</head>
<body class="">
<div class="page">
    <div class="page-single">
        <div class="container">
            <div class="row">
                <div class="col col-lg-8 col-md-10 col-sm-12 mx-auto">
                    <div class="text-center mb-6">
                        <h3>Топ комментаторов</h3>
                    </div>
                    <?php

                    if (true) {

                        ?>
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover table-outline table-vcenter text-nowrap card-table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th class="text-center w-1"><i class="icon-people"></i></th>
                                        <th>Пользователь</th>
                                        <th>Репутация</th>
                                        <th class="text-center"><i class="icon-settings"></i></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php

                                    $db = $vas->getDatabaseConnection();

                                    $query = $db->query('SELECT `vkId`, `date`, `firstName`, `lastName`, `photo_50`, `reputation` FROM `vkUsers` ORDER BY `reputation` DESC, `date` ASC LIMIT 100;');

                                    $firstRow = true;

                                    $row = $query->fetch(PDO::FETCH_ASSOC);

                                    $i = 0;

                                    if ($row !== false) {

                                        $maxReputation = (int)$row['reputation'];

                                        if ($maxReputation === 0) {
                                            $maxReputation = 1;
                                        }

                                        do {

                                            $i++;

                                            $reputationPercent = ceil((float)$row['reputation'] / $maxReputation * 100);

                                            $reputationColor = $reputationPercent >= 50 ? 'green' : 'yellow';

                                            ?>
                                            <tr>
                                                <td>
                                                    <?= $i ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="avatar d-block" style="background-image: url(<?= $row['photo_50']; ?>)">
                                                        <!--<span class="avatar-status bg-green"></span>-->
                                                    </div>
                                                </td>
                                                <td>
                                                    <div><?= StringUtils::escapeHTML($row['firstName']); ?> <?= StringUtils::escapeHTML($row['lastName']); ?></div>
                                                    <!--<div class="small text-muted">
                                                        Registered: Mar 19, 2018
                                                    </div>-->
                                                </td>
                                                <td>
                                                    <div class="clearfix">
                                                        <div class="float-left">
                                                            <strong><?= $reputationPercent ?>%</strong>
                                                        </div>
                                                    </div>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar bg-<?= $reputationColor ?>" role="progressbar" style="width: <?= $reputationPercent ?>%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="item-action dropdown">
                                                        <a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <!--<div class="dropdown-divider"></div>-->
                                                            <a href="https://vk.com/id<?= $row['vkId']; ?>" class="dropdown-item" target="_blank"><i class="dropdown-icon fe fe-link"></i> Перейти на страницу</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php

                                        } while (($row = $query->fetch(PDO::FETCH_ASSOC)) !== false);

                                    }

                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php

                    }
                    else {

                        ?>
                        <div class="alert alert-danger" role="alert">
                            Данная страница отключена администратором.
                        </div>
                        <?php

                    }

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>