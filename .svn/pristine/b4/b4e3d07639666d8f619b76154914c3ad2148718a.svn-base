<?php

class buildPorts extends snmpData{
	public function __construct(){
		parent::__construct();
	}	
	private $ports = array();
	private $remoteIF = array(); //Store results from call for the remoteIF data
	private $remoteIPs = array(); // Store results from call for remote IP
	private $remoteNetwork = array(); //Store results from call for remote network
	private $remoteSDP = array();
	private $remoteServiceID = array();
	private $SNMP;
	private $oids = array();
	private $switchData;
	
	private function parsePorts(){
		/*
		
		This function takes an associate array and does the following
		
		1) takes the key of each element and breaks it on the '.' into an array  and assigns the second element to the IFindex of the local port in the port object
		2) resets the tmp array used in step one (this likely can be refactored into a collection chain later) then explodes the val of the element on the ':' into an array.
		3) using a second tmp array is used to take the second element of the tmp array and explode it on the ','
		4) varify if the tmp2 array has one element then assign tmp[1] to the portDescr of the new port object
		5) if step for is false see if the count of tmp2 is 2 and assign the first element to the port and second element to portType of the port Object
		6) if steps 4 and 5 are false then assign tmp2[0] to port, tmp2[1] to portType and tmp2[2] to portDescr
		7) Push the port object to the private port array of the object
		8) Unset the local port variable as a cleanup step.
		
		This entire foreach loop likely could be refactored to a collection which will remove the need of the two tmp arrays and allow for a smaller and cleaner codeset.
		
		*/
		
		foreach($this->switchData as $key => $val){
			$port = new portsObj();
			$tmp = array();
			$tmp2 = array();
			$tmp = explode('.',$key);
			//echo $tmp[1].'<br>';
			$port->portIfindex = $tmp[1];
			$tmp = array();
			$tmp = explode(':',$val);
			$tmp2 = explode(',',$tmp[1]);
			if(count($tmp2) == 1){
				$port->portDescr = $tmp[1];
			}elseif(count($tmp2) == 2){
				$port->port = $tmp2[0];
				$port->portType = $tmp2[1];
			}else{
				$port->port = $tmp2[0];
				$port->portType = $tmp2[1];
				$port->portDescr = $tmp2[2];
			}			
			array_push($this->ports,$port);			
		}
		unset($port);
	}
	
	private function parse6400Port(){
		foreach($this->switchData as $key => $val){
			$port = new portsObj();
			$tmp = explode('.',$key);
			$port->portIfindex = array_pop($tmp);
			array_push($this->ports,$port);
		}
		
	}
	
	private function parseRemoteIfInx(){
		/*
		This function take the new switchData and parses it into the the remoteIFindex and added to the the private variable for later use
		Two arrays are used in this function the rec array holds the final values to be pushed to the Private remoteIF array the tmp array is used in parsing the data.
		1) The Key of the record is exploded on the '.' then the 9th element in the new array is assigned to the rec['lldpuid'] element and the 10th element is assigned to the rec['localIF'] element of the rec array
		2) the value of the switchData record is broken at the ':' and the second element is added to the rec['remoteIF'] element.
		3) the rec array is pushed into the private remoteIF array
		
		This is a function that is going to be refactored into a generic class file for parsing as this same data structure is found in many SNMP return values.
		
		*/
		foreach($this->switchData as $key => $val){
			$tmp = array();
			$rec = array();
			$tmp = explode('.',$key);
			$rec['lldpuid'] = $tmp[10];
			$rec['localIF'] = $tmp[11];
			$tmp = array();
			$tmp = explode(':',$val);
			$rec['remoteIF'] = $tmp[1];
			array_push($this->remoteIF,$rec);
		}
	}
	
	private function parseRemoteIP(){
		/*
		This function takes the switchData and loops through it doing the following
		1) breaking the key value on the character '.' into an array
		2) assigning the element 11 (position 10) of the given tmp array to $rec['lldbuid']
		3) assigning the element 12 (position 11) of the given tmp array to $rec['localIF']
		4) pulling the last 4 elements of this array into a string imploded using '.' as the delimiter to assign as the $rec['ip']
		5) The $rec array is then pushed into the Objects remoteIPS property/variable.
		
		*/
		foreach($this->switchData as $key => $val){
			$tmp = explode('.',$key);
			$rec['lldpuid'] = $tmp[10];
			$rec['localIF'] = $tmp[11];
			$rec['ip'] = implode('.',array_slice($tmp,-4));			
			array_push($this->remoteIPs,$rec);
		}
		
	}
	
