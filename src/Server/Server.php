<?php
namespace SwoStar\Server;
use SwoStar\Foundation\Application;
use SwoStar\Support\Inotify;
use SwoStar\Console\Input;
use SwoStar\RPC\Rpc;
use Swoole\Server as SwooleServer;

use Redis;
/**
* 服务的父级类
*/
abstract class Server
{
	protected $app;
	/**
	* @var SwoStar/Support/Inotify
	*/
	protected $inotify = null;
	/**
	* 是否开启文件检测
	* @var bool
	*/
	protected $watchFile = false;
	/**
	* 用于记录系统pid的信息
	* @var string
	*/
	protected $pidFile = "swostar.pid";
	/**
	* 用于记录pid的信息
	* @var array
	*/
	protected $pidMap = [
		'masterPid' => 0,
		'managerPid' => 0,
		'workerPids' => [],
		'taskPids' => []
	];

	/**
	* swostar server
	* @var Server|HttpServer|WebSocketServer|
	*/
	protected $swooleServer;
	/**
	* 服务的类型
	* @var tcp|udp|http|ws
	*/
	protected $serverType = 'TCP';
	/**
	* 监听端口
	* @var int
	*/
	protected $port = 9000;
	/**
	* 监听的端口
	* @var string
	*/
	protected $host = '0.0.0.0';
	protected $mod = SWOOLE_PROCESS;
	protected $sock_type = SWOOLE_SOCK_TCP;
	/**
	* 注册的回调事件
	* [
	* // 这是所有服务均会注册的时间
	* "server" => [],
	* // 子类的服务
	* "sub" => [],
	* // 额外扩展的回调函数
	* "ext" => []
	* ]
	*
	* @var array
	*/
	protected $event = [
		// 这是所有服务均会注册的时间
		"server" => [
			"start" => "onStart",
			"managerStart" => "onManagerStart",
			"managerStop" => "onManagerStop",
			"shutdown" => "onShutdown",
			"workerStart" => "onWorkerStart",
			"workerStop" => "onWorkerStop",
			"workerError" => "onWorkerError",
		],
		// 子类的服务
		"sub" => [],
		// 额外扩展的回调函数
		// 如 ontart等
		"ext" => []
	];
	/**
	* swoole的相关配置信息
	* @var array
	*/
	protected $config = [
		'task_worker_num' => 0,
	];

	protected $flag;
	/**
	* 创建服务
	* 
	*/
	protected abstract function createServer();
	/**
	* 初始化监听的事件
	*/
	protected abstract function initEvent();

	protected abstract function initSetting();

	public function __construct(Application $app,$flag = 'http')
	{
		$this->flag = $flag;
		$this->app = $app;
		// 初始化swoole配置
		$this->initSetting();
		// 创建服务
		$this->createServer();
		// 设置回调函数
		$this->initEvent();
		// 设置swoole的回调事件
		$this->setSwooleEvent();
	}
	/**
	* 启动服务
	* 
	*/
	public function start()
	{
		if (empty($this->swooleServer)) {
			// 应该 return error;
			return "error";
		}
		// 设置swoole的配置
		$this->swooleServer->set($this->config);
		if (app('config')->get('server.http.tcpable')) {
			new Rpc($this->swooleServer, app('config')->get('server.http.rpc'));
		}
		// 启动swoole服务
		$this->swooleServer->start();
	}
	/**
	* 设置swoole的回调事件
	* 
	*/
	protected function setSwooleEvent()
	{
		foreach ($this->event as $type => $events) {
			foreach ($events as $event => $func) {
				$this->swooleServer->on($event, [$this, $func]);
			}
		}
	}
	/**
	* 代码热加载
	* 
	*/
	public function watchEvent()
	{
		return function($event){
			$action = 'file:';
			switch ($event['mask']) {
				case IN_CREATE:
				$action = 'create';
				break;
				case IN_DELETE:
				$action = 'delete';
				break;
				case IN_MODIFY:
				$action = 'modify';
				break;
				case IN_MOVE:
				$action = 'move';
				break;
			}
			echo "worker reloaded by inotify : ".$action." ".$event['name']."\n";
			// 重启swoole进程
			$this->swooleServer->reload();
		};
	}
	// 注册回调函数
	public function onStart(SwooleServer $server)
	{
		$this->pidMap['masterPid'] = $server->master_pid;
		$this->pidMap['managerPid'] = $server->manager_pid;
		// 保存PID到文件里面
		$pidStr = \sprintf('%s,%s', $server->master_pid, $server->manager_pid);
		//\file_put_contents($this->app->getBasePath().$this->pidFile, $pidStr);
		\file_put_contents(app()->getBasePath().$this->pidFile, $pidStr);
		if ($this->watchFile) {
			$this->inotify = new Inotify($this->app->getBasePath(), $this->watchEvent());
			$this->inotify->start();
		}
		// 设置启动事件
		$this->app->make('event')->trigger('start', [$this, $server]);
	}
	public function onManagerStart(SwooleServer $server)
	{
	}
	public function onManagerStop(SwooleServer $server)
	{
	}
	public function onShutdown(SwooleServer $server)
	{
	}
	public function onWorkerStart(SwooleServer $server, int $worker_id)
	{
		$this->pidMap['workerPids'] = [
			'id' => $worker_id,
			'pid' => $server->worker_id
		];
		$this->redis = new Redis;
		$this->redis->pconnect($this->app->make('config')->get('database.redis.host'), $this->app->make('config')-
			>get('database.redis.port'));
	}
	/**
	* 
	* @return Redis
	*/
	public function getRedis()
	{
		return $this->redis;
	}
	public function setWatchFile($watch)
	{
		$this->watchFile = $watch;
		return $this;
	}
	public function onWorkerStop(SwooleServer $server, int $worker_id)
	{
	}
	public function onWorkerError(SwooleServer $server, int $workerId, int $workerPid, int $exitCode, int $signal)
	{
	}
	// get | set 方法
	public function getServerType()
	{
		return $this->serverType;
	}
	public function setServerType($serverType)
	{
		$this->serverType = $serverType;
		return $this;
	}
	public function getPort(): int
	{
		return $this->port;
	}
	public function setPort($port)
	{
		$this->port = $port;
		return $this;
	}
	public function getHost(): string
	{
		return $this->host;
	}
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}
	public function getEvent(): array
	{
		return $this->swooleEvent;
	}
	public function setEvent($type, $event)
	{
		// 暂时不支持直接设置系统的回调事件
		if ($type == "server") {
			return $this;
		}
		$this->event[$type] = $event;
		return $this;
	}
	public function getConfig(): array
	{
		return $this->config;
	}
	public function setConfig($config)
	{
		$this->config = array_map($this->config, $config);
		return $this;
	}
}
?>