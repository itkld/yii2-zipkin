<?php

namespace Lxj\Yii2\Zipkin;

use yii\web\Request;
use Zipkin\Propagation\Getter;
use Zipkin\Propagation\Setter;

final class YiiRequestHeaders implements Getter, Setter
{
    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     */
    public function get($carrier, $key)
    {
        $lKey = strtolower($key);
        $v =  $carrier->getHeaders()->has($lKey) ? $carrier->getHeaders()->get($lKey) : null;
        return $v;
    }

    /**
     * {@inheritdoc}
     *
     * @param Request $carrier
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function put(&$carrier, $key, $value)
    {
        $lKey = strtolower($key);
        $carrier = $carrier->getHeaders()->add($lKey, $value);
    }
}
