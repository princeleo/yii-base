<?php

define('CODE_RUNTIME_LOCAL','local');
define('CODE_RUNTIME_DEV','dev');
define('CODE_RUNTIME_BETA','beta');
define('CODE_RUNTIME_TEST','test');
define('CODE_RUNTIME_ONLINE','online');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV',  CODE_RUNTIME_LOCAL);