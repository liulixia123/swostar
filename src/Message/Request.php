<?php
namespace SwoStar\Message;
class Request
{
	protected $method;
	/**
	* 获取请求的类型
	* 
	*/
	public function getMethod()
	{
	return $this->method;
	}
	/**
	* 获取get传递的参数
	* 
	* @param string $name
	*/
	public function get($name = null)
	{
	}
	/**
	* 获取post传递的参数
	* 
	* @param string $name
	*/
	public function post($name = null)
	{
	}
}
?>