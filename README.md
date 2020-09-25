# yii2-zipkin
# important!!!
this package is extends from luoxiaojun/yii2-zipkin [here](https://github.com/luoxiaojun1992/yii2-zipkin)

## Description
Zipkin in Yii2

# features
1. fix bug
2. add zipkin hprose rpc

# how to use
 1. add zipkin to components
      ```
      'zipkin' => [
         'class' => TracerAlias::class,
         'serviceName' => 'basic',
         'endpointUrl' => 'http://192.168.99.100:9411/api/v2/spans',
         'sampleRate' => 2,
         'apiPrefix' => '/'
     ],
     ```
 2. zipkin sample
    * single controller
        add Lxj\Yii2\Zipkin\Filter to behaviors mathod.
        ```
        public function behaviors()
            {
                return [
                    'zipkin' => [
                        'class' => Filter::class,
                    ],
                ];
            }
        ```
    * modules
    ```
        class MyModule extends Lxj\Yii2\Zipkin\Module
    ```
 3. send requests with tag
     * use Lxj\Yii2\Zipkin\HttpClient to send http request
     * hprose rpc, add the following to components config 
     ```
     'rpc' => [
         'class' => \Lxj\Yii2\Zipkin\HproseRpcClient::class,
         //服务中心地址（也可以只包含一个）
         'discoverUrls' => [
             'http://192.168.99.100/index.php?r=discovery/index',
         ]
     ]
     ```
     


