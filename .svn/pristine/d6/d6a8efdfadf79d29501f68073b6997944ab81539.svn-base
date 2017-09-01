<?php
class MasterADS extends masterADSData{
	private $chassies 	= array();
	private $slots 		= array();
	protected $localports	= array();
	private $tree		= array();
	
	
	public function __construct(){
		parent::__construct();
		$this ->chassies = $this->pullChasies();
	}
	
	
	
	
	private function buildBranch($level=0){ //Not used at this time
		if($level == 0){
			//Default action for top level
			foreach($this->chassies as $c){
				$branch = new Branch();
				$branch->label = $c['network_name'];
				$branch->data = array('switch_id' 	=> $c['switch_id'],
								 'mm' 				=> $c['switchdesc'],
								 'ip' 				=> $c['ip'],
								 'adid' 			=> $c['address_id'],
								 'footprint_seq' 	=> $c['footprint_seq'],
								 'mngfootprint' 	=> $c['mngfootprint'],
								 'sdp' 				=> $c['SDP'],
								 'lag' 				=> $c['lag'],
								 'ups1' 			=> $c['ups1_ip'],
								 'ups2' 			=> $c['ups2_ip'],
								 'perle' 			=> $c['perle_ip'],
								 'oob_ap_ip' 		=> $c['oob_ap_ip']);
				array_push($this->tree,$branch);
				unset($branch);
			}
		}elseif($level == 1){
			
		}
	}
	
	
	//Pull All Chassies from the Master ADS table or for a single given chassie Works with The MasterADSData class
	
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
	
	
	//Same Functionality as getAllAds except this pushes the results to a CSV file
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
		
	//Interface for Pulling a given Chassies Slots/Hardware and LocalPorts
	public function pullSlots($switch){		
		if(is_array($switch)){
			$switch = $switch[0];
			//$serial = $parms['serial'];
		}
		$this->slots = $this->pullSwitchSlots($switch);
		
		$x = 0;
		foreach($this->slots as $s){
			$this->localports = $this->pullLocalPorts($switch,$s['serial']);
			$this->slots[$x]['localports'] = $this->localports;
			//unset($this->localports);
			$this->localports = array();
			$x++;
		}
		return $this->slots;
	}
	//Depricated function to be removed Once Acceptance of new Data Structures are completed
	public function getStack($sid){
		return $this->pullStack($sid[0]);
	}
	
	//Not used yet
	public function GetBranch($lvl=0,$input=0){
		if(is_array($lvl)){
			$lvl = $lvl[0];
		}
		if($lvl == 1 and is_array($input)){
			$this->slots = $this->pullSwitchSlots($input['switch_id']);
			foreach($this->localports['serial'] as $s){
				$this->localports = $this->pullLocalPorts($input['serial'],$input['switch_id']);
				$this->slots['serial']['localports'] = $this->localports;
				
			}
		}								
		$this->buildBranch($lvl);
		return $this->tree;
	}
}
?>