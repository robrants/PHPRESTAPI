<?php
class IFIndexWalker extends snmpData{	
	private $data;
	private $snmpArray = array();
	private $network = 'ut0p1a5nmp';
	
	public function __construct(){		
		parent::__construct();				
	}
	
	private function walkIP($switch){
		$ip = $switch['ip'];
		$this->snmpArray = array();
		$SNMP = new SNMP(SNMP::VERSION_2c,$ip,$this->network);
		$this->data = $SNMP->walk('1.3.6.1.2.1.2.2.1.2');
		//$this->data = snmp2_real_walk($ip,$this->network,'1.3.6.1.2.1.2.2.1.2');
		if($this->data == false){ return -1;}
		foreach($this->data as $key =>$val){			
			//echo $key.' -> '.$val.'<br>';
			$rec['switch_id'] = $switch['switch_id'];
			$tmp = array();
			$tmp = explode('.',$key);
			$rec['ifidxid'] = $tmp[1];			
			//echo $rec['ifidxid'].'<br>';
			$tmp = array();
			$tmp = explode(':',$val);
			$tmp2 = explode(',',$tmp[1]);
			if(count($tmp2) == 1){
				$rec['descr'] = $tmp[0];
			}elseif(count($tmp2) == 2){
				$rec['port'] = $tmp2[0];
				$rec['porttype'] = $tmp2[1];
			}else{
				$rec['port'] = $tmp2[0];
				$rec['porttype'] = $tmp2[1];
				$rec['descr'] = $tmp2[2];
			}
			
			
			/*foreach($rec as $key => $val){
				echo $key.' -> '.$val.'<br>';
			}*/
			array_push($this->snmpArray,$rec);
		}
		
		/*foreach($this->data as $key => $val){
			$rec = array();
			$rec['switch_id'] = $switch['switch_id'];
			$tmp = explode('.',$key);			
			$rec['ifidxid'] = $tmp[1];						
			$tmp = explode(':',$val);				
			$tmp = explode(',',$tmp[1]);
			if(count($tmp) === 1){
				$rec['port'] = 'NA';
				$rec['porttype'] = 'NA';
				$rec['descr'] = $tmp[0];
			}elseif(count($tmp) === 2){
				$rec['port'] = $tmp[0];
				$rec['porttype'] = $tmp[1];
				$rec['descr'] ='NA';
			}else{
				$rec['port'] = $tmp[0];
				$rec['porttype'] = $tmp[1];
				$rec['descr'] = $tmp[2];
			}*/
			
			return 1;
		}
	
	public function walkDevice(){
		$this->getADSifIndex();
		foreach($this->switches as $s){			
			$rt = $this->walkIP($s);
			if($rt == 1){
				$r = $this->writeIfIndex($this->snmpArray);
				//print_r($this->snmpArray);
				if($r != 1){exit;}
			}else continue;		
		}
	}
	
	public function walkSingleDevice($parms){
		$switch = array('switch_id' => $parms[0],'ip'=>$parms[1]);
		$rt = $this->walkIP($switch);
		foreach($this->snmpArray as $s){
			foreach($s as $key => $val){
				echo $key.' -> '.$val.'<br>';
			}
		}
	}
	
	
}

?>