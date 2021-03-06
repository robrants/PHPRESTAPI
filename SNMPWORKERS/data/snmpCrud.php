<?php
	class snmpCrud extends myOracle{
		private $switchObj;
		private $ports;
		private $lags;
		private $vlans;
		private $stack;
		private $logger;
		private $runcount;
		use crudBuilder;
		public function __construct($s,$l){
			parent::__construct();
			$this->logger = new appLog($l);
			$this->switchObj = $s;
			error_log('Breaking Out Each Object',0);
			//pull out each object array to be parsed
			$this->ports = $this->switchObj->ports;
			$this->lags = $this->switchObj->lags;
			$this->vlans = $this->switchObj->vlan_services;
			$this->stack = $this->switchObj->stack;
			
			//Step to validate and update firmware version
			if($this->switchObj->firmware != $this->pullFirmware()){
				$audit = array('pkrecord' => $tgus->switchObj->switch_id,'tablename' => 'blackr.masterads','colname' => 'firmware','oldval' => $this->pullFirmware(),'newval' => $this->switchObj->firmware);
				$where = ' where switch_id = '.$this->switchObj->switch_id;
				$update = array('firmware' => $this->switchObj->firmware);
				$this->updateRecord('blackr.masterads',$update,$where);
			}
			
			//Step one validate the network name			
			if($this->switchObj->network_name != $this->pullNetwork_name()){
				$audit = array('pkrecord' => $this->switchObj->switch_id,'tablename' =>'blackr.masterads','colname' =>'network_name','oldval'=>$this->pullNetwork_name(),'newval'=>$this->switchObj->network_name);
				error_log('No NetworkName Found',0);
				$where = ' where switch_id = '.$this->switchObj->switch_id;
				$update = array('network_name' => $this->switchObj->network_name);
				$this->updateRecord('blackr.masterads',$update,$where);
			}
			//Set a new runid to tracking changes to this switches tables
			$this->getUpdateID();			
			//Step two process and validate the Local and remote Ports
			error_log('Begin Processing Ports',0);
			$this->processPorts();
			//Step Three process lags
			error_log('Begin Processing Lags',0);
			$this->processLags();
			//Step Four Process Vlan Services
			error_log('Begin Processing Vlan Data',0);
			$this->processVlan();
			//Step Five Process Stacks
			error_log('Begin Processing Stack Data',0);
			$this->processStack();	
			error_log('All Done!',0);
		}
		
		private function getUpdateID(){
			$table = 'blackr.masteradsruncount';
			$runidSQL = 'select blackr.seq_runids.nextval from dual';
			$r = $this->runQuery($runidSQL);
			$runid = oci_fetch_row($r);
			$s = $this->switchObj->switch_id;
			$insert = "insert into $table (runid,runstamp,switch_id) values($runid[0],sysdate,$s)";
			$r2 = $this->runInsert($insert);
			if($r2){
				 $this->runcount = $runid[0];
				error_log($this->runcount,0);
			}
		}
		
		private function addRecord($table,$data){
			if($table == 'blackr.tableaudits'){
				//error_log('total Columns is '.count($data),0);
				$keys = implode(',',array_keys($data));
				$vals = $this->parseVals($data);
				$insert = "insert into $table (updatestamp,$keys) values(sysdate,".$vals.")";
			}else $insert = $this->buildInsert($table,$data,0);
			//error_log($insert,0);			
			return $this->runInsert($insert);
		}
		
		private function updateRecord($table,$data,$where){			
			$insert = $this->buildUpdate($table,$data,$where);
			error_log($insert,0);
			if($this->runInsert($insert)){error_log('updated',0);}
			else return -1;
			return 1;
		}
		
		
		private function pullFirmware(){
			$sql = 'select firmware from blackr.masterads where switch_id ='.$this->switchObj->switch_id;
			$r = $this->runQuery($sql);
			$row = oci_fetch_row($r);
			return $row[0];
		}
		
		private function pullNetwork_name(){
			error_log('returning Switch_id '.$this->switchObj->switch_id,0);
			$sql = 'select network_name from blackr.masterads where switch_id = '.$this->switchObj->switch_id;
			$r = $this->runQuery($sql);
			while($row = oci_fetch_row($r)){
				$network_name = $row[0];
			}
			error_log($network_name,0);
			return $network_name;
		}

		private function getSiteId($networkName){
			//pull the switch_id from a given network name
			$sql = "select switch_id from blackr.masterads where network_name = '".$networkName."'";
			$r = $this->runQuery($sql);
			$row = oci_fetch_row($r);
			return $row[0];
		}
		
		//Pull the DB records for each object set
		
		private function pullLocalPorts(){
			$sql = "select * from blackr.localports where switch_id = ".$this->switchObj->switch_id;
			error_log('Getting LocalPorts from DB',0);
			if($r = $this->runQuery($sql)){
				$data = array();
				while($row = oci_fetch_array($r)){
					$rec = array('ifindex' 	=>$row['IFIDXID'],
							'serial' 	=> $row['SERIAL'],
							'port'		=> $row['PORT'],
							'porttype'	=> $row['PORTTYPE'],
							'descr'		=> $row['DESCR']);
					array_push($data,$rec);
				}
				return $data;
			} else return 0;
		}
		
		private function pullRemotePorts($ifindex){
			$sql = "select * from blackr.remoteports where local_switch_id = ".$this->switchObj->switch_id." and local_ifindex = ".$ifindex;
			if($r = $this->runQuery($sql)){
				$data = array();
				while($row = oci_fetch_array($r)){
					$rec = array('local_switch'		=>$row['LOCAL_SWITCH_ID'],
							'lldp_uid' 			=> $row['LLDP_UID'],
							'local_ifindex'		=> $row['LOCAL_IFINDEX'],
							'remote_ifindex'	=> $row['REMOTE_IFINDEX'],
							'remote_switch_id'	=> $row['REMOTE_SWITCH_ID'],
							'remote_ip'			=> $row['REMOTE_IP']);
					array_push($data,$rec);
				}
				return $data;
			}
			return -1;
		}
		
		private function pullLags(){
			$sql = "select * from blackr.lags where switch_id = ".$this->switchObj->switch_id;
			$r = $this->runQuery($sql);
			$data = array();
			while($row = oci_fetch_array($r)){
				$rec = array('lagifindex'	=> $row['IFINDEX'],
							'lagnum'		=> $row['LAGNUM']);
				array_push($data,$rec);
			}
			return $data;
		}
		
		private function pullVlanServices(){
			$sql = "select * from blackr.VLANSERVICES where switch_id = ".$this->switchObj->switch_id;
			$data = array();
			$r = $this->runQuery($sql);
			while($row = oci_fetch_array($r)){
				$rec = array('serviceid' 	=>$row['SERVICEID'],
							'vlan_tag'	=>$row['VLAN_TAG'],
							'ifindex'	=> $row['IFINDEX'],
							'switch_id'	=> $row['SWITCH_ID']);
				array_push($data,$rec);
			}
			return $data;
		}
		
		private function pulllagstoports($lag){
			$sql ="select * from blackr.lagstoports where lagifindex = ".$lag;
			$data = array();
			$r = $this->runQuery($sql);
			while($row = oci_fetch_array($r)){
				$rec = array('portifindex'	=>$row['PORTIFINDEX'],
							'descr'			=> $row['DESCR']);
				array_push($data,$rec);
			}
			return $data;
		}
		
		private function pullStack(){
			$sql = "select serial,mac,partnum,hardwareIndex,position,label from blackr.switchslots where switch_id = ".$this->switchObj->switch_id." order by position";
			$data = array();
			$r = $this->runQuery($sql);
			$x=0;
			while($row = oci_fetch_array($r)){
				$x++;
				$rec = array('position' => $row['POSITION'],
							'serial' 	=> $row['SERIAL'],
							'mac'		=> $row['MAC'],
							'partnum'	=> $row['PARTNUM'],
							 'label'	=> $row['LABEL'],
							'hrdidx'	=> $row['HARDWAREINDEX']);
				array_push($data,$rec);
			}
			return $data;
		}
		//These sections need to be finished and implemented once we have data flowing that we are confident in
		private function validateMngFootprint(){
			$sql ="select 1 
				from BLACKR.SERVICE_VLAN sv,BLACKR.MASTERADS m,BLACKR.MNGFOOTPRINTS mf
				where sv.switch_id = s.switch_id and sv.service_id = mf.service_id 
				and s.mngfootprint_id = mf.footprint_id 
				and mv.switch_id = ". $this->switchObj->switch_id;
			$r = $this->runQuery($sql);
			$row = oci_fetch_row($r);
			if($row[0] == 1) return 1;
			else $this->updateMngFootprint();
		}
		
		private function pullSwitchRole(){}
		
		private function updateMngFootprint(){
			$sql = "select mf.footprintid from blackr.mngfootprints mf, blackr.service_vlan sv where mf.service_id = sv.service_id and sv.switch_id= ".$this->switchObj->switch_id;
			$r = $this->runQuery($sql);
			$x=0;
			while($row = oci_fetch_row($r)){
				$footprint = $row[0];
				$x++;
			}
			if($x>1) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Too Many MNGFootprints found'); //error_log('Too Many MNGFootprints found',0);
			else {
				$update = array('mngfootprint' => $footprint);
				$where = 'where switch_id = '.$this->switchObj->switch_id;
				if($this->updateRecord('blackr.masterads',$update,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Successfully Updated'); //error_log('mngfootprint updated',0);
				else  $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Updated');//error_log('Failed to update mngfootprint',0);
			}
		}
		
		private function validateSDP(){
			
		}
		//End of next phase code
		
		private function scrubValue($val){
			if(count($tmp = explode('\"',$val)) > 1){
				$newval = $tmp[1];
			}elseif(count($tmp = explode('"',$val)) > 1){
				$newval=  $tmp[1];
			}elseif(count($tmp = explode(':',$val)) > 1){
				$newval = implode(':',array_slice($tmp,0,count($tmp)-2));
			}else{
				$newval= $val;
			}
			return $newval;
		}
		
		//Process and validate data adding and updating as needed
		private function processPorts(){
			$recset = $this->pullLocalPorts();			
			foreach($this->ports as $p){
				//Set up new arrays for local and remote ports on each switch to be processed
				$localPort = array();
				$remotePort = array();
				//Add log entry to debugging and trouble shooting in future
				error_log('processing Ports',0);
				preg_match('/Alcatel-Lucent*/',$this->switchObj->make,$matches);
				if(count($matches)>0){
					//6400 series switch
					//echo '6400<br>';
					if($p->portIfindex < 2000) {
						$positions[0] = 1;
						$positions[1] = substr($p->portIfindex,-2);							
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 2000 && $p->portIfindex < 3000) {
						$positions[0] = 2;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 3000 && $p->portIfindex < 4000) {
						$positions[0] = 3;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 4000 && $p->portIfindex < 5000) {
						$positions[0] = 4;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 5000 && $p->portIfindex < 6000) {
						$positions[0] = 5;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 6000 && $p->portIfindex < 7000) {
						$positions[0] = 6;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif($p->portIfindex > 7000 && $p->portIfindex < 8000) {
						$positions[0] = 7;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
					elseif ($p->portIfindex > 8000 && $p->portIfindex < 9000){
						$positions[0] = 8;
						$positions[1] = substr($p->portIfindex,-2);
						if(substr($positions[1],0,1) == '0') {
							$positions[1] = substr($positions[1],-1);
							//echo $positions[1]."<br>";
						}
					}
				}elseif($p->port == NULL or $p->port == ''){
					//Notification in error logs of a port that was either a management port or loopback port or a 6400 series
					error_log('NonCore Switch Found '.$p->portDescr,0);
					$port = explode(' ',$p->portDescr);
					//error_log('Port is '.$port[2],0);
					$positions = explode('/',$port[2]); 
				}else $positions = explode('/',$p->port);
				foreach($this->stack as $s){
					if($s->position == $positions[0]){
						//find the serial number for the given card/stack the port is associated to						
						$localPort['serial'] = $s->serial;						
					}
				}
				
				if(!isset($localPort['serial'])) error_log('No Serial Number',0); //Log if no serial number matches up with the given port
				//Assign the values from the OID pulled local port data to the localport array prior to validation with DB data
				$localPort['switch_id'] = $this->switchObj->switch_id;
				$localPort['ifidxid'] = $p->portIfindex;				
				$localPort['port'] = implode('/',$positions);
				$localPort['porttype'] = $p->portType;
				$localPort['descr'] = $p->portDescr;
				//Scrub the OID data so that is will match up with the DB data remove special characters and spaces
				$localPort = $this->ScrubData($localPort);
				//Setup loop for comparing current port to db.
				$found = 0;
				//$update = array(); redundant line
				//Check to see if we pulled back any data on this switch for ports. If we did not this is simple as we will have to add each port entry
				//Otherwise we walk through it for each port.
				if(count($recset) >0){
					foreach($recset as $rec){
						$update = array();
						if($localPort['ifidxid'] == $rec['ifindex']){
							//We have a match
							$found = 1;
							//update the runid to show this port is present form previous run							
							$update['runid'] = $this->runcount;
							//Validate each column in the record and set the update array for those that do not match and add that column to the audit trail.
							$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$localPort['ifidxid'],'tablename' =>'blackr.localports');
							if($localPort['port'] != $rec['port']) {
								$update['port'] = $localPort['port'];
								$audit['colname'] = 'port';
								$audit['oldval'] = $rec['port'];
								$audit['newval'] = $localPort['port'];
								//$audit = $this->scrubData($audit);
								$this->addRecord('blackr.tableaudits',$audit);
							}
							if($localPort['porttype'] != $rec['porttype']) {
								$update['porttype'] = $localPort['porttype'];
								$audit['colname'] = 'porttype';
								$audit['oldval'] = $rec['porttype'];
								$audit['newval'] = $LocalPort['porttype'];
								//$audit = $this->scrubData($audit);
								$this->addRecord('blackr.tableaudits',$audit);
							}
							if($localPort['descr'] != $rec['descr']) {
								$update['descr'] = $localPort['descr'];
								$audit['colname'] = 'descr';
								$audit['oldval'] = $rec['descr'];
								$audit['newval'] = $localPort['descr'];
								//$audit = $this->scrubData($audit);
								$this->addRecord('blackr.tableaudits',$audit);
							}
							if($localPort['serial'] != $rec['serial'] && isset($localPort['serial']) && $localPort['serial'] != '') {
								$update['serial'] = $localPort['serial'];
								$audit['colname'] = 'serial';
								$audit['oldval'] = $rec['serial'];
								$audit['newval'] = $localPort['serial'];
								//$audit = $this->scrubData($audit);
								$this->addRecord('blackr.tableaudits',$audit);
							}
						}
						//Did we have a matched record if not add a new record with a null runid
						if(count($update)> 0){
							$where = ' where ifidxid = '.$p->portIfindex.' and switch_id = '.$this->switchObj->switch_id;
							if($this->updateRecord('blackr.localports',$update,$where)){
								$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Successfully Updated');//error_log('Updated Port',0);
							}else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Updated');//error_log('Failed To Update Port',0);
						}
					}
				}
				if($found != 1){
						$localPort = $this->validateData($localPort);
						if($this->addRecord('blackr.localports',$localPort)){
							$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'LocalPort Added');//error_log('LocalPort Added',0);							
						}else{
							$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add LocalPort');//error_log('failed to add Local port',0); //change this to a local file for easy tracking of issues
					}			
				}
				//error_log('Adding Remote Ports',0);
				if(isset($p->remoteIfindex)){
					//error_log('entering into remote port logic',0);
					$remotePort = array('local_switch_id' =>$this->switchObj->switch_id,
										'lldp_uid' =>$p->lldpUid,
										'local_ifindex' => $p->portIfindex,
										'remote_ifindex' => $p->remoteIfindex,
										'remote_ip' => $p->remoteIP);
					//error_log($this->scrubValue($p->remoteSwitchId),0);
					$remotePort['remote_switch_id'] = $this->getSiteId($this->scrubValue($p->remoteSwitchId));
					$remotePort = $this->scrubData($remotePort);
					//error_log($p->remoteIP.' = '.$remotePort['remote_ip'],0);
					if($remoteRecSet = $this->pullRemotePorts($remotePort['local_ifindex'])){
						$where = ' where local_ifindex = '.$remotePort['local_ifindex'].' and local_switch_id = '.$remotePort['local_switch_id'];
				//Setup Loop for remote update.
						$remoteFound = 0;
						$remoteUpdate = array();
						$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$remotePort['local_ifindex'].'-'.$remotePort['lldp_uid'],'tablename' =>'blackr.remoteports');
						foreach($remoteRecSet as $remote){					
							if($remote['remote_ifindex'] == $remotePort['remote_ifindex']){
						//We have a match
								$remoteUpdate['runid'] = $this->runcount;
								$remoteFound = 1;
								//error_log('Found Remote Port',0);
								if($remote['lldp_uid'] != $remotePort['lldp_uid']) {
									error_log('lldb_uids dont match. Switch Has '.$remotePort['lldp_uid'].' DB has '.$remoteUpdate['lldp_uid'],0);
									$remoteUpdate['lldp_uid'] = $remotePort['lldp_uid'];
									$audit['colname'] = 'lldp_uid';
									$audit['oldval'] = $remote['lldp_uid'];
									$audit['newval'] = $remotePort['lldp_uid'];
									//$audit = $this->scrubData($audit);
									$this->addRecord('blackr.tableaudits',$audit);
								}
								if($remote['remote_ip'] != $remotePort['remote_ip']) {
									error_log('Ips dont match',0);
									$remoteUpdate['remote_ip'] = $remotePort['remote_ip'];
								}
								if($remote['remote_switch_id'] != $remotePort['remote_switch_id']) {
									error_log('Switch_id does not match',0);
									$remoteUpdate['remote_switch_id'] = $remotePort['remote_switch_id'];
								}
							}
							if($remoteFound == 1 && count($remoteUpdate) >0 ){
								if($this->updateRecord('blackr.remotePorts',$remoteUpdate,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'RemotePort 	Updated');//error_log('Remote Port Updated',0);
								else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'RemotePort Failed to Updated');//error_log('Remote Port Failed to Update',0);
							}
						}
						if(count($remoteUpdate) == 0 && $remoteFound == 0){
							$remotePort = $this->validateData($remotePort);
							if($this->addRecord('blackr.RemotePorts',$remotePort)){
								$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'RemotePort Added');//error_log('Remote Port Added',0); //Move to local file for easy tracking
							}else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add RemotePort');//error_log('failed to add Remote port',0); //change this to a local file for easy tracking of issues
						}
					}else{
						$remotePort = $this->validateData($remotePort);
						if($this->addRecord('blackr.RemotePorts',$remotePort)){
							$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'RemotePort Added');//error_log('Remote Port Added',0); //Move to local file for easy tracking
						}else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add RemotePort');//error_log('failed to add Remote port',0); //change this to a local file for easy tracking of issues						
					}
					
				}			
			}
		}
		
		private function processLags(){
			$recset = $this->pullLags();
			if(count($this->lags) == 0) return -1;
			foreach($this->lags as $l){
				$found = 0;
				$update = array();
				$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$l->lagIfIndex,'tablename' =>'blackr.lags');
				foreach($recset as $rec){
					if(count($rec) == 0) break;
					if($rec['lagifindex'] == $l->lagIfIndex){
						//We have a match
						error_log('Lag found',0);
						$found = 1;
						$update['runid'] = $this->runcount;
						if($l->lagNum != $rec['lagnum']) {							
							$update['lagnum'] = $l->lagNum;
							$audit['colname'] = 'lagnum';
							$audit['oldval'] = $rec['lagnum'];
							$audit['newval'] = $l->lagNum;
							//$audit = $this->scrubData($audit);
							$this->addRecord('blackr.tableaudits',$audit);
						}
						break;
					}
				}
				if($found == 1 && count($update) > 0){ //Update found switch or move on to ports
					$where = ' where switch_id = '.$this->switchObj->switch_id.' and ifindex = '.$l->lagIfIndex;
					if($this->updateRecord('blackr.lags',$update,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Lag Updated');//error_log('Lag Updated',0);
					else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Update Lag');//error_log('Failed to Update Lag',0);
				}elseif($found == 0){
					$lag = array('switch_id' => $this->switchObj->switch_id,'Ifindex' => $l->lagIfIndex,'lagnum' => $l->lagNum);
					if($this->addRecord('blackr.lags',$lag)){
						$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Lag Added');//error_log('lag added successfully',0); //Move to seperate local file for better record keeping	
					}else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add Lag');//error_log('Lag failed to added',0);
				}
				//Lag management complete move to the lags ports	
				$this->processLagtoPorts($l);
			} //End Lag Loop
		} //End Function
		
		private function processLagtoPorts($lag){
			$lagportrec = $this->pulllagstoports($lag->lagIfIndex);
				foreach($lag->ports as $p){
					$pfound = 0;
					$p['portDescr'] = $this->scrubValue($p['portDescr']);
					$pupdate = array();
					foreach($lagportrec as $portrec){
						//error_log($portrec['portifindex'].'='.$p['portIFIndex'],0);
						$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$p['portIFIndex'].'-'.$lag->lagIfIndex,'tablename' =>'blackr.lagstoports');
						if($portrec['portifindex'] == $p['portIFIndex']){
							//match Found
							$pfound = 1;
							$pupdate['runid'] = $this->runcount;
							if($p['portDescr'] != $portrec['descr']) {
								$pupdate['descr'] = $p['portDescr'];
								$audit['colname'] = 'portDescr';
								$audit['oldval'] = $portrec['portDescr'];
								$audit['newval'] = $p['descr'];
								//$audit = $this->scrubData($audit);
								$this->addRecord('blackr.tableaudits',$audit);
								
							}
							break;
						}
					}
					if($pfound == 1 && count($pupdate)>0){
						$where = ' where lagifindex = '.$lag->lagIfIndex.' and portifindex = '.$p['portIFIndex'];
						if($this->updateRecord('blackr.lagstoports',$pupdate,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Updated Lag to Port');//error_log('Lag to Port Updated',0);
						else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to update Lag to Port');//error_log('failed to update Lag to Port',0);
					}elseif($pfound == 0){
						$lagPort = array('lagIfIndex' => $lag->lagIfIndex,'portIfIndex' => $p['portIFIndex'],'descr' => $p['portDescr']);
						//$lagPort = $this->scrubData($lagPort);
						if($this->addRecord('blackr.lagstoports',$lagPort)){
							$this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Lag to Port Added');//error_log('Lag To Port Added',0);
						}else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add Lag to Port');//error_log('Failed to Add Lag To Port',0);
					} //End Insert Block					
				}//End Ports to Lag Loop
		}
		
		private function processVlan(){
			$recset = $this->pullVlanServices();
			//loop start for vlan services
			foreach($this->vlans as $v){
				//set found and update variables
				$found = 0;
				$update = array();
				//Start data loop
				foreach($recset as $rec){
					//error_log('ifindex is '.$rec['ifindex'].' Switch has '.$v->ifindex,0);
					//error_log('switch_id is '.$rec['switch_id'].' Switch has '.$this->switchObj->switch_id,0);
					if($v->ifindex == $rec['ifindex'] && $v->vlanTag == $rec['vlan_tag'] && $rec['switch_id'] == $this->switchObj->switch_id){
						//match found
						//error_log('VlanMatch',0);
						$found = 1;	
						$update['runid'] = $this->runcount;
						$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$v->ifindex.'-'.$v->vlanTag,'tablename' =>'blackr.vlanservices');
						if($rec['serviceid'] != $v->serviceId) {
							$update['serviceid'] = $v->serviceId;
							$audit['colname'] = 'serviceid';
							$audit['oldval'] = $rec['serviceid'];
							$audit['newval'] = $v->serviceId;
							//$audit = $this->scrubData($audit);
							$this->addRecord('blackr.tableaudits',$audit);
						}
						break;
					}
				}
				if($found ==1 && count($update) > 0){
					$where = ' where ifindex = '.$v->ifindex.' and vlan_tag = '.$v->vlanTag;
					if($this->updateRecord('blackr.vlanservices',$update,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'VlanService Updated');//error_log('VlanService Updated',0);
					else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Update VlanService');//error_log('failed to update vlanServce',0);
				}elseif($found == 0){
					$vlan = array('switch_id' => $this->switchObj->switch_id,'ifindex' => $v->ifindex,'vlan_tag' => $v->vlanTag,'serviceId' => $v->serviceId);
					if($this->addRecord('blackr.vlanservices',$vlan)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'VlanService Added');//error_log('VlanService Added',0);
					else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Add VlanService');//error_log('Failed to Add VlanService',0);
				}
			}
		}
		
		private function processStack(){
			$recset = $this->pullStack();
			//build loop for chassis
			//error_log(count($this->stack),0);
			if(count($this->stack) == 0) return -1;
			foreach($this->stack as $s){
				//scrub the data first convert object to array and apss to trait then convert back to object
				$s = json_decode(json_encode($s),true);
				$s = $this->scrubData($s);
				$s = json_decode(json_encode($s),false);
				error_log('Working Through Stack',0);
				//setup found and update vars
				$found = 0;
				$update = array();
				//setup loop for data				
				foreach($recset as $rec){
					if(count($rec) == 0) break;
					if($rec['mac'] == $s->mac){
						//match found
						//error_log('in',0);	
						$found = 1;
						$update['runid'] = $this->runcount;
						$audit = array('pkrecord' => $this->switchObj->switch_id.'-'.$s->mac,'tablename' =>'blackr.switchslots');
						if($rec['serial'] != trim($s->serial)) {
							error_log('serial is '.$rec['serial'].' and object is '.$s->serial,0);
							$audit['colname'] = 'serial';
							$audit['oldval'] = $rec['serial'];
							$audit['newval'] = $s->serial;
							$update['serial'] = $s->serial;
							//error_log(count($audit),0);
							//$audit = $this->scrubData($audit);
							$this->addRecord('blackr.tableaudits',$audit);
						}
						if($rec['partnum'] != trim($s->partnum)) {
							error_log('partnum is '.$rec['partnum'].' and object is '.$s->partnum,0);
							$update['partnum'] = $s->partnum;
							$audit['colname'] = 'partnum';
							$audit['oldval'] = $rec['partnum'];
							$audit['newval'] = $s->partnum;
							//error_log(count($audit),0);
							//$audit = $this->scrubData($audit);
							$this->addRecord('blackr.tableaudits',$audit);
						}
						if($rec['mac'] != trim($s->mac)) {
							error_log('mac is '.$rec['mac'].' and object is '.$s->mac,0);
							$update['mac'] = $s->mac;
							$audit['colname'] = 'mac';
							$audit['oldval'] = $rec['mac'];
							$audit['newval'] = $s->mac;
							//error_log(count($audit),0);
							//$audit = $this->scrubData($audit);
							$this->addRecord('blackr.tableaudits',$audit);
						}
						if($rec['label'] != trim($s->label)){
							error_log('label is '.$rec['label'].' and object is '.$s->label,0);
							$update['label'] = $s->label;
							$audit['colname'] = 'label';
							$audit['oldval'] = $rec['label'];
							$audit['newval'] = $s->label;
							$this->addRecord('blackr.tableaudits',$audit);
						}
						break;
					}
				}				
				// Check if we have an update
				if($found == 1){
					if(count($update) > 0){	
						$where = ' where switch_id = '.$this->switchObj->switch_id.' and (position = '.$s->position;
						if(isset($s->hrdIndex)){
							$where .= ' or HARDWAREINDEX = '.$s->hrdIndex.')';
						}else $where .= ')';
						//$where = ' where switch_id = '.$this->switchObj->switch_id.' and (position = '.$s->position.' or HARDWAREINDEX = '.$s->hrdIndex.')';
						if($this->updateRecord('blackr.switchslots',$update,$where)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Stack 	Updated');//error_log('Stack updated',0);
						else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Failed to Update Stack');//error_log('Failed to update Stack',0);
					}
				}else{
					error_log('Lets add A record',0);
					if(!isset($s->mac) or $s->mac == '') break; //no data skip it
					$stackOBJ = array('SWITCH_ID'=>$this->switchObj->switch_id,'HARDWAREINDEX' =>$s->hrdIndex,'position'=>$s->position,'serial'=>$s->serial,'partnum'=>$s->partnum,'mac'=>$s->mac);
					//$stackOBJ = $this->scrubData($stackOBJ);
					$stackOBJ = $this->validateData($stackOBJ);
					//error_log(count($stackOBJ).' to add to table',0);
					if($this->addRecord('blackr.switchslots',$stackOBJ)) $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Stack Added Added');
					else $this->logger->addentry('switchcrud.log',__CLASS__,__METHOD__,'Stack Failed to Add');
				}
			}
			
		}				
	}
?>