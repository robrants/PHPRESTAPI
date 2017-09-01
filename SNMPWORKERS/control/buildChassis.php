<?php
	class buildChassis extends snmpData{
		public function __construct(){
			parent::__construct();
		}
		private $chassis;
		private $devtype;
		private $swData;
		
		private function parseMakeFirm(){
			/*
			
			This function parses the firmware based on device type
			device type 1 the following steps
			1) pull the value for the array element and break it at the whitespace into an array data
			2) next assign the property of firmware on array object chassis to the second element (position 1) of data
			3) a tmp array is created from $data starting at postion 3 and consuming 3 elements (3,4,and 5)
			4) merge the tmp array into a single string delimited by whitespace
			5) assign this value to chassis make property
			
			device type 2 the following steps are executed
			1) follow the same first step from device 1
			2) pull out element positions 1 and two and merge them with whitespace and assign to chassis make property
			3) assign element/position 3 to firmware property
			
			*/
			
			if($this->devtype == 1){
				foreach($this->swData as $key => $val){
					$data = explode(' ',$val);
					$this->chassis->firmware = $data[1];
					$tmp = array_slice($data,3,3);
					$this->chassis->make = implode(' ',$tmp);
				}
			}elseif($this->devtype == 2){
				foreach($this->swData as $key => $val){
					$data = explode(' ',$val);
					$mm = array_slice($data,1,2);
					$this->chassis->make = implode(' ',$mm);
					$this->chassis->firmware = $data[3];
					
				}
			}			
		}
		
		public function buildChassis($switch){
			
			/*
			
			 See BuildPorts class file for build documentation this class uses the same logic but pulls descriptions for CHASSIS
			
			*/
			
			$this->chassis = new chassisObj();
			if(is_array($switch)){
				$switchId = $switch[0];
			}else $switchId = $switch;
			$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
			$this->devtype = $this->modelidToDevType($switchConn['model']);
			$this->chassis->switch_id = $switchId;
			$this->chassis->local_ip = $switchConn['ip'];	
			$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
			$this->oids = $this->getoids($switchConn['model'],'CHASSIS%'); // pull the needed oids for the make/model of this switch
			foreach($this->oids as $o){
				
				if(!$this->swData = $this->SNMP->snmpGetter($o['oid'])){
					return -1;
				}
			}
			$this->parseMakeFirm();
			return $this->chassis;
		}
		
	}
?>