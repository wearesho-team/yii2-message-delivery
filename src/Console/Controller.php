<?php

namespace Wearesho\Delivery\Yii2\Console;

use yii\console;
use yii\di;
use Wearesho\Delivery;

/**
 * Class Controller
 * @package Wearesho\Delivery\Yii2\Console
 */
class Controller extends console\Controller
{
    /** @var string|array|Delivery\ServiceInterface definition */
    public $delivery;

    /** @var string */
    public $message = '';

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
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeAction($action)
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
