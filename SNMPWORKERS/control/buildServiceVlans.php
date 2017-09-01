<?php
class buildServiceVlans extends snmpData{
	public function __construct(){
		parent::__construct();
	}
	private $Services = array();
	private $servicedata;
	private $SNMP;
	private $oids = array();
	
	private function parseVlans(){
		foreach($this->servicedata as $key => $val){
			$vlan = new serviceVlan();
			$tmp = explode('.',$key);
			$vlan->vlanTag = array_pop($tmp);
			$vlan->ifindex = array_pop($tmp);
			$vlan->serviceId = array_pop($tmp);
			array_push($this->Services,$vlan);
		}
	}
	
	public function buildServiceVlan($switch){
		//check parameter to see if it came in as part of API or as class method call in other interface
		if(is_array($switch)){
			$switchId = $switch[0];
		}else $switchId = $switch;
		//echo $switchId.'<br>';
		$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
		$oids = $this->getoids($switchConn['model'],'VLAN%'); // pull the needed oids for the make/model of this switch
		foreach($oids as $o){ //Ensure we run the OIDS in the correct order
			switch($o['descr']){
				case 'VLAN':
					$this->oids[0] = $o;
				break;				
			}
		}
		//echo 'Total oids to run: '.count($this->oids).'<br><br>';
		for($x=0; $x<count($this->oids); $x++){
			//echo $this->oids[$x]['oid'].'<br>';
			//$this->servicedata = $this->SNMP->snmpGetter('1.3.6.1.4.1.6527.3.1.2.4.3.2.1.1');
			if(!$this->servicedata = $this->SNMP->snmpGetter($this->oids[$x]['oid'])){
				return -1;
			}
			if($this->servicedata === false) return -1;
			switch($this->oids[$x]['descr']){
				case 'VLAN':
					//echo 'Parsing VLAN Services<br>';
					$this->parseVlans();
				break;				
			}
		}
		return $this->Services;
		/*echo 'Total VLAN Services for this Switch: '.count($this->Services).'<br>';
		foreach($this->Services as $l){			
			echo 'IfIndex: '.$l->ifindex.'<br>';
			echo 'Service ID: '.$l->serviceId.'<br>';
			echo 'VLAN Tag: '.$l->vlanTag.'<br>';			
			echo '<br><br>';
		}*/
	}
}

?>