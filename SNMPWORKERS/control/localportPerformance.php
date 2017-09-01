<?php
class localportPerformance extends snmpData{
	private $switch_id;
	private $switchData = array();
	private $oids = array();
	private $switchConn = array();
	private $SNMP;
	private $parsedData = array();
	public function __construct(){
		parent::__construct();
	}
	
	private function loadOid($descr){		
		$this->oids = $this->getoids($this->switchConn['model'],$descr.'%');
	}
	
	
	private function parseData($key){
		foreach($this->switchData as $keys => $data){
			error_log('KEY is: '.$key,0);
			
			//$keys = key($data);
			$index = explode('.',$keys);
			$ifindex = $index[1];
			error_log('IFINDEX: '.$ifindex,0);
			if($key == 'STATE'){
				$val = $data;
				error_log('Value is: '.$val,0);
			}else{
				$index = explode(':',$data);
				$val = trim($index[1]);
				error_log('Value is: '.$val,0);
			}
			$tmp = array('ifindex'=>$ifindex,'key'=>$key,'val'=>$val);
			array_push($this->parsedData,$tmp);
		}
	}
	
	public function pullPerformacne($switch){
		if(is_array($switch)){
			$switchId = $switch[0];
			$this->switch_id = $switchId;
		}else {
			$switchId = $switch;
			$this->switch_id = $switchId;
		}
		$this->switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->SNMP = new SNMPDriver($this->switchConn['ip'],$this->switchConn['community']);
		$this->loadOid('SPEED%');
		if(!$this->switchData = $this->SNMP->snmpGetter($this->oids[0]['oid'])){
			error_log('oid is '.$this->oids['oid'],0);
			return $this->oids[0];
		}
		error_log('Speed Oid Run',0);
		$this->parseData('Speed');
		$this->loadOid('MTU%');
		if(!$this->switchData = $this->SNMP->snmpGetter($this->oids[0]['oid'])){
			return -1;
		}
		error_log('MTU Oid Run',0);
		$this->parseData('MTU');
		$this->loadOid('STATE CHANGE%');
		if(!$this->switchData = $this->SNMP->snmpGetter($this->oids[0]['oid'])){
			return -1;
		}
		error_log('STATE Oid Run',0);
		$this->parseData('STATE');
		
		return $this->parsedData;
	}	
}