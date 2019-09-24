<?php

use Lzz\Utils\Utils;
use Lzz\Account\Account;

require_once $_SERVER['DOCUMENT_ROOT'] . '/autoload.php';

if ($lzz->account->loggedIn()) {
    header('Location: /account/cabinet');
    exit(0);
}

$returnLogin = '';
$returnEmail = '';

if (
    isset($_POST['name']) &&
    isset($_POST['email']) &&
    isset($_POST['password']) &&
    isset($_POST['g-recaptcha-response'])
) {
    $ip = Utils::getUserIpAddress();
    $name = Utils::escapeHTML(trim($_POST['name']));
    $email = Utils::escapeHTML(trim($_POST['email']));

    $secret = '<google recaptcha secret key>';
    $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.'&response='.$_POST['g-recaptcha-response'].'&remoteip='.$ip);
    $responseData = json_decode($verifyResponse);

    if ($responseData->success) {

        $password = trim($_POST['password']);

        // validation

        if (Utils::rstrlen($password) > Account::PASSWORD_MAX_LENGTH) {

            $returnLogin = $name;
            $returnEmail = $email;
            require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
            Utils::headerError('Ошибка! Указанный Вами пароль слишком длинный!');

        }
        elseif (Utils::rstrlen($email) > Account::EMAIL_MAX_LENGTH) {

            $returnLogin = $name;
            require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
            Utils::headerError('Ошибка! Указанная Вами эл. почта слишком длинная!');

        }
        elseif (Utils::rstrlen($name) > Account::NAME_MAX_LENGTH) {

            $returnEmail = $email;
            require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
            Utils::headerError('Ошибка! Указанный Вами логин слишком длинный!');

        }
        elseif (!preg_match('/^[a-zA-Z\d]+$/', $name)) {

            $returnLogin = $name;
            $returnEmail = $email;

            require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
            Utils::headerError('Ошибка! Вы можете использовать только латинские буквы и цифры в Вашем логине!');

        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
            Utils::headerError('Ошибка! Вы ввели некорректный адрес эл. почты.');

            $returnLogin = $name;
            $returnEmail = $email;

        }
        else {

            $db = Utils::getDatabase();

            $query = $db->prepare('SELECT COUNT(*) AS `count` FROM `users` WHERE `name` = ? OR `email` = ? OR `ip` = ? LIMIT 1;');
            $query->execute([
                $name,
                $email,
                $ip
            ]);

            $result = $query->fetch(PDO::FETCH_ASSOC);

            if (!isset($result['count']) || (int)$result['count'] !== 0) {

                $returnLogin = $name;
                $returnEmail = $email;

                require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
                Utils::headerError('Ошибка! Указанный Вами логин уже занят, либо Вы уже регистрировались на сайте!');

            }
            else {

                $salt = Utils::generateCode(64);

                $passwordHashed = Account::hashPassword($password, $salt);

                $query = $db->prepare('INSERT INTO `users` (`name`, `email`, `password`, `salt`, `ip`, ipLastLogin, dateRegister, dateLastLogin, dateLastLink, linksShortened, linksShortenedToday) VALUES (?,?,?,?,?,?,?,?,?,?,?);');

                $query->execute([
                    $name,
                    $email,
                    $passwordHashed,
                    $salt,
                    $ip, // ip
                    $ip, // last ip
                    time(),
                    time(),
                    0, // dateLastLink,
                    0,
                    0
                ]);

                $jwt = Account::generateToken(json_encode([
                    'id' => (int)$db->lastInsertId(),
                    'name' => $name
                ]));

                setcookie('zl', $jwt, time()+(86400 * 30), '/', null);

                header('Location: /account/cabinet');

                exit(0);

            }

        }

    }
    else {
        $returnLogin = $name;
        $returnEmail = $email;

        require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
        Utils::headerError('Ошибка! Вы не прошли капчу. Пожалуйста, докажите, что Вы человек, а не робот!');
    }
}
else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/header.php';
}

?>
    <section>Регистрация:<hr></section>
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <form method="POST" action="">
        Логин:<br><br><input type="text" name="name" placeholder="Только латиница" size="35" maxlength="<?= Account::NAME_MAX_LENGTH ?>" required="" value="<?= $returnLogin; ?>"><br>
        Эл. почта:<br><br><input type="text" name="email" placeholder="example@mail.ru" size="35" maxlength="<?= Account::EMAIL_MAX_LENGTH ?>" required="" value="<?= $returnEmail; ?>"><br>
        Пароль:<br><br><input type="password" name="password" placeholder="Придумайте сложный пароль" size="35" maxlength="<?= Account::PASSWORD_MAX_LENGTH ?>" required=""><br>
        <div class="g-recaptcha" data-sitekey="<google recaptcha public key>"></div><br>
        <input type="submit" name="submit" value="Зарегистрироваться" class="btn no-border" style="margin-left: 0;">
    </form>
<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/structure/footer.php';