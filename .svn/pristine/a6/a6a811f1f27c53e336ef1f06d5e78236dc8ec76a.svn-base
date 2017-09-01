<?php
//namespace Reports\control;
//use Reports\data\custCountFootprint as data;

class custCountControl{
	protected $custData;
	
	public function __construct(){
		$this->custData = new custCountFootprint();
	}
	
	public function getCustCountByFootprint(){
		$this->custData->getData();
		$response = $this->custData->formatData();
		return($response);
	}
}
?>