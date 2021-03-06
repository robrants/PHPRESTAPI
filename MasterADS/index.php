<?php
	namespace MasterADS;
	/*
		loadup the needed dependencies from the global config file passing through the known class repositories.
	
	*/
	include_once('../vendor/autoload.php');
	include_once('config/global.php');
	$classes = $globals['class'];
	function loadclass($class){		
		foreach($class as $c){			
			$source = array('../Common/'.$c.'.php','data/'.$c.'.php','control/'.$c.'.php');			
			foreach($source as $s){				
				if(file_exists($s)){
					//echo $s."<br>";
					require_once($s);
				}
			}
		}
	}
	
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
		//print_r($parms);
	} else{
		$class = $API[0];
		$verb = $API[1];
	}
	if($myclass = new $class){
		if(method_exists($myclass,$verb)){
			if(isset($parms)){
				if(isset($input)) $results = json_encode($myclass->$verb($input,$parms)); //Always put posted data first
				else $results = json_encode($myclass->$verb($parms));
			}else{
				if(isset($input)) $results = json_encode($myclass->$verb($input));
				else $results = json_encode($myclass->$verb());
			}
			//header('Content-Type: text/json');
			echo $results;
		}else
			throw Exception('No Class By that Name',405);
	}
?>