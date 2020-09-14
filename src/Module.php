<?php

namespace Lxj\Yii2\Zipkin;

use yii\base\Event;
use yii\httpclient\Request;

/**
 * Class Module
 * @package Lxj\Yii2\Zipkin
 */
class Module extends \yii\base\Module
{
    use Middleware;

    public function init()
    {
        $this->trace();
        parent::init();
    }
}
