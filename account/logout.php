<?php

setcookie('zl', '', time()-(86400 * 30), '/', null);

header('Location: /account/login');