	private function parseRemoteNetName(){
		/*
		
			This function pulls is used to pull the remote network name and convert it to its given switch_id using the following
			1) the same info as parseRemoteIP for lldpuid and localIF
			2) explodes the val of the given element on the character ':' to retrive the network name
			3) Takes the given network name array and pushes this to the objects remoteNetwork property
			
			
		*/
		foreach($this->switchData as $key => $val){
			$tmp = explode('.',$key);
			$rec['lldpuid'] = $tmp[10];
			$rec['localIF'] = $tmp[11];			
			$tmp = array();
			$tmp = explode(':',$val);
			$rec['remoteSwitch'] = $tmp[1];
			array_push($this->remoteNetwork,$rec);
		}
	}
	
	private function parseRemoteSDP(){
		/*
		
			This function pulls out the SDP for the Remote Switch using the following steps
			1)loop through the switchData
			2) explode the key on '.' into the sdp array variable
			3) explode the val on ':' into the remote array variable
			4) remove the whitespace from the beginning and end of $remote[1] (This can be refactored into a single step)
			5) Explode $remote[1] into $r array variable on whitespace
			6) Here we have an exception when the count of $r is 4.
				a) taking each element of $r and converting it to hex code seperated by a '.' and copyed into $remote[1] to create a single value in $remote[1]
			7) create an array variable called $data adding the array $sdp to $data['sdp'] and the value of $remote[1] to $data['remoteIP']
			8) add the array $data to the object array remoteSDP
		
		*/
		foreach($this->switchData as $key => $val){
			$sdp = explode('.',$key);
			$remote = explode(':',$val);
			//Exception Code for new OID replacing .4 with .61
			$remote[1] = rtrim($remote[1]);
			$remote[1] = ltrim($remote[1]);
			$r = explode(' ',$remote[1]);
			//error_log(count($r).'total elements',0);
			if(count($r) == 4){
				//error_log('found values',0);
				$remote[1] = hexdec($r[0]);
				$remote[1] .= '.'.hexdec($r[1]);
				$remote[1] .= '.'.hexdec($r[2]);
				$remote[1] .= '.'.hexdec($r[3]);
				//error_log($remote[1],0);
			}
			//End of Exception Code
			$data = array();
			$data['sdp'] = array_pop($sdp);
			$data['remoteIP'] = $remote[1];
			array_push($this->remoteSDP,$data);
		}
	}
	
	private function convertSDP($s){
		/*
		
		This function is used to take a given array $s and do the following
		1) validate that it has no more than 4 elements or terminate
		2) take each element in reverse order and do the following
			a) if element 4 is greater than 0 set that as the seed value
			b) for elements 3 to 1 sum their value * 256 to total
			c) return total
		
		*/
		$total = 0;
		//$t = implode('.',$s);
		//error_log($t.' value passed');
		if(count($s) < 4) exit; //bad data
		if($s[3] > 0) $total = $s[3];
		if($s[2] > 0) $total += ($s[2] * 256);
		if($s[1] > 0) $total += ($s[1] * 256);
		if($s[0] > 0) $total += ($s[0] * 256);
		//error_log($total.' new total amount',0);
		return $total;
	}
	
	private function parseRemoteServiceID(){
		/*
		
		This function  parses each element of the switchData for the following
		1) explode the key on '.'
		2) set element $rec['svid'] equal to the 11th element (position 10) in the key array ($data)
		3) set $sdpid as an array starting at element 12 (posistion 11) and pulling in the next 3 (total of 4) elements.
		4) pass this array to converSDP function and capture the returned total to $rec['sdp']
		5) add the $rec array to the object array remoteServiceID
		
		*/	
		
		foreach($this->switchData as $key => $val){
			$rec = array();
			//error_log('pulled value '.$key,0);
			$data = explode('.',$key);
			$rec['svid'] = $data[10];
			$sdpid = array_slice($data,11,4);			
			$rec['sdp'] = $this->convertSDP($sdpid);
			array_push($this->remoteServiceID,$rec);
		}
	}
	
