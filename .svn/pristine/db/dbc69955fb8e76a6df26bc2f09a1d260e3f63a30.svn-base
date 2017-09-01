<?php

class UtopiaErrors extends Exception {
	private $logPath;
	protected $class;
	protected $method;
	protected $line;
	

	public function __construct($class,$method,$line){
		$this->class = $class;
		$this->method = $method;				
		$this->line = $line;
		$this->logPath = 'logs/application.log';
	}	
		
	public function catchError(){
		$stamp = date('d/m/Y');
		$Entry = 'ERROR '.$stamp.' Occured on '.$this->class.' in method'.$this->method." on line ".$this->line." with message ".$this->getMessage()."\n End ERROR\n";
		$logged = file_put_contents($this->logPath,$Entry,FILE_APPEND);
	}		
	
}
?>