<?php
namespace SwoStar\Foundation;
use SwoStar\Console\Input;
use SwoStar\Container\Container;
use SwoStar\Route\Route;
use SwoStar\Server\Http\HttpServer;
use SwoStar\Server\WebSocket\WebSocketServer;
/**
* swostar的入口文件
*/
class Application extends Container
{
	public const SWOSTAR_WELCOME = "
	_____ _____ ___
	/ __/ ____ / __/ __/ /__ ___ __ __ __
	\__ \ | | /| / / / __ \ \__ \ /_ ___/ / _` | | \/ /
	__/ / | |/ |/ / / /_/ / __/ / / /_ | (_| | | _/
	/___/ |__/\__/ \____/ /___/ \___/ \___/\_| |__|
	";
	protected $basePath = "";
	public function __construct()
	{
		//echo self::SWOSTAR_WELCOME."\n";
		// 设置项目系统路径
		$this->setBasePath($path);
		// 注册系统核心实例对象
		$this->registerBaseBindings();
		echo self::SWOSTAR_WELCOME."\n";
		// 初始化系统
		$this->init();
		Input::info(self::SWOSTAR_WELCOME, "welcome");
	}
	public function run($arg)
	{
		$server = null;
		switch ($arg[1]) {
			case 'http:start':
			$server = new HttpServer($this);
			break;
			case 'ws:start':
			$server = new WebSocketServer($this);
			break;
		}
		$server->setWatchFile(true);
		$server->start();
	}
	public function setBasePath($basePath)
	{
		$this->basePath = rtrim($basePath, '\/');
	}
	public function getBasePath()
	{
		return $this->basePath;
	}
	/**
	* 把系统核心容器对象绑定到容器中
	* 
	*/
	protected function registerBaseBindings()
	{
		// 设置单列
		self::setInstance($this);
		$bind = [
			// 分开绑定
			"httpRequest" => (new \SwoStar\Message\Http\Request()),
			"httpResponse" => (new \SwoStar\Message\Http\Response()),
			"config" => (new \SwoStar\Config\Config()),
		];
		foreach ($bind as $key => $value) {
			$this->bind($key, $value);
		}
	}
	protected function init()
	{
	// 注册路由
	// $this->bind('route', Route::getInstance()->registerRoute());
	// 测试
	dd(Route::getInstance()->registerRoute()->getRoutes());
	}
}
?>