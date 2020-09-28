# yii2-zipkin-for-hprose

## Description
Zipkin for hprose in Yii2

# Features
1. add zipkin hprose rpc

# How to use
* hprose rpc, add the following to components config 
	```
	'rpc' => [
	 'class' => \Itkld\Yii2\Zipkin\HproseRpcClient::class,
	 //服务中心地址（也可以只包含一个）
	 'discoverUrls' => [
		 'http://192.168.99.100/index.php?r=discovery/index',
	 ]
	]
	```
     


