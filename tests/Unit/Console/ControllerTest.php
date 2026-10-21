<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Tests\Unit\Console;

use Wearesho\Delivery;
use Wearesho\Delivery\Yii2\Tests\ServiceMock;
use yii\base;

class ControllerTest extends Delivery\Yii2\Tests\TestCase
{
    private Delivery\Yii2\Console\Controller $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new Delivery\Yii2\Console\Controller(
            'delivery',
            new base\Module('default'),
            [
                'delivery' => [
                    'class' => ServiceMock::class,
                ],
            ]
        );
    }

    public function testActionSend(): void
    {
        $this->controller->runAction('send', [
            'message' => 'Test Message',
            '380930000001',
        ]);

        $delivery = $this->controller->delivery;
        $this->assertInstanceOf(ServiceMock::class, $delivery);
        $this->assertCount(1, $delivery->messages);
        $this->assertEquals('Test Message', $delivery->messages[0]->getText());
        $this->assertEquals('380930000001', $delivery->messages[0]->getRecipient());
    }

    public function testActionSendWithMessageAlias(): void
    {
        $this->controller->runAction('send', [
            '_aliases' => [
                'm' => 'Aliased Message',
            ],
            '380930000002',
        ]);

        /** @var ServiceMock $delivery */
        $delivery = $this->controller->delivery;
        $this->assertCount(1, $delivery->messages);
        $this->assertEquals('Aliased Message', $delivery->messages[0]->getText());
        $this->assertEquals('380930000002', $delivery->messages[0]->getRecipient());
    }

    public function testOptions(): void
    {
        $this->assertContains('message', $this->controller->options('send'));
        $this->assertEquals('message', $this->controller->optionAliases()['m'] ?? null);
    }
}
