<?php
//include_once 'Utopiamysql.php';
//include_once 'dbutls.php';
//include_once 'sysUtils.php';
class UtopiaDHCP {
	private $mac = '';
	private $ip = '';
	private $adid = '';
	private $staticList = array();
	private $city = array();
	public $DB = NULL;
	//Constructor
	public function __construct(){
		if(!$this->DB = new Utopiamysql('10.250.255.44','blackr','S3cur3_My_K3@DHCP','kea_dhcp')){
			echo 'Failed to Connect';	
		}	
	}
	//Begin Private Functions
	//IP to MAC and MAC to IP conversion Functions for leases and Statics
	private function macToIP_lease(){
	
		$sql = "SELECT INET_NTOA(address) FROM lease4 where HEX(hwaddr) = '".$this->mac."' and expire = (select max(expire) from lease4 where HEX(hwaddr) = '".$this->mac."') and state != 2";
		//echo $sql;
		$r = $this->DB->runMyQuery($sql);
		$rec = mysqli_fetch_row($r);
		return $rec[0];			
	}
	
	private function IPtoMac_lease(){
	
		$sql = "SELECT HEX(hwaddr) FROM lease4 where  INET_NTOA(address) = '".$this->ip."'";
		$r = $this->DB->runMyQuery($sql);
		$rec = mysqli_fetch_row($r);
		return $rec[0];			
	}
	
	private function macToIP_Static(){
	
		$sql = "SELECT INET_NTOA(ipv4_address) from hosts where HEX(dhcp_identifier) = '".$this->mac."'";
		$r = $this->DB->runMyQuery($sql);
		$rec = mysqli_fetch_row($r);
		return $rec[0];
	}		
	
	private function IPtoMac_Static(){
	
		$sql = "SELECT HEX(dhcp_identifier) from hosts where INET_NTOA(ipv4_address) = '".$this->ip."'";
		$r = $this->DB->runMyQuery($sql);
		$rec = mysqli_fetch_row($r);
		return $rec[0];	
	}
	
	//End IP to MAC and MAC to IP conversion Functions for leases and Statics
	
	// Begin functions for managing the Hosts Table (Static and Reservation Assignment and Management
	
	// Dump All of the Current Reservice and Static Assigned Addresses
	private function PullStaticHosts(){
		$sql = 'select host_id,HEX(dhcp_identifier) mac,dhcp_identifier_type,INET_NTOA(ipv4_address) ip,dhcp4_subnet_id,hostname,dhcp4_client_classes from hosts';
		$r = $this->DB->runMyQuery($sql);
		while($row = mysqli_fetch_array($r)){
			$rec = array(
				
				'host_id' 				=> $row['host_id'],
				'mac'					=> $row['mac'],
				'dhcp_identifier_type'	=> $row['dhcp_identifier_type'],
				'subnet'				=> $row['dhcp4_subnet_id'],
				'ip'					=> $row['ip'],
				'hostname'				=> $row['hostname'],
				'dhcp4_client_classes'	=> $row['dhcp4_client_classes']
			);
			array_push($this->staticList,$rec);
		}
	}
	
	//Search Function for getting Static addresses by ADID
	private function IPMacFromADID_Static(){
	
		$sql = "SELECT HEX(hwaddr) mac,INET_NTOA(address) ip,valid_lifetime,expire,state,hostname from lease4 where  (hostname like '%".$this->adid."%') or (HEX(hwaddr) = '".$this->mac."') and expire = (select max(expire) from lease4 where HEX(hwaddr) = '".$this->mac."')";
		$r = $this->DB->runMyQuery($sql);
		$lease = array();
		while ($row = mysqli_fetch_array($r)){
			$rec = array(
				'mac'					=> $row['mac'],
				'valid_lifetime'		=> $row['valid_lifetime'],
				'expire'				=> $row['expire'],
				'ip'					=> $row['ip'],
				'hostname'				=> $row['hostname'],
				'state'					=> $row['state']);
			array_push($lease,$rec);
		}
		return $lease;		
	}

