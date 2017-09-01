<?php
//namespace control;
//use data\masterADSData as data;

class Crud{
	protected $ADSData;
	public function __construct(){
		$this->ADSData = new masterADSData();
	}
	public function addChassie($input,$flag){
		$table = 'BLACKR.MASTERADS';
		if($flag[0] != 0){
			$flag = ' where switch_id = '.$flag[0];
		} else $flag = 0;
		$response = $this->ADSData->putADS($input,$flag,$table);
		return $response;
	}
	public function addStack($input,$flag){
		$table = 'BLACKR.ADSSTACK';
		if($flag[0] != 0){
			$flag = ' where stackid = '.$flag[0];
		}else $flag = 0;		
		$response = $this->ADSData->putADS($input,$flag,$table);
		return $response;
	}
}

?>