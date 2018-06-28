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

    public function actionSend(string $text, string $recipient): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->delivery->send(new Delivery\Message($text, $recipient));
    }
}
