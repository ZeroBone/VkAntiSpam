<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Utils\Captcha;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!defined('VAS_IN_INSTALLATION')) {
    if (!$vas->account->loggedIn()) {
        Utils::redirect('/account/login');
        exit(0);
    }

    if (!$vas->account->isRole(Account::ROLE_ADMIN)) {
        Utils::redirect('/account/login');
        exit(0);
    }
}

$captcha = new Captcha();

$returnLogin = '';
$returnEmail = '';

$error = null;

if (
    isset($_POST['name']) &&
    isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['role'])
) {

    $name = StringUtils::escapeHTML(trim($_POST['name']));
    $email = StringUtils::escapeHTML(trim($_POST['email']));

    if ($captcha->isSubmitting() && $captcha->isHuman()) {

        $ip = Utils::getUserIpAddress();

        $password = trim($_POST['password']);

        $role = (int)$_POST['role'];

        // validation

        if (StringUtils::getStringLength($password) < Account::PASSWORD_MIN_LENGTH) {

            $returnLogin = $name;
            $returnEmail = $email;

            $error = 'Ошибка! Указанный Вами пароль слишком короткий!';

        }
        elseif (StringUtils::getStringLength($password) > Account::PASSWORD_MAX_LENGTH) {

            $returnLogin = $name;
            $returnEmail = $email;

            $error = 'Ошибка! Указанный Вами пароль слишком длинный!';

        }
        elseif (StringUtils::getStringLength($email) > Account::EMAIL_MAX_LENGTH) {

            $returnLogin = $name;

            $error = 'Ошибка! Указанная Вами эл. почта слишком длинная!';

        }
        elseif (StringUtils::getStringLength($name) > Account::NAME_MAX_LENGTH) {

            $returnEmail = $email;

            $error = 'Ошибка! Указанный Вами логин слишком длинный!';

        }
        elseif (StringUtils::getStringLength($name) < Account::NAME_MIN_LENGTH) {

            $returnEmail = $email;

            $error = 'Ошибка! Указанный Вами логин слишком короткий!';

        }
        elseif (
            $role !== Account::ROLE_VISITOR &&
            $role !== Account::ROLE_MODERATOR &&
            $role !== Account::ROLE_EDITOR &&
            $role !== Account::ROLE_ADMIN
        ) {

            $returnLogin = $name;
            $returnEmail = $email;

            $error = 'Ошибка! Некорректная роль.';

        }
        elseif (!preg_match('/^[a-zA-Z\d]+$/', $name)) {

            $returnLogin = $name;
            $returnEmail = $email;

            $error = 'Ошибка! Вы можете использовать только латинские буквы и цифры в Вашем логине!';

        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $returnLogin = $name;
            $returnEmail = $email;

            $error = 'Ошибка! Вы ввели некорректный адрес эл. почты.';

        }
        else {

            $db = VkAntiSpam::get()->getDatabaseConnection();

            $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `users` WHERE `name` = ? OR `email` = ? LIMIT 1;');
            $query->execute([
                $name,
                $email
            ]);

            $result = $query->fetch(PDO::FETCH_ASSOC);

            if (!isset($result['count']) || (int)$result['count'] !== 0) {

                $returnLogin = $name;
                $returnEmail = $email;

                $error = 'Ошибка! Указанный Вами логин или электронная почта уже заняты.';

            }
            else {

                $salt = StringUtils::generateCode(64);

                $passwordHashed = Account::hashPassword($password, $salt);

                $query = $db->prepare('INSERT INTO `users` (`name`, `email`, `password`, `salt`, `csrfToken`, `ip`, ipLastLogin, dateRegister, dateLastLogin, role) VALUES (?,?,?,?,?,?,?,?,?,?);');

                $query->execute([
                    $name,
                    $email,
                    $passwordHashed,
                    $salt,
                    StringUtils::generateCode(32),
                    $ip, // ip
                    $ip, // last ip
                    time(),
                    time(),
                    $role
                ]);

                $jwt = Account::generateToken(json_encode([
                    'id' => (int)$db->lastInsertId(),
                    'name' => $name,
                    'role' => $role
                ]));

                setcookie('zl', $jwt, time() + (86400 * 30), '/', null);

                header('Location: /account/cabinet');

                exit(0);

            }

        }

    }
    else {

        $returnLogin = $name;
        $returnEmail = $email;

        $error = 'Ошибка! Вы не прошли капчу. Пожалуйста, докажите, что Вы человек, а не робот!';

    }

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
    <link rel="icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" type="image/x-icon" href="./favicon.ico"/>
    <title>Регистрация нового аккаунта</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <script src="/assets/js/require.min.js"></script>
    <script>
        requirejs.config({
            baseUrl: '.'
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
                <div class="col col-login mx-auto">
                    <?php $captcha->printScript(); ?>
                    <div class="text-center mb-6">
                        VkAntiSpam
                    </div>
                    <form class="card" action="" method="post">
                        <div class="card-body p-6">
                            <?php

                            if ($error !== null) {

                                ?>
                                <div class="alert alert-icon alert-danger" role="alert">
                                    <i class="fe fe-alert-triangle mr-2" aria-hidden="true"></i> <?= $error ?>
                                </div>
                                <?php

                            }

                            ?>
                            <div class="card-title">Регистрация нового пользователя</div>
                            <div class="form-group">
                                <label class="form-label">Логин</label>
                                <input type="text" class="form-control" name="name" placeholder="Имя пользователя" value="<?= $returnLogin ?>" required="" maxlength="<?= Account::NAME_MAX_LENGTH ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Эл. почта</label>
                                <input type="email" class="form-control" name="email" placeholder="Адрес эл. почты" value="<?= $returnEmail ?>" required="" maxlength="<?= Account::EMAIL_MAX_LENGTH ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Пароль</label>
                                <input type="password" class="form-control" name="password" placeholder="Пароль" required="" minlength="<?= Account::PASSWORD_MIN_LENGTH ?>" maxlength="<?= Account::PASSWORD_MAX_LENGTH ?>">
                            </div>
                            <div class="form-group">
                                <div class="form-label">Роль</div>
                                <div class="custom-controls-stacked">
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="role" value="<?= Account::ROLE_VISITOR ?>" checked="">
                                        <div class="custom-control-label">Наблюдатель</div>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="role" value="<?= Account::ROLE_MODERATOR ?>">
                                        <div class="custom-control-label">Модератор</div>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="role" value="<?= Account::ROLE_EDITOR ?>">
                                        <div class="custom-control-label">Редактор</div>
                                    </label>
                                    <label class="custom-control custom-radio">
                                        <input type="radio" class="custom-control-input" name="role" value="<?= Account::ROLE_ADMIN ?>">
                                        <div class="custom-control-label">Администратор</div>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php $captcha->printBox(); ?>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary btn-block">Зарегистрировать</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>