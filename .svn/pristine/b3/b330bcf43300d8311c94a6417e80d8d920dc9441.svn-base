<?php
	namespace Reports;
	/*
		loadup the needed dependencies from the global config file passing through the known class repositories.
	
	*/	
	const SPATH = '/var/www/mu/Applications/Reports/';
	require_once(SPATH.'../vendor/autoload.php');
	require_once('config/global.php');
	function loadclass($class){		
		foreach($class as $c){			
			$source = array(SPATH.'../DHCP/data/'.$c.'.php',SPATH.'../Common/'.$c.'.php',SPATH.'templates/'.$c.'.php');			
			foreach($source as $s){
				//echo $s.'<br>';
				if(file_exists($s)){
					//echo $s."<br>";
					require_once($s);
				}
			}
		}
	}

	$classes = $globals['class'];
	loadclass($classes);

	/**
	* Pull the Request variable validate the application and verb being used
	Request should be /Application/controller/verb/parms....
	
	*/

	$request = $_GET['request'];
	$API = explode('/',rtrim($request,'/'));
	$API = array_slice($API,3);
	$method = $_SERVER['REQUEST_METHOD'];
	if($method == 'POST') $input = file_get_contents("php://input");
	//print_r($API);

	if(count($API) > 2){
		$class = $API[0];
		$verb = $API[1];
		$parms = array_slice($API,2);		
	} else{
		$class = $API[0];
		$verb = $API[1];
	}
	if($myclass = new $class){
		if(method_exists($myclass,$verb)){
			if(isset($parms)){
				if(isset($input)) $results = json_encode($myclass->$verb($parms,$input));
				else $results = json_encode($myclass->$verb($parms));
			}else{
				if(isset($input)) $results = json_encode($myclass->$verb($input));
				else $results = json_encode($myclass->$verb());
			}
			echo $results;
		}else
			throw Exception('No Class By that Name',405);
	}

?>