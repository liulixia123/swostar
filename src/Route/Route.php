<?php
namespace SwoStar\Route;
use SwoStar\Console\Input;
class Route
{
	protected static $instance;
	/**
	* 用于记录路由的集合文件
	* @var array
	*/
	protected $routeMap = [];
	// 路由本质实现是会有一个容器在存储解析之后的路由
	protected $routes = [];
	// 定义了访问的类型
	protected $verbs = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
	// 请求类型
	protected $method;
	// 标识
	protected $flag;
	protected function __construct()
	{
		$this->routeMap = [
			'Http' => app()->getBasePath()."/route/http.php",
			'WebSocket' => app()->getBasePath()."/route/web_socket.php"
		];
	}
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new static;
		}
		return self::$instance;
	}
	public function get($uri, $action)
	{
		$this->addRoute(['GET'], $uri, $action);
	}
	public function post($uri, $action)
	{
		$this->addRoute(['POST'], $uri, $action);
	}
	public function any($uri, $action)
	{
		$this->addRoute($this->$verbs, $uri, $action);
	}
	public function addRoute($methods, $uri, $action)
	{
		foreach ($methods as $method ) {
			$this->routes[$this->flag][$method][$uri] = $action;
		}
		return $this;
	}
	/**
	* 用于路由校验
	* 
	*/
	public function match($path)
	{
		$action = null;
		foreach ($this->routes[$this->flag][$this->method] as $uri => $value) {
			$uri = ($uri && substr($uri,0,1)!='/') ? "/".$uri : $uri;
			if ($path === $uri) {
				$action = $value;
				break;
			}
		}
		if (!empty($action)) {
			return $this->runAction($action, $param);
		}
		Input::info('没有找到方法');
		return "404";
		// 失败没有找到路由
	}
	/**
	* 运行路由的方法
	*/
	private function runAction($action)
	{
		// 跳过参数解析
		if ($action instanceof \Closure) {
			// 如果是闭包就执行
			return $action();
		} else {
			$namespace = "\App\\".$this->flag."\Controller\\";
			// 控制器的方法
			$string = explode("@", $action);
			$controller = $namespace.$string[0];
			$class = new $controller();
			return $class->{$string[1]}();
		}
	}
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}
	public function registerRoute()
	{
		foreach ($this->routeMap as $key => $path) {
			$this->flag = $key;
			require_once $path;
		}
		return $this;
	}
	public function getRoutes()
	{
		return $this->routes;
	}
	public function setRouteMap($map)
	{
		$this->routeMap = $map;
	}
	public function getRouteMap()
	{
		return $this->routeMap;
	}
	public function wsController($uri, $controller)
	{
		$actions = [
			'open',
			'message',
			'close'
		];
		foreach ($actions as $key => $action) {
			$this->addRoute([$action], $uri, $controller."@".$action);
		}
	}
	public function setFlag($flag)
	{
		$this->flag = $flag;
		return $this;
	}
}
?>