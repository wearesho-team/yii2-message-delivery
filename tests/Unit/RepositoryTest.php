<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit;

use Wearesho\Delivery;

class RepositoryTest extends Delivery\Yii2\Tests\TestCase
{
    use Delivery\Test\RepositoryTest;

    protected function getRepository(): Delivery\RepositoryInterface
    {
        return new Delivery\Yii2\Repository();
    }
}