	private function writeStatic($i){
	
		$sql = "INSERT INTO hosts (dhcp_identifier, dhcp_identifier_type, dhcp4_subnet_id, ipv4_address, hostname)
				VALUES (UNHEX('".$this->mac."'), 0, ".$i['subnet'].", INET_ATON('".$i['ip']."'), '".$i['hostname']."')";
		$r = $this->DB->runMyQuery($sql);
		return $r;
	}
	
	private function updateStatic($i){
		$sql = "UPDATE hosts set dhcp_identifier = UNHEX('".$this->mac."'), dhcp4_subnet_id =  ".$i['subnet'].",ipv4_address = INET_ATON('".$i['ip']."'), hostname = '".$i['hostname']."' where host_id = ".$i['host_id'];
		$r = $this->DB->runMyQuery($sql);
		return $r;
	}
	
	private function deleteStatic($i){
		$sql = "delete from hosts where host_id = ".$i['host_id'];
		$r = $this->DB->runMyQuery($sql);
		return $r;	
	}
	
	private function pullCities(){
		$sql = 'select * from city_subnet';
		$r = $this->DB->runMyQuery($sql);
		while($row = mysqli_fetch_array($r)){
			$rec = array('city' => $row['city'],
						'subnet_id'	=> $row['subnet_id'],
						'network'	=> $row['network'],
						'pool'		=> $row['pool_start']
			);
			array_push($this->city,$rec);	
		}	
	}
	
	private function pullSingleCity($city){
		$sql = "select * from city_subnet where city = '".$city."'";
		$r = $this->DB->runMyQuery($sql);
		while($row = mysqli_fetch_array($r)){
			$rec = array(
				'subnet'	=> $row['subnet_id'],
				'network'	=> $row['network']
			);
		}
		return $rec;
	}
	
	private function NextIP($city){
		$this->pullCities();
		$subnet = array();
		//echo $city;
		foreach($this->city as $c){
			if($c['city'] === $city) {				
				$subnet['start'] = $c['network'].'.11';
				$subnet['end'] = $c['pool'].'.255';
				$tmp = explode('.',$c['network']);
				$network['start'] = $tmp[2];
				$tmp = explode('.',$c['pool']);
				$network['pool'] = $tmp[2];
				$subnet['network'] = $c['network'];
				$subnet['pool']	= $c['pool'];
				break;
			}
		}
		//Assign the value for the city network (starting point) to network which is used to iterate through the three vialbe networks for the cities static pool
		$count = 11; //start iteration through the record set looking for holes or next avaliable IP
		
		$sql = "select INET_NTOA(ipv4_address) from hosts where ipv4_address between INET_ATON('".$subnet['start']."') and INET_ATON('".$subnet['end']."') order by 1 asc";
		$r = $this->DB->runMyQuery($sql);
		$comp = 0;	
		$x = 0;
		//Get all the currently assigned IP's and break them into their seperate networks

		$net = $network['start'];
		while ($row = mysqli_fetch_row($r)){		
			$iparray = explode('.',$row[0]);
			if ($iparray[2] > $net){
				$net = $iparray[2];
				$x = 0;
			}
			$currentAssigned[$net][$x] = $row[0];
			$x++;
		}
		
		//Lets loop through all IP's in each network and find any unassigned IP's that we can assign
		$net = $network['start'];

		while ($net <= $network['pool']){
			if($net > $network['start']) {
				$count = 0;
				if(!isset($currentAssigned[$net])) return $subnet['network'].'.0';
				if (count($currentAssigned[$net]) == 255){// We have not gaps and all IPs are assigned
					$net ++;
					$subnet['network'][2] = $net;
					$subnet['network'] = implode('.',$subnet['network']);
				}else{ //Find an unassigned IP for this network
					while($count <= 255){
						$ip = $subnet['network'].'.'.$count;
						$comp = 0;
						foreach($currentAssigned[$net] as $aip){
							//echo 'assigned '.$aip.'<br>';
							if($ip == $aip){
								$comp = 0;								
								$count++;
								$ip = $subnet['network'].'.'.$count;
								//echo 'next IP '.$ip;
							}else $comp = $ip;
						}
						if ($ip != 0) return $comp;										
					}
				}
			}else{
				 $count = 11;
				 if(!isset($currentAssigned[$net])) return $subnet['network'].'.11';
				 if (count($currentAssigned[$net]) == 244){// We have not gaps and all IPs are assigned
				 	$subnet['network'] = explode('.',$subnet['network']);
					$net ++;
					$subnet['network'][2] = $net;
					$subnet['network'] = implode('.',$subnet['network']);
				}else{ //Find an unassigned IP for this network
				$ip = $subnet['network'].'.'.$count;
					while($count <= 255){						
						$comp = 0;
						foreach($currentAssigned[$net] as $aip){
							//echo 'assigned '.$aip.'<br>';
							//echo 'Compair IP '.$ip.'<br>';
							if($ip == $aip){
								$comp = 0;								
								$count++;
								//echo ' Change Count is '.$count.'<br>';
								$ip = $subnet['network'].'.'.$count;
								//echo 'next IP '.$ip.'<br>';
							}else {
								$comp = $ip;
								//echo 'Not found '.$count.'<br>';
							}
						}
						if ($comp != 0) return $comp;
						//$count++;
						$ip = $subnet['network'].'.'.$count;
					}
				}
			
			}
		
			
		}
		
		
		
	}
	
//End Private Functions Begin Public Interfaces

