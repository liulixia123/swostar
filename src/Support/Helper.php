<?php
use SwoStar\Foundation\Application;
if (! function_exists('app')) {
	/**
	* 获取应用初始化程序
	* 
	* @return Application
	*/
	function app($abstract = null)
	{
		if (is_null($abstract)) {
		return Application::getInstance();
		}
		return Application::getInstance()->make($abstract);
	}
}