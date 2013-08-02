<?php 
require 'vendor/autoload.php';
require 'config.php';
require 'routes.php';
use app\core\JSONRender;
use app\core\Helper;
use app\model\DataSource;

//autoload function
$autoload = function ($className){
	$array = explode('\\', $className);
	$fileName = array_pop($array).'.php';
	$dir = implode('/', $array);
	$inc = $dir.'/'.$fileName;
	include $inc;
};

spl_autoload_register($autoload);
$slim = new \Slim\Slim ();
$render = new JSONRender($slim);
DataSource::createInstance($config); //create instance

//map each route with an anonymous function that binds the controller methods
//in the slim framework.
foreach($routes as $route){
	$request = $slim->request();
	$handler = $route['handler'];
	$controller = new $route['controller'];
	$path = $route['path'];
	
	//anonymous function
	$func = function () use ($controller,$handler,$path,$request,$render) {
		//create named parameters according the routes.php file
		$arguments = func_get_args();
		$paramNames = Helper::getParamNames($path);
		//ensure that both array have the same length that is greater than 0
		if(count($paramNames)==count($arguments) && count($paramNames)!=0){
			$params = array_combine($paramNames,$arguments);
		} else {
			$params = $arguments;
		}
		
		//call the controller method and render the response
		$render->render($controller->$handler($params));
	};
	// map route with a handler
	$map = $slim->map($route['path'],$func);
	if(is_array($route['method'])){	
		foreach($route['method'] as $m){
			$map->via($m);
		}
	} else {
		$map->via($route['method']);
	}
}

$slim->run ();
?>