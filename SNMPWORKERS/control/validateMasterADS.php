<?php
	class validateMasterADS{
		private $liveSwitch;
		private $Masterads;
		
		private function pullMasterADS($s){
			$ADS = new masterADSData();
			$this->Masterads = $ADS->pullChasies($s);
		}
		
		private function getSwitch($s){
			$SW = new buildSwitch();
			if($this->liveSwitch = $SW->buildSwitch($s)){
				error_log('Switch Built!',0);
				return 1;
			}else {
				error_log('Failed To build Switch',0); 
				return -1;
			}
		}
		
		private function validate(){
			$validator = new snmpCrud($this->liveSwitch,'SNMPWORKERS');			
		}
		
		
		public function crawlNetwork($switch_id){
			if(is_array($switch_id)){
				$switch = $switch_id[0];
			}else $switch = $switch_id;
			$this->pullMasterADS($switch);
			foreach($this->Masterads as $s){
				$v = $this->getSwitch($s['switch_id']);
				if($v == 1) $this->validate();
			}
			return 1;
		}
	}