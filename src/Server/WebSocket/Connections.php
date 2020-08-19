<?php
namespace SwoStar\Server\WebSocket;
class Connections
{
	private static $connecitons = [];
	/**
	* 初始化
	* 
	* @param int $fd 连接fd
	* @param string $path 地址
	*/
	public static function init($fd, $path){
		self::$connecitons[$fd]['path'] = $path;
	}
	/**
	* 获取连接所对应的path信息
	* 
	* @param int $fd 连接fd
	* @return string
	*/
	public static function get($fd = null){
		if($fd == null){
			return null;
		}
		return self::$connecitons[$fd] ?? null;
	}
	/**
	* 删除方法
	* 
	* @param int $fd 连接fd
	*/
	public static function del($fd = null){
		if($fd == null){
			return false;
		}
		if(isset(self::$connecitons[$fd])){
			unset(self::$connecitons[$fd]);
			return true;
		}
		return false;
	}
}
?>