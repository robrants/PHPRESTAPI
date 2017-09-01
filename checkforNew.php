<?php
const SPATH = '/var/www/mu/Applications/';
require_once(SPATH.'vendor/autoload.php');
require_once('config/global.php');
function loadclass($class){		
	foreach($class as $c){			
		$source = array(SPATH.'DHCP/data/'.$c.'.php',SPATH.'Common/'.$c.'.php',SPATH.'SNMPWORKERS/data/'.$c.'.php',SPATH.'SNMPWORKERS/control/'.$c.'.php');			
		foreach($source as $s){
			if(file_exists($s)){
				//echo $s."<br>";
				require_once($s);
			}
		}
	}
}
loadclass($globals['class']);
$deploy = new SwitchDiscover();
$results = $deploy->pullCore();
return $results;