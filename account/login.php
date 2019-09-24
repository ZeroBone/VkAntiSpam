<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\Utils\Captcha;
use VkAntiSpam\Utils\StringUtils;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if ($vas->account->loggedIn()) {
    Utils::redirect('/account/cabinet');
    exit(0);
}

$returnLogin = '';

if (isset($_GET['name'])) {
	$returnLogin = StringUtils::escapeHTML($_GET['name']);
}

$error = null;
$captcha = new Captcha();

if (
    isset($_POST['name']) &&
    isset($_POST['password'])
) {

    $name = StringUtils::escapeHTML(trim($_POST['name']));

	if ($captcha->isSubmitting() && $captcha->isHuman()) {

	    $db = VkAntiSpam::get()->getDatabaseConnection();
		
		$query = $db->prepare('SELECT * FROM `users` WHERE `name` = ? LIMIT 1;');
		$query->execute([$name]);

		$result = $query->fetch(PDO::FETCH_ASSOC);

		if (isset($result['id'])) {

			$password = trim($_POST['password']);

			// unknown password salted with the right salt
			$hashedPassword = Account::hashPassword($password, $result['salt']);

			// if the salted user password matches the unknown salted password
			if (hash_equals($hashedPassword, $result['password'])) {

				$userId = (int)$result['id'];

				$query = $db->prepare('UPDATE `users` SET `ipLastLogin` = ?, `dateLastLogin` = ? WHERE `id` = ?;');
				$query->execute([
                    Utils::getUserIpAddress(),
                    time(),
                    $userId
                ]);

				$jwt = Account::generateToken(json_encode([
                    'id' => $userId,
                    'name' => $result['name'],
                    'role' => (int)$result['role']
                ]));

				setcookie('zl', $jwt, time() + (86400 * 30), '/', null);

                Utils::redirect('/account/cabinet');

                exit(0);
			}
		}

        $error = 'Ошибка! Вы ввели неверный логин и/или пароль!';

	}
	else {

		$returnLogin = $name;

		$error = 'Ошибка! Вы не прошли капчу. Пожалуйста, докажите, что Вы человек, а не робот!';

	}

}

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
    <title>Авторизация</title>
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
                        <!--<img src="/assets/brand/tabler.svg" class="h-6" alt="">-->
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
                            <div class="card-title">Авторизация</div>
                            <div class="form-group">
                                <label class="form-label">Логин</label>
                                <input type="text" name="name" class="form-control" id="exampleInputEmail1" placeholder="Имя пользователя" value="<?= $returnLogin ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Пароль</label>
                                <input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Пароль">
                            </div>
                            <div class="form-group">
                                <?php $captcha->printBox(); ?>
                            </div>
                            <div class="form-footer">
                                <button type="submit" class="btn btn-primary btn-block">Войти</button>
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