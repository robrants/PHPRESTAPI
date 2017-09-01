<?php
	class buildSwitch extends snmpData{
		public function __construct(){
			parent::__construct();
		}
		private $switchObj;
		private $chassis;
		private $lags;		
		private $ports;
		private $vlansServices;		
		private $devtype;
		private $stack;
		private $swData = array();
		private $SNMP;
		private $oids = array();
		
		private function pullSwitchName($switchId){
			$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
			$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
			$oids = $this->getoids($switchConn['model'],'SWITCH%');
			$data;			
			foreach($oids as $oid){
				error_log($oid['oid'],0);
				if($data = $this->SNMP->snmpGetter($oid['oid'])){										
					foreach($data as $d){
						$name = explode(':',$d);						
					}				
					$this->switchObj->network_name = trim($name[1]);
				}else return -1;
			}
		}
		
		public function buildSwitch($switch){
			if(is_array($switch)){
			$switchId = $switch[0];		
			}else {
				$switchId = $switch;			
			}
			//Build out the ports,lags and vlan_services
			$cs = new buildChassis();
			$p = new buildPorts();
			$l = new buildLags();
			$s = new buildServiceVlans();
			$c = new buildChassisInfo();
			$st = new buildStack();
			$this->chassis = $cs->buildChassis($switchId);
			$this->ports = $p->BuildPorts($switchId,$this->chassis->firmware);
			$this->lags = $l->pullLag($switchId);
			$this->vlansServices = $s->buildServiceVlan($switchId);
			$this->stack = $st->buildChassis($switchId);
			$this->switchObj = new switchObj();
			$this->pullSwitchName($switchId);
			$this->switchObj->make = $this->chassis->make;
			$this->switchObj->local_ip = $this->chassis->local_ip;
			$this->switchObj->switch_id = $switchId;
			$this->switchObj->firmware = $this->chassis->firmware;
			$this->switchObj->ports = $this->ports;
			$this->switchObj->lags = $this->lags;
			$this->switchObj->vlan_services = $this->vlansServices;
			$this->switchObj->stack = $this->stack;
			//Pull Chassis make and firmware version
			return $this->switchObj;
		}
	}
?>