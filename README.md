# yii2-zipkin-for-hprose
# important!!!
this package is extends from luoxiaojun/yii2-zipkin [here](https://github.com/luoxiaojun1992/yii2-zipkin)

## Description
Zipkin for hprose in Yii2

# Features
1. add zipkin hprose rpc

# How to use
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
     


