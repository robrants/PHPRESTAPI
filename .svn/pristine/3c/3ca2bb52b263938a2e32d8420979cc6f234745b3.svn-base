<?php
class buildChassisInfo extends snmpData{
	public function __construct(){
		parent::__construct();
	}
	private $Chassis = array();
	private $chassisData;
	private $SNMP;
	private $oids = array();
	
	
	/*
	
	This class file is used to pull hardware indexes from 7750s and 7450s
	
	*/
	
	private function parseChassis(){
		/*
		
		 This function pulls the oid array and does the following
		 1) take tke key off the chassisData element and breaking it into an array tmp breaking on '.'
		 2) pulls the last value off tmp array and assigning it to chassis hardwareIndex property
		 3) reassign the tmp array to the break out of the $val variable broken on ':'
		 4) pulls off the last element of the array and assign it to chassis descr property
		
		*/
		foreach($this->chassisData as $key => $val){
			$chassis = new chassisInfo();
			$tmp = explode('.',$key);
			$chassis->hardwareIndex = array_pop($tmp);
			$tmp = explode(':',$val);
			$chassis->descr = array_pop($tmp);
			array_push($this->Chassis,$chassis);
		}		
	}
	
	public function buildChassis($switch){
		
		
		/*
			
			 See BuildPorts class file for build documentation this class uses the same logic but pulls descriptions for HARWARE
			
			*/
		
		
		//check parameter to see if it came in as part of API or as class method call in other interface
		if(is_array($switch)){
			$switchId = $switch[0];
		}else $switchId = $switch;
		//echo $switchId.'<br>';
		$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
		$oids = $this->getoids($switchConn['model'],'HARDWARE%'); // pull the needed oids for the make/model of this switch
		foreach($oids as $o){ //Ensure we run the OIDS in the correct order
			switch($o['descr']){
				case 'HARDWARE':
					$this->oids[0] = $o;
				break;				
			}
		}
		for($x=0; $x<count($this->oids); $x++){
			if(!$this->chassisData = $this->SNMP->snmpGetter($this->oids[$x]['oid'])){ 
				return -1;
			}
			
			if($this->chassisData === false) return -1;
			switch($this->oids[$x]['descr']){
				case 'HARDWARE':
					//echo 'Parsing IFIndex and Lag Nums<br>';
					$this->parseChassis();
				break;				
			}
		}
		
		return $this->Chassis;
	}
}
?>