	private function ConsolidatePortData(){
		/*
		
		 This function takes all the port properties 
		 
		 $remoteIF
		 $remoteIPs
		 $remoteNetwork
		 $remoteSDP
		 $remoteServiceID
		 
		 and consolodates them into the $ports ports object array
		 
		 1) remoteIF is compared to the ports portIfindex property on a match
		 the lldbUid and remoteIfindex of the port object is assigned the values from 4remoteIF['lldpuid'] and ['remoteIF']
		 2) The same comparision as in step 1 is done on remoteIP
		 	a) ports object's property remoteIP is assigned the value of remoteIP['ip']
			b) nested in is a check between remoteIP['ip'] and remoteSDP['remoteIP']
				1) on a match the ports sdp proterty is assigned remoteSDP['sdp'] of the matched element
				2) nested into this a check against the matched remoteServiceID['sdp'] to remoteServiceID array
					a) on a match the svid element is assigned to the ports object's svid property
		3) the remoteNetwork array is looped through compairing the localIF index of the current ports IfIndex on a match the ports remoteSwitchID property is assigned the remoteNetworks remoteSwitch element.
		
		*/
		
		$x = 0;
		foreach($this->ports as $p){
			foreach($this->remoteIF as $r){
				if($p->portIfindex == $r['localIF']){
					$this->ports[$x]->lldpUid = $r['lldpuid'];
					$this->ports[$x]->remoteIfindex = $r['remoteIF'];
				}
			}
			foreach($this->remoteIPs as $ip){
				if($ip['localIF'] == $p->portIfindex){
					$this->ports[$x]->remoteIP = $ip['ip'];
					//error_log(count($this->remoteSDP)."\n",0);
					foreach($this->remoteSDP as $sdp){							
						if($ip['ip'] == ltrim($sdp['remoteIP'])){							
							$this->ports[$x]->sdp = $sdp['sdp'];
							foreach($this->remoteServiceID as $sid){								
								if($sdp['sdp'] == ltrim($sid['sdp'])){									
									$this->ports[$x]->svid = $sid['svid'];
								}
							}
						}
					}
				}				
			}
			foreach($this->remoteNetwork as $r){
				if($r['localIF'] == $p->portIfindex){
					$this->ports[$x]->remoteSwitchId = $r['remoteSwitch'];
				}			
			}
			$x++;
		}
	}
	
	public function BuildPorts($switch,$firmware=0){
		
		/*
		
		 This is the main public interface to this class file
		  The workflow is as follows
		  
		  1) The function takes 1 to 2 parameters 1) switch_id 2) firmware is optional
		  2) Check the switch id parameter and see if its an array if so
		  	a) assign $switch[0] to $switchId
			b) assign $switch[1] to $fware
		 3) If step 2 is false then assign the following
		 	a) $switchId = $switch
			b) $fware = $firmware
		 4) Assign the the returned array from getSwitchConn to $switchConn (this will be the IP address of the switch and the community for SNMP and the model_id of the given switch)
		 5) Open an SNMP connetion assigned to SNMP object
		 6) Pull all oids for the given model for all oid descriptions of PORTS
		 7) Check for oid exceptions and substitute the exception oid
		 8) Loop through the oids and run them assigning the returning array to switchData
		 	a) check the oid description and call the correct parsing function based on description.
		9) run the consolidation function and return the objects ports object array
		
		*/
		
		
		
		//check parameter to see if it came in as part of API or as class method call in other interface
		if(is_array($switch)){
			$switchId = $switch[0];
			$fware = $switch[1];
		}else {
			$switchId = $switch;
			$fware = $firmware;
		}
		
		$switchConn = $this->getSwitchConn($switchId); //get the host and community needed for this ADS
		$this->SNMP = new SNMPDriver($switchConn['ip'],$switchConn['community']);
		$this->oids = $this->getoids($switchConn['model'],'PORTS%'); // pull the needed oids for the make/model of this switch
		$this->oids = $this->pullExceptions($this->oids,$fware);
		foreach($this->oids as $o){
			
			if(!$this->switchData = $this->SNMP->snmpGetter($o['oid'])){
				return -1;
			}
			
			if($this->switchData === false) return -1;
			switch($o['descr']){
				case 'PORTS':
					//echo 'Parsing Ports<br>';
					$this->parsePorts();
					//echo 'Total Ports: '.count($this->ports).'<br>';
				break;
				case 'PORTS REMOTE INDEX':
					//echo 'Parsing Remote Index<br>';
					$this->parseRemoteIfInx();
				break;
				case 'PORTS REMOTE IP':
					//echo 'Parsing Remote IPs<br>';
					$this->parseRemoteIP();
				break;
				case 'PORTS REMOTE NAME':
					//echo 'Parsing Remote Names<br>';
					$this->parseRemoteNetName();
				break;
				case 'PORTS SDP':
					$this->parseRemoteSDP();
				break;
				case 'PORTS SDP SERVICEID':
					$this->parseRemoteServiceID();
				break;
				case 'PORTS 6400':
					$this->parse6400Port();
				break;
			}
			
		}
		$this->ConsolidatePortData();						
		return $this->ports;
	}
}
?>