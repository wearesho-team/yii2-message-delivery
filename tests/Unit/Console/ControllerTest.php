<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Console;

use yii\base;
use Wearesho\Delivery;

class ControllerTest extends Delivery\Yii2\Tests\TestCase
{
    protected Delivery\Yii2\Console\Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock = new Delivery\ServiceMock();
        $this->controller = \Yii::$container->get(
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
        $this->assertArrayHasKey('m', $aliases);
        $this->assertEquals('message', $aliases['m']);
    }
}
