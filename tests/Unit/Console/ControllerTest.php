<?php

namespace Wearesho\Delivery\Yii2\Tests\Unit\Console;

use yii\base;
use Wearesho\Delivery;

/**
 * Class ControllerTest
 * @package Wearesho\Delivery\Yii2\Tests\Unit\Console
 */
class ControllerTest extends Delivery\Yii2\Tests\TestCase
{
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
                'delivery' => [
                    'class' => Delivery\ServiceMock::class,
                ],
            ]
        );
    }

    public function testActionSend(): void
    {
        $text = 'Test Message';
        $recipient = 'testRecipient';

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->controller->runAction('send', [
            'message' => $text,
            $recipient,
        ]);

        /** @var Delivery\ServiceMock $delivery */
        $delivery = $this->controller->delivery;
        $this->assertInstanceOf(
            Delivery\ServiceMock::class,
            $delivery
        );
        $repository = $delivery->getRepository();

        $this->assertTrue(
            $repository->isSent(new Delivery\Message($text, $recipient))
        );
    }

    public function testOptionsAliases(): void
    {
        $aliases = $this->controller->optionAliases();
        $this->assertArraySubset([
            'm' => 'message',
        ], $aliases);
    }
}
