easy-http
=========

EasyHttp是一个帮助你忽略不同的php环境情况，无差别地发送http请求的php类。
你不再需要关注当前php环境是否支持curl/fsockopen/fopen，EasyHttp会自动选择一个最合适的方式去发出http请求。
EasyHttp源于WordPress中的WP_Http类，去除了所有对WordPress其他函数的依赖，将其拆分到不同的文件中，并做了少量简化。

== DEMO ==
require 'EasyHttp.php';
require 'EasyHttp/Curl.php';
require 'EasyHttp/Cookie.php';
require 'EasyHttp/Encoding.php';
require 'EasyHttp/Fsockopen.php';
require 'EasyHttp/Proxy.php';
require 'EasyHttp/Streams.php';

$http = new EasyHttp();
$response = $http->request('http://localhost/', array(
		'method' => 'GET',		//	GET/POST
		'timeout' => 5,			//	超时的秒数
		'redirection' => 5,		//	最大重定向次数
		'httpversion' => '1.0',	//	1.0/1.1
		//'user-agent' => 'USER-AGENT',		
		'blocking' => true,		//	是否阻塞
		'headers' => array(),	//	header信息
		'cookies' => array(),	//	关联数组形式的cookie信息
		'body' => null,
		'compress' => false,	//	是否压缩
		'decompress' => true,	//	是否自动解压缩结果
		'sslverify' => true,
		'stream' => false,
		'filename' => null		//	如果stream = true，则必须设定一个临时文件名
	));
var_dump($response);

== Contact ==
EasyHttp由多说网的沈振宇维护，如果你有什么疑问或者建议，欢迎写zhenyu (at) duoshuo.com，或者在新浪微博上私信@沈振宇。

== Showcases ==
* 多说评论系统 for DedeCMS插件
* 多说评论系统 搬家程序