	public function addStatic($i){
		$this->setMac($i['mac']);
		$ret = $this->writeStatic($i);
		return $ret;	
	}
	
	public function getStaticList(){
		$this->PullStaticHosts();
		return $this->staticList;	
	}
	
	public function setADID($i){
		$this->adid = $i;	
	}
	
	public function setIP($i){
		$this->ip = $i;	
	}
	public function setMac($i){
		$UTIL = new sysUtils;
		//strip any .,- or other device notation formating and return just the hex value
		$mac = $UTIL->formatMac($i,1);
		//assign mac to object
		$this->mac = $mac;	
	}
	public function getIP($i){
		$this->setMac($i);		
		$ret = $this->macToIP_Static();	
		if($ret == NULL || $ret == ''){
			$ret = $this->macToIP_lease();
		}
		//echo $ret;
		return $ret;		
	}
	
	public function getMac($i){
		$this->setIP($i);
		$ret = $this->IPtoMac_Static();
		if($ret == NULL || $ret == ''){
			$ret = $this->IPtoMac_lease();				
		}
		return $ret;
	}
	public function getStaticFromADID($i){
		$this->setADID($i);
		$this->setMac($i);
		$ret = $this->IPMacFromADID_Static();
		return $ret;
	}
	
	public function getCity(){
		$this->pullCities();
		return $this->city;	
	}
	
	public function getSingleCity($city){
		$c = $this->pullSingleCity($city[0]);
		return $c;	
	}
	
	public function getNextIP($city){
		$ip = $this->NextIP($city[0]);		
		/*$iparray = explode('.',$ip);
		if($iparray[3] == 11 || $iparray[3] == 255) return $ip;
		$iparray[3] += 1;
		$ip = implode('.',$iparray);*/
		return $ip;	
	}
	
	public function resetStatic($i){
		$this->setMac($i['mac']);
		$ret = $this->updateStatic($i);
		if ($ret)return 1 ;
		else return -1;	
	}
	public function removeStatic($i){
		$ret = $this->deleteStatic($i);	
		if ($ret)return $ret;
		else return -1;	
	}
	
	public function getnetwork($ip){
		$net = explode('.',$ip);
		
		
		foreach ($this->city as $c){
			$tmpNet = explode('.',$c['network']);
			$tmpPool = explore('.',$c['pool_start']);
			if($net[1] >=$tmpNet[1] && $net[1] <= $tmpPool[1]){
				return $c['network']+'.1';	
			}
		}
	}
}
