<?php
class appLog{
	private $logpath = '/var/www/mu/Applications/';
	
	public function __construct($filepath){
		$this->logpath .= $filepath.'/logs/';
	}
	
	public function addentry($file,$class,$method,$error){
		$d = date('d/m/Y h:i:s a');
		$log = $this->logpath.$file;
		$message = $d.' -Error happened in '.$class.'-'.$method.' with error: '.$error."\n";
		error_log($method,3,$log);
	}
}
?>