<?php
namespace SwoStar\Message\Http;
use SwoStar\Console\Input;
use SwoStar\Message\Request as BaseRequest;
use Swoole\Http\Request as SwooleRequest;

class Request extends BaseRequest
{
	protected $swooleRequest = null;
	protected $server = null;
	protected $uriPath = null;
	protected $method = null;
	/**
	* 初始化Request请求对象
	* 
	* @param SwooleRequest $swooleRequest
	*/
	public static function init(SwooleRequest $swooleRequest)
	{
		// 从容器中获取避免重复创建对象
		$self = app("httpRequest");
		$self->swooleRequest = $swooleRequest;
		$self->server = $swooleRequest->server;
		$self->uriPath = $self->server['request_uri'] ?? '';
		$self->method = $self->server['request_method'] ?? '';
		return $self;
	}
	/**
	* 获取访问的路径
	* 
	*/
	public function getUriPath()
	{
		return $this->uriPath;
	}
}
?>