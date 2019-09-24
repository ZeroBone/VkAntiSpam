<?php

use VkAntiSpam\System\TextClassifier;

require $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form class="card" method="post">
                    <div class="card-header">
                        <h3 class="card-title">Проверка сообщения</h3>
                    </div>
                    <div class="card-body">
                        <?php

                        if (isset($_POST['text'])) {

                            $text = trim(htmlspecialchars(stripslashes($_POST['text'])));

                            $classifier = new TextClassifier();

                            $category = $classifier->classify($text);

                            if ($category === TextClassifier::CATEGORY_HAM) {
                                ?>
                                <div class="alert alert-success" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    Это сообщение было классифицировано как <b>не-спам</b>.
                                </div>
                                <?php
                            }
                            elseif ($category === TextClassifier::CATEGORY_SPAM) {

                                ?>
                                <div class="alert alert-danger" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    Это сообщение было классифицировано как <b>спам</b>.
                                </div>
                                <?php

                            }
                            else {

                                ?>
                                <div class="alert alert-warning" role="alert">
                                    <button type="button" class="close" data-dismiss="alert"></button>
                                    Это сообщение было классифицировано как <b>подозрительное</b>.
                                </div>
                                <?php

                            }

                        }

                        ?>
                        <div class="form-group">
                            <label class="form-label">Сообщение</label>
                            <textarea rows="5" class="form-control" placeholder="Введите сообщение" name="text" required=""></textarea>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Анализировать</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';