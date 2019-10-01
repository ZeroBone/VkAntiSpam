<?php

use VkAntiSpam\Account\Account;
use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\Utils\Utils;
use VkAntiSpam\VkAntiSpam;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$vas = VkAntiSpam::web();

if (!$vas->account->loggedIn()) {
    Utils::redirect('/account/login');
    exit(0);
}

if (!$vas->account->isRole(Account::ROLE_SUPER_MODERATOR)) {
    Utils::redirect('/account/login');
    exit(0);
}

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form class="card" method="post">
                    <div class="card-header">
                        <h3 class="card-title">Обучение системы распознавания спама</h3>
                    </div>
                    <div class="card-body">
                        <?php

                        if (
                            isset($_POST['text']) &&
                            isset($_POST['category'])
                        ) {

                            $text = trim(htmlspecialchars(stripslashes($_POST['text'])));

                            $category = ((int)$_POST['category'] === 1) ? TextClassifier::CATEGORY_SPAM : TextClassifier::CATEGORY_HAM;

                            $classifier = new TextClassifier();

                            $successCount = 0;
                            $errorCount = 0;

                            // start learning

                            foreach (explode("\n", $text) as $message) {

                                if ($classifier->learn(trim($message), $category)) {
                                    $successCount++;
                                }
                                else {
                                    $errorCount++;
                                }

                            }

                            if ($successCount === 0) {

                                ?>
                                <div class="alert alert-warning" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    <?= $errorCount ?> сообщений не прошли предварительную фильтрацию и не могут быть использованы как материал для обучения.
                                </div>
                                <?php

                            }
                            else {

                                ?>
                                <div class="alert alert-success" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    Успешно: <b><?= $successCount ?></b><br>
                                    Не прошли предварительную фильтрацию: <b><?= $errorCount ?></b>
                                </div>
                                <?php

                            }

                        }

                        ?>
                        <div class="form-group">
                            <label class="form-label">Сообщения</label>
                            <textarea rows="6" class="form-control" placeholder="Введите сообщения." name="text" required=""></textarea>
                            <div class="form-control-plaintext">Каждое сообщение должно быть на отдельной строке</div>
                        </div>
                        <div class="form-group">
                            <div class="form-label">Категория</div>
                            <div class="custom-controls-stacked">
                                <label class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="category" value="1" checked="">
                                    <div class="custom-control-label">Спам</div>
                                </label>
                                <label class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="category" value="0">
                                    <div class="custom-control-label">Не-спам</div>
                                </label>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Обучить</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';