<?php
namespace SwoStar\Server\Http;
use SwoStar\Server\Server;
use Swoole\Http\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
class HttpServer extends Server
{
	/**
	* 创建http服务
	*/
	protected function createServer()
	{
		$this->swooleServer = new SwooleServer($this->host, $this->port);
		echo "http://".$this->host.":".$this->port."\n";
	}
	/**
	* 初始化监听的事件
	* 六星教育 @shineyork老师
	*/
	protected function initEvent()
	{
		$this->setEvent('sub', [
			'request' => "onRequest"
		]);
	}
	/**
	* 设置http回调事件
	* 
	* @return [type] [description]
	*/
	public function onRequest(SwooleRequest $swooleRequest,SwooleResponse $swooleResponse)
	{
		if ($swooleRequest->server['request_uri'] == '/favicon.ico') {
			$swooleResponse->end('404');
			return null;
		}
		// 初始化http的请求类对象
		$request = HttpRequest::init($swooleRequest);
		// $response = HttpResponse::init($swooleResponse);
		$swooleResponse->end(app('route')->setMethod($request->getMethod())->match($request->getUriPath()));
		
	}
}
?>