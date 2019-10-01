<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\VkAntiSpam;

if (!defined('SECURITY_CANARY')) {
    exit(0);
}

if (!VkAntiSpam::get()->account->loggedIn()) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new Exception('The user should be logged in.');
}

?><!DOCTYPE html>
<html lang="ru" dir="ltr">
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
    <meta name="author" content="Alexander Mayorov">
    <link rel="icon" href="/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
    <title>VkAntiSpam - система фильтрации спама</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <script src="/assets/js/require.min.js"></script>
    <script>
        requirejs.config({
            baseUrl: "/"
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
    <!--<script>
        require.config({
            shim: {
                'datatables': ['jquery','core'],
            },
            paths: {
                'datatables': '/assets/plugins/datatables/datatables.min',
            }
        });
    </script>-->
</head>
<body class="">
<div class="page">
    <div class="page-main">
        <div class="header py-4">
            <div class="container">
                <div class="d-flex">
                    <a class="header-brand" href="/">
                        VkAntiSpam
                        <!--<img src="./demo/brand/tabler.svg" class="header-brand-img" alt="tabler logo">-->
                    </a>
                    <div class="d-flex order-lg-2 ml-auto">
                        <div class="nav-item d-none d-md-flex">
                            <a href="https://github.com/ZeroBone" class="btn btn-sm btn-outline-primary" target="_blank">GitHub</a>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="nav-link pr-0 leading-none" data-toggle="dropdown">
                                <span class="avatar avatar-purple"><?= strtoupper(substr(VkAntiSpam::get()->account->getName(), 0, 2)); ?></span>
                                <span class="ml-2 d-none d-lg-block">
                                    <?php

                                    $role = '';

                                    switch (VkAntiSpam::get()->account->getRole()) {

                                        case Account::ROLE_VISITOR:
                                            $role = 'Пользователь';
                                            break;

                                        case Account::ROLE_SUPER_MODERATOR:
                                            $role = 'Модератор';
                                            break;

                                        case Account::ROLE_EDITOR:
                                            $role = 'Редактор';
                                            break;

                                        case Account::ROLE_ADMIN:
                                            $role = 'Администратор';
                                            break;

                                        default:
                                            break;

                                    }

                                    ?>
                                    <span class="text-default"><?= VkAntiSpam::get()->account->getName() ?></span>
                                    <small class="text-muted d-block mt-1"><?= $role ?></small>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                <a class="dropdown-item" href="/account/cabinet">
                                    <i class="dropdown-icon fe fe-user"></i> Личный кабинет
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/account/logout">
                                    <i class="dropdown-icon fe fe-log-out"></i> Выйти
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="#" class="header-toggler d-lg-none ml-3 ml-lg-0" data-toggle="collapse" data-target="#headerMenuCollapse">
                        <span class="header-toggler-icon"></span>
                    </a>
                </div>
            </div>
        </div>
        <div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg order-lg-first">
                        <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                            <li class="nav-item">
                                <a href="/" class="nav-link"><i class="fe fe-home"></i> Панель управления</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown"><i class="fe fe-minus-circle"></i> Антиспам</a>
                                <div class="dropdown-menu dropdown-menu-arrow">
                                    <a href="/antispam/test" class="dropdown-item">Проверка сообщений</a>
                                    <?php

                                    if (VkAntiSpam::get()->account->isRole(Account::ROLE_SUPER_MODERATOR)) {
                                        ?>
                                        <a href="/antispam/train" class="dropdown-item">Обучение</a>
                                        <?php
                                    }

                                    ?>
                                </div>
                            </li>
                            <?php if (VkAntiSpam::get()->account->isRole(Account::ROLE_SUPER_MODERATOR)): ?>
                            <li class="nav-item">
                                <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown"><i class="fe fe-settings"></i> Настройки</a>
                                <div class="dropdown-menu dropdown-menu-arrow">
                                    <a href="/settings/group/" class="dropdown-item">Настройки групп</a>
                                    <?php

                                    if (VkAntiSpam::get()->account->isRole(Account::ROLE_ADMIN)) {

                                        ?>
                                        <a href="/account/register" class="dropdown-item">Регистрация пользователей</a>
                                        <a href="/settings/groups" class="dropdown-item">Группы</a>
                                        <?php

                                    }

                                    ?>
                                </div>
                            </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="/messages/" class="nav-link"><i class="fe fe-message-square"></i> Сообщения</a>
                            </li>
                            <!--<li class="nav-item dropdown">
                                <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown"><i class="fe fe-file"></i> Pages</a>
                                <div class="dropdown-menu dropdown-menu-arrow">
                                    <a href="./profile.html" class="dropdown-item ">Profile</a>
                                    <a href="./login.html" class="dropdown-item ">Login</a>
                                    <a href="./register.html" class="dropdown-item ">Register</a>
                                    <a href="./forgot-password.html" class="dropdown-item ">Forgot password</a>
                                    <a href="./400.html" class="dropdown-item ">400 error</a>
                                    <a href="./401.html" class="dropdown-item ">401 error</a>
                                    <a href="./403.html" class="dropdown-item ">403 error</a>
                                    <a href="./404.html" class="dropdown-item ">404 error</a>
                                    <a href="./500.html" class="dropdown-item ">500 error</a>
                                    <a href="./503.html" class="dropdown-item ">503 error</a>
                                    <a href="./email.html" class="dropdown-item ">Email</a>
                                    <a href="./empty.html" class="dropdown-item active">Empty page</a>
                                    <a href="./rtl.html" class="dropdown-item ">RTL mode</a>
                                </div>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="./form-elements.html" class="nav-link"><i class="fe fe-check-square"></i> Forms</a>
                            </li>
                            <li class="nav-item">
                                <a href="./gallery.html" class="nav-link"><i class="fe fe-image"></i> Gallery</a>
                            </li>
                            <li class="nav-item">
                                <a href="./docs/index.html" class="nav-link"><i class="fe fe-file-text"></i> Documentation</a>
                            </li>-->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="my-3 my-md-5">