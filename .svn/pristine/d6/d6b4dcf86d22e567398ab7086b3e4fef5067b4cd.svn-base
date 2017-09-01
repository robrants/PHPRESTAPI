<?php
class porterrors extends snmpData{
	private $switchData = array();
	private $switch_id;
	private $parsedData = array();
	private $PortErrors = array();
	private $oids = array();
	private $SNMP;
	public function __construct(){
		parent::__construct();
	}
	
	private function parseData($key){
		foreach($this->switchData as $keys => $data){
			error_log('Key is: '.$key,0);
			$index = explode('.',$keys);
			$ifindex = array_pop($index);
			error_log('Ifindex is: '.$ifindex,0);
			$index = explode(':',$data);
			$error = trim($index[1]);
			error_log('Value is: '.$error,0);
			$porterror = array('ifindex'=>$ifindex,'key'=>$key,'error'=>$error);
			array_push($this->parsedData,$porterror);
		}
	}
	
	private function ConsoladateData(){
		$errorin = $this->parsedData['Errors In'];
		$inDiscards = $this->parsedData['Errors DisCard In'];
		$errorout = $this->parsedData['Errors Out'];
		$outDiscards = $this->parsedData['Errors DisCard Out'];
		foreach($errorin as $data){
			$tmp = array('switch_id' => $this->switch_id,'ifindex' => $data['ifindex'],'ein' => $data['error']);
			foreach($inDiscards as $d2){
				if($d2['ifindex'] == $tmp['ifindex']){
					$tmp['din'] = $d2['error'];
					break;
				}
			}
			foreach($errorout as $d3){
				if($d3['ifindex'] == $tmp['ifindex']){
					$tmp['eout'] = $d3['error'];
					break;
				}
			}
			foreach($outDiscards as $d4){
				if($d4['ifindex'] == $tmp['ifindex']){
					$tmp['dout'] = $d4['error'];
					break;
				}
			}
			
			array_push($this->PortErrors,$tmp);
		}
	}
	
	public function gatherErrors($switch){
		if(is_array($switch)){
			$switchId = $switch[0];
			$this->switch_id = $switchId;
		}else {
			$switchId = $switch;
			$this->switch_id = $switchId;
		}
		$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
		$this->oids = $this->getoids($switchConn['model'],'ERRORS%'); // pull the needed oids for the make/model of this switch
		foreach($this->oids as $o){
			if(!$this->switchData = $this->SNMP->snmpGetter($o['oid'])){
				return -1;
			}
			
			if($this->switchData === false) return -1;
			
			switch($o['descr']){
				case 'ERRORS IN':
					//echo 'Parsing Ports<br>';
					$this->parseData('Errors In');
					//echo 'Total Ports: '.count($this->ports).'<br>';
				break;
				case 'ERRORS DISCARDS IN':
					//echo 'Parsing Remote Index<br>';
					$this->parseData('Errors DisCard In');
				break;
				case 'ERRORS OUT':
					//echo 'Parsing Remote IPs<br>';
					$this->parseData('Errors Out');
				break;
				case 'ERRORS DISCARD OUT':
					//echo 'Parsing Remote Names<br>';
					$this->parseData('Errors Discard Out');
				break;				
			}
		}
		
		//$this->ConsoladateData();
		//return $this->PortErrors;
		return $this->parsedData;
	}
}




?>