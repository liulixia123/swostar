<?php
namespace SwoStar\Event;
use SwoStar\Foundation\Application;
abstract class Listener
{
	protected $name = 'interface';
	/**
	* [protected description]
	* @var Application
	*/
	protected $app ;
	public abstract function handler();
	public function getName()
	{
		return $this->name;
	}
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
}
?>