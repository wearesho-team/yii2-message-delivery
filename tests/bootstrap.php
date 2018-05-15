<?php

// phpcs:disable

use Dotenv\Dotenv;

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

$dotEnv = new Dotenv(dirname(__DIR__));
$dotEnv->load();

defined('YII_DEBUG') || define("YII_DEBUG", true);
defined('YII_ENV') || define("YII_ENV", "test");

getenv('DB_PATH') || putenv("DB_PATH=" . __DIR__ . '/db.sqlite');


\Yii::setAlias(
    '@Wearesho/Delivery/Yii2',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
);

Yii::setAlias('@runtime', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime');

\Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');

Yii::setAlias('@fileStorage', Yii::getAlias('@runtime'));

Yii::setAlias('@output', __DIR__ . DIRECTORY_SEPARATOR . 'output');
