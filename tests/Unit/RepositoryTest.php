<?php

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;

/**
 * Class RepositoryTest
 * @package Wearesho\Delivery\Yii2\Tests\Unit
 * @internal
 */
class RepositoryTest extends Delivery\Yii2\Tests\TestCase
{
    use Delivery\Test\RepositoryTest;

    protected function getRepository(): Delivery\RepositoryInterface
    {
        return new Delivery\Yii2\Repository();
    }
}
