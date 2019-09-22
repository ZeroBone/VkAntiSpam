<?php

use VkAntiSpam\System\TextClassifier;
use VkAntiSpam\VkAntiSpam;

define('SECURITY_CANARY', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/autoload.php';

$antispam = new VkAntiSpam(require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php');

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/header.php';

?>
    <header class="jumbotron my-4">
        <h1 class="display-3">Welcome to VkAntiSpam system.</h1>
    </header>
    <div class="row">
        <div class="col-md-12">
            <?php

            if (isset($_POST['text'])) {

                $text = trim(htmlspecialchars(stripslashes($_POST['text'])));

                $classifier = new TextClassifier();

                $category = $classifier->classify($text);

                if ($category === TextClassifier::CATEGORY_HAM) {
                    ?>
                    <div class="alert alert-success" role="alert">
                        This message was classified as <b>ham</b>.
                    </div>
                    <?php
                }
                elseif ($category === TextClassifier::CATEGORY_SPAM) {

                    ?>
                    <div class="alert alert-danger" role="alert">
                        This message was classified as <b>spam</b>.
                    </div>
                    <?php

                }
                else {

                    ?>
                    <div class="alert alert-warning" role="alert">
                        This message was classified as <b>invalid</b>.
                    </div>
                    <?php

                }

            }

            ?>
            <form autocomplete="off" method="post" action="/">
                <div class="form-group">
                    <label for="exampleFormControlTextarea1">Message</label>
                    <textarea required="" class="form-control" id="exampleFormControlTextarea1" rows="5" name="text"></textarea>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" title="Classify">
                </div>
            </form>
        </div>
    </div>
<?php

require $_SERVER['DOCUMENT_ROOT'] . '/src/structure/footer.php';