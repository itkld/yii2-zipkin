<?php

namespace Itkld\Yii2\Zipkin;

use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\Exception;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\CurlTransport;

/**
 * 支持服务中心的rpc客户端
 * User: 李鹏飞 <523260513@qq.com>
 * Date: 2016/7/14
 * Time: 18:26
 */
class HproseRpcClient extends Component
{
    /**
     * @var array
     */
    public $discoverUrls;

    /**
     * @var array
     */
    private $_config;
    /**
     * @var array
     */
    private $_service = [];

    const EVENT_AFTER_INIT_CLIENT = 'after_init_client';

    /**
     * 获取服务url配置
     * @param $service
     * @return array
     * @throws ErrorException
     */
    protected function getServiceUrls($service)
    {
        if (!$this->_config) {
            $httpClient = new HttpClient();
            $httpClient->transport = CurlTransport::class;
            $requests = [];
            foreach ($this->discoverUrls as $discoverUrl) {
                $requests[] = $httpClient->get($discoverUrl);
            }
            $responses = $httpClient->batchSend($requests);

            $list = [];
            foreach ($responses as $response) {
                foreach ($response->data as $name => $url) {
                    $list[$name][] = $url;
                }
            }

            $this->_config = $list;
        }

        if (isset($this->_config[$service]) && $this->_config[$service]) {
            return $this->_config[$service];
        } else {
            throw new ErrorException('不存在该服务，请检查服务中心');
        }
    }

    private function hackHprose() {
        $distFilePath = \Yii::$app->getVendorPath() . '/hprose/hprose/src/Hprose/Http/Client.php';
        $newContent = file_get_contents(__DIR__ . '/hack/Client.php');
        $oldContent = file_get_contents($distFilePath);

        if($newContent != $oldContent) {
            if(!file_put_contents($distFilePath, $newContent)) {
                throw new Exception("can not hack file ${$distFilePath}");
            }
        }
    }

    /**
     * 查找服务
     * @param $service
     * @param $async
     * @return Client
     * @throws \Exception
     */
    public function getService($service, $async = false)
    {
        $this->hackHprose();
        if ($async) {
            $group = 'async';
        } else {
            $group = 'sync';
        }

        if (!isset($this->_service[$service][$group])) {
            $obj = new HproseClient($this->getServiceUrls($service), $async);
            $this->trigger(static::EVENT_AFTER_INIT_CLIENT, new Event([
                'sender' => &$obj
            ]));
            $this->_service[$service][$group] = $obj;
        }

        return $this->_service[$service][$group];
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        $group = 'async';

        foreach ($this->_service as $service){
            if(isset($service[$group])){
                /* @var $client Client */
                $client = $service[$group];
                $client->loop();
            }
        }
    }
}
