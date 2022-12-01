<?php

declare(strict_types=1);

namespace Wearesho\Delivery\Yii2\Console;

use Wearesho\Delivery;
use yii\console;
use yii\base;
use yii\di;

class Controller extends console\Controller
{
    /** @var string|array|Delivery\ServiceInterface definition */
    public $delivery;

    /** @var string */
    public string $message = '';

    public function options($actionID): array
    {
        $options = [
            'message',
        ];

        return array_merge(parent::options($actionID), $options);
    }

    public function optionAliases(): array
    {
        $aliases = [
            'm' => 'message',
        ];

        return array_merge(parent::optionAliases(), $aliases);
    }

    /**
     * @throws base\InvalidConfigException
     */
    public function beforeAction($action): bool
    {
        $this->delivery = di\Instance::ensure($this->delivery, Delivery\ServiceInterface::class);
        return parent::beforeAction($action);
    }

    public function actionSend(string $recipient): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->delivery->send(new Delivery\Message($this->message, $recipient));
    }
}
