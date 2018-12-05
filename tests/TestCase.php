<?php

namespace Wearesho\Delivery\Yii2\Tests;

use yii\phpunit;
use yii\helpers;

/**
 * Class TestCase
 * @package Wearesho\Delivery\Yii2\Tests
 */
class TestCase extends phpunit\TestCase
{
    public function globalFixtures(): array
    {
        $fixtures = [
            [
                'class' => phpunit\MigrateFixture::class,
                'migrationNamespaces' => [
                    'Wearesho\\Delivery\\Yii2\\Migrations',
                ],
            ]
        ];

        return helpers\ArrayHelper::merge(parent::globalFixtures(), $fixtures);
    }
}
