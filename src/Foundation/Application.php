<?php
namespace SwoStar\Foundation;
use SwoStar\Server\Http\HttpServer;
/**
* swostar的入口文件
*/
class Application
{
	public const SWOSTAR_WELCOME = "
	_____ _____ ___
	/ __/ ____ / __/ __/ /__ ___ __ __ __
	\__ \ | | /| / / / __ \ \__ \ /_ ___/ / _` | | \/ /
	__/ / | |/ |/ / / /_/ / __/ / / /_ | (_| | | _/
	/___/ |__/\__/ \____/ /___/ \___/ \___/\_| |__|
	";
	public function __construct()
	{
		echo self::SWOSTAR_WELCOME."\n";
	}
	public function run()
	{
		$httpServer = new HttpServer();
		$httpServer->start();
	}
}
?>