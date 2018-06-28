<?php

namespace Wearesho\Delivery\Yii2\Tests\Console;

use yii\base;
use yii\phpunit\TestCase;
use Wearesho\Delivery;

/**
 * Class ControllerTest
 * @package Wearesho\Delivery\Yii2\Tests\Console
 */
class ControllerTest extends TestCase
{
    /** @var Delivery\ServiceMock */
    protected $mock;

    /** @var Delivery\Yii2\Console\Controller */
    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock = new Delivery\ServiceMock();
        $this->controller = $this->container->get(
            Delivery\Yii2\Console\Controller::class,
            [
                'delivery',
                new base\Module('default'),
            ],
            [
                'delivery' => $this->mock,
            ]
        );
    }

    public function testActionSend(): void
    {
        $text = 'Test Message';
        $recipient = 'testRecipient';

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->controller->createAction('send')->runWithParams([
            'text' => $text,
            'recipient' => $recipient,
        ]);

        $repository = $this->mock->getRepository();

        $this->assertTrue(
            $repository->isSent(new Delivery\Message($text, $recipient))
        );
    }
}
