<?php
namespace SwoStar\Container;
use Closure;
use Exception;
class Container
{
	// 0. 单例
	protected static $instance;
	// 1. 需要容器
	protected $bindings = [];
	protected $instances = [];
	// 2. 需要注册方法
	public function bind($abstract, $object)
	{
		// 标识要绑定
		// 1. 就是一个对象
		// 2. 闭包的方式
		// 3. 类对象的字符串 (类的地址)
		$this->bindings[$abstract] = $object;
	}
	// 3. 需要解析
	public function make($abstract, $parameters = [])
	{
		if (isset($this->instances[$abstract])) {
			return $this->instances[$abstract];
		}
		if (!$this->has($abstract)) {
			// 如果不存在自行
			// 选择返回, 可以抛出一个异常
			throw new Exception('没有找到这个容器对象'.$abstract, 500);
		}
		$object = $this->bindings[$abstract];
		// 在这个容器中是否存在
		// 闭包的方式
		if ($object instanceof Closure) {
			return $object();
		}
		// 类对象的字符串 (类的地址),在解析成功之后就会存在共享不再重复创建，同时返回实例
		return $this->instances[$abstract] = (is_object($object)) ? $object : new $object(...$parameters) ;
	}
	// 判断是否在容器中
	// 1. 容器很多便于扩展
	// 2. 可能在其他场景中会用到
	public function has($abstract)
	{
		return isset($this->bindings[$abstract]);
	}
	// 单例创建
	public static function getInstance()
	{
		if (is_null(static::$instance)) {
			static::$instance = new static;
		}
		return static::$instance;
	}
	public static function setInstance($container = null)
	{
		return static::$instance = $container;
	}
}
?>