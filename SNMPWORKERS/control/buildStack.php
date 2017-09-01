<?php
class buildStack extends snmpData{
	public function __construct(){
		parent::__construct();
	}
	private $Stack = array();
	private $stackData;
	private $devtype;
	private $SNMP;
	private $oids = array();
	
	private function parsePart($stack){
		foreach($this->stackData as $key => $val){
			$tmp = explode(':',$val);
			if(count($tmp) == 2){
				return $tmp[1];
			}
		}
	}
	
	private function parseSerial($stack){		
		foreach($this->stackData as $key => $val){			
			$tmp = explode(':',$val);
			if(count($tmp) == 2){
				return $tmp[1];
			}
		}
	}
	
	private function parseMac($stack){
		foreach($this->stackData as $key =>$val){
			$tmp = explode(':',$val);
			return implode(':',explode(' ',ltrim($tmp[1])));
		}
	}
	
	private function parseLabel($stack){
		foreach($this->stackData as $key => $val){
			$tmp = explode(':',$val);
			return $tmp[1];
		}
	}
	
	public function buildChassis($switch){
		//check parameter to see if it came in as part of API or as class method call in other interface
		if(is_array($switch)){
			$switchId = $switch[0];
		}else $switchId = $switch;
		//echo $switchId.'<br>';
		$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->devtype = $this->modelidToDevType($switchConn['model']);
		//echo $this->devtype.'<br>';
		$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
		$oids = $this->getoids($switchConn['model'],'STACK%'); // pull the needed oids for the make/model of this switch
		foreach($oids as $o){ //Ensure we run the OIDS in the correct order
			switch($o['descr']){
				case 'STACK PARTNUM':
					$this->oids[0] = $o;
				break;
				case 'STACK SERIAL':
					$this->oids[1] = $o;
				break;
				case 'STACK MAC':
					$this->oids[2] = $o;
				break;
				case 'STACK LABEL':
					$this->oids[3] = $o;
				break;
			}
		}
		
		if($this->devtype == 1){ //7750 or 7450 switch;
			$h = new buildChassisInfo();
			$hardwareindex = $h->buildChassis($switchId);
			$x = 1;
			foreach($hardwareindex as $hindex){
				$stack = new stackObj();
				$stack->hrdIndex = $hindex->hardwareIndex;
				$stack->position = $x;
				foreach($this->oids as $o){
					$o['oid'] .='.'.$hindex->hardwareIndex;
					//echo $o['oid'].'<br>';
					$this->stackData = $this->SNMP->snmpGetter($o['oid']);
					switch($o['descr']){
						case 'STACK PARTNUM':					
							$stack->partnum = $this->parsePart($stack);
							//echo $stack->partnum.'<br>';
						break;
						case 'STACK SERIAL':
							$stack->serial = $this->parseSerial($stack);
							//echo $stack->serial.'<br>';
						break;
						case 'STACK MAC':
							$stack->mac = $this->parseMac($stack);
							//echo $stack->mac.'<br>';
						break;
						case 'STACK LABEL':
							$stack->label = $this->parseLabel($stack);
					}					
				}
				if($stack->mac == '00:00:00:00:00:00:'){
					continue;
				}else{
					array_push($this->Stack,$stack);
					$x++;	
				}
				
			}
		}elseif($this->devtype == 2){ //6400 series
			for($x=1; $x<=8; $x++){
				$stack = new stackObj();
				$stack->position = $x;
				foreach($this->oids as $o){
					$o['oid']  .= '.'.$x; //add the index to oid up to 8
					if(!$this->stackData = $this->SNMP->snmpGetter($o['oid'])){
						continue;
					}
					switch($o['descr']){
						case 'STACK PARTNUM':					
							$stack->partnum = $this->parsePart($stack);
						break;
						case 'STACK SERIAL':
							$stack->serial = $this->parseSerial($stack);
						break;
						case 'STACK MAC':
							$stack->mac = $this->parseMac($stack);
						break;
					}					
				}
				array_push($this->Stack,$stack);
			}					
		}
		
		return $this->Stack;
	}
}
?>