<?php

use VkAntiSpam\System\BanAccessSecurity;use VkAntiSpam\VkAntiSpam;

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
    <title>Разблокировка пользователей</title>
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
                    <?php

                    // echo http_build_query(BanAccessSecurity::constructHttpQueryArray(520463843, 135, 1570878032));

                    $ban = BanAccessSecurity::getBan();

                    if ($ban !== null) {

                        ?>
                        <form class="card" action="" method="post">
                            <div class="card-status bg-danger"></div>
                            <div class="card-body p-6">
                                <div class="card-title">Разблокировка пользователей - шаг 2</div>
                                <?php

                                $db = $vas->getDatabaseConnection();

                                // $query = $db->prepare();

                                ?>
                                <p>
                                    dafedfidhfskjfdhkj
                                </p>
                                <div class="alert alert-warning">
                                    <strong>Внимание!</strong> Нажимая на кнопку &quot;Я был заблокирован по ошибке&quot; Вы подтверждаете,
                                    что Вы ознакомились с правилами соответствующей группы и уверены в том, что блокировка ошибочная.<br>
                                    За ложное сообщение об ошибочной блокировке Вы можете быть заблокированы <b>навсегда</b>.
                                </div>
                                <div class="form-footer">
                                    <button type="submit" class="btn btn-primary btn-block">Я согласен с блокировкой</button>
                                    <button type="submit" class="btn btn-secondary btn-block">Я был заблокирован по ошибке</button>
                                </div>
                            </div>
                        </form>
                        <?php

                    }
                    else {

                        ?>
                        <div class="alert alert-danger" role="alert">
                            Несанкционированный доступ запрещён!
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