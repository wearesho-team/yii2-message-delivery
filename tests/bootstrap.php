<?php

use Dotenv\Dotenv;

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env')) {
    (new Dotenv(dirname(__DIR__)))->load();
}

getenv('DB_PATH') || putenv("DB_PATH=" . __DIR__ . '/db.sqlite');


\Yii::setAlias(
    '@Wearesho/Delivery/Yii2',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
);

Yii::setAlias('@runtime', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime');

\Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');

Yii::setAlias('@fileStorage', Yii::getAlias('@runtime'));

Yii::setAlias('@output', __DIR__ . DIRECTORY_SEPARATOR . 'output');
