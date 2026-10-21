<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use DatabaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->tearDownDatabase();
    }
}
