<?php
	//namespace control;	
	//use data\masterADSData as Data;
	class Reports extends masterADSData{
		public function __construct(){
			parent::__construct();
		}
		
		public function getAllAds($parms=''){
			if($parms===''){unset($parms);}
			if(isset($parms)){
				if(count($parms) == 1){
					return $this->pullChasies($parms[0]);
				}elseif(count($parms) == 2){
					return $this->pullChasies($parms[0],$parms[1]);
				}else return $this->pullChasies(0);
			}else return $this->pullChasies(0);					
		}
		
		public function exportADS($parms=''){
			if($parms === ''){unset($parms);}
			if(isset($parms)){
				$data = $this->exportChasies($parms[0]);				
			}else $data = $this->exportChasies('');
			$UTIL = new sysUtils();
			$header = array_keys($data[0]);
			$report = 'masterADS.csv';			
			return $UTIL->exportReport($report,$data,$header);
		}
		
		public function getStack($sid){
			return $this->pullStack($sid[0]);
		}
	}