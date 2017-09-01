<?php
	class SwitchDiscover extends myOracle{
		private $remotePorts = array();
		private $switchid;
		private $remoteSwitches = array();		
		public function __construct(){
			parent::__construct();
		}
		
		private function parseRemotePorts(){
			$SW = new buildSwitch();
			$switchObj = $SW->buildSwitch($this->switchid);
			$this->remotePorts = $switchObj->ports;
			
			foreach($this->remotePorts as $port){				
				$rec = array();
				if($port->remoteSwitchId != ''){
				$s = explode('"',$port->remoteSwitchId);				
				$rec['switch'] = $s[1];				
				$rec['ip'] = $port->remoteIP;				
				array_push($this->remoteSwitches,$rec);
				}
			}
			return true;
		}
		
		private function checkSwitch(){
			//echo count($this->remoteSwitches).' remoteSwitches found';
			foreach($this->remoteSwitches as $switch){
				$sql = "select count(1) from blackr.masterads where network_name = '".trim($switch['switch'])."'";
				$r = $this->runQuery($sql);
				$row = oci_fetch_row($r);				
				if($row[0] == 0){					
					$insert = "insert into blackr.masterads (switch_id,network_name,ip_address) values(blackr.seq_masterads.nextval,'".$switch['switch']."','".$switch['ip']."')";
					$r = $this->runInsert($insert);
					if(!$r) return -1;
					else continue;
				}
			}
			
			return 1;
		}
		
		public function pullCore(){
			$sql = "select switch_id from blackr.masterads where status = 'Active' and (network_name like 'RCS%' or network_name like 'DCS%')";
			$r = $this->runQuery($sql);
			while($row = oci_fetch_row($r)){
				$this->switchid = $row[0];				
				if($this->parseRemotePorts()){
					if($this->checkSwitch()){continue;}
					else return -1;
				}
				else return -1;
				
			}
			return 1;
		}								
	}
?>