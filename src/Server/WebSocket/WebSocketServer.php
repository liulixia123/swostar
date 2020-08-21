<?php
namespace SwoStar\Server\WebSocket;
use SwoStar\Console\Input;
use SwoStar\Server\Http\HttpServer;
use Swoole\WebSocket\Server as SwooleServer;
class WebSocketServer extends HttpServer
{
	/**
	* 用于记录连接
	* @var Connections
	*/
	protected $conections;
	/**
	* 创建服务
	* 
	*/
	protected function createServer()
	{
		$this->swooleServer = new SwooleServer($this->host, $this->port);
		Input::info("ws://".$this->host.":".$this->port, "访问websocket服务");
	}
	/**
	* 初始化监听的事件
	* 
	*/
	protected function initEvent()
	{
		$this->setEvent('sub', [
			'request' => "onRequest",
			'open' => 'onOpen',
			'message' => 'onMessage',
			'close' => 'onClose'
		]);
	}
	/**
	 * 初始化swoole配置
	 * @return [type] [description]
	 */
	protected function initSetting()
	{
		$config = app('config');
		$this->port = $config->get('server.ws.port');
		$this->host = $config->get('server.ws.host');
		$this->config = $config->get('server.ws.swoole');
	}
	public function onOpen(SwooleServer $server, $request) {
		app('route')->setFlag('WebSocket')->setMethod('open')->match($request->server['path_info'], [$server, $request]);
		// 记录连接信息
		Connections::init($request->fd, $request->server['path_info']);
	}
	public function onMessage(SwooleServer $server, $frame) {
		app('route')->setFlag('WebSocket')->setMethod('message')->match((Connections::get($frame->fd))['path'], [$server, $frame]);
	}
	public function onClose(SwooleServer $server, $fd) {
		app('route')->setFlag('WebSocket')->setMethod('close')->match((Connections::get($fd))['path'], [$server, $frame]);
		Connections::del($fd);
	}

	//为了与方便后期项目的扩展，这里我们在onHandShake 中给他添加一个事件
	public function onHandShake($request, $response)
	{
		$this->app->make('event')->trigger('ws.hand', [$this, $request, $response]);
	}
}
?>