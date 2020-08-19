<?php
require __DIR__."/../vendor/autoload.php";
use SwoStar\Index;
use App\App;
echo (new Index())->index()."\n";
echo (new App())->index();
?>