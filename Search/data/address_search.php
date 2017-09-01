<?php
//
//include "../mu/classes/myOracle.php";
//include "../mu/classes/Utopiamysql.php";
class address_search extends myOracle{
	private $house;
	private $street;
	private $num_suffix;
	private $city;
	private $apt;
	private $street_type;
	private $validaddress = array();
	private $address_1 = array();
	private $rtypes = array();
	private $directional = array('NORTH','SOUTH','EAST','WEST','N','S','E','W');
	private $query;
	private $logPath;
	private $DB2;
	
	public function __construct(){
		parent::__construct();
// Break up the street address into its parts based on spaces between each elelment
		//$this->DB2 = new utopiaMysql('192.168.253.11','wordpress','Go4Utopia','wordpress');
		$this->logPath = $_SERVER['DOCUMENT_ROOT'].'/Applications/Search/logs/address_search.log';						
	}
	
	private function logresults(){
		$q = $this->getQuery();
		$logtime = date('d/m/Y H:i:s');
		$entry = $logtime."\nAddress Breakdown \n House: ".$this->house."\n Suffix: ".$this->num_suffix."\n Street : ". $this->street."\n Street Type: ".$this->street_type."\n Apt: ".$this->apt."\n Query used\n". $q."\n Results From Query\n ".$this->dumpValidAddress(2);
		$results = file_put_contents($this->logPath,$entry,FILE_APPEND);
		return $results;
	}
	
	private function compareSuffix($a){

		for ($x = 0; $x< count($this->directional); $x++){
			$ele = explode('.',$a);
			$a = implode($ele);
			if(strtoupper($a) == $this->directional[$x])  return 1;
		}
		return 0;
	}
	
	private function compareStype($a){
		for ($x = 0; $x< count($this->rtypes); $x++){
			if(strtoupper($a) == $this->rtypes[$x])  return 1;
		}
		return 0;
	}
	
	private function check_address(){
		
		$s = "select * from blackr.om_address_lookup_more_info where house_num = ". $this->house ." and house_number_suffix like (upper('". $this->num_suffix. "%')) and street_name like (upper('%".$this->street."%')) and city like upper('".$this->city."%')";
		//if(isset($this->street_type)) $s .= " and street_typ like upper('%".$this->street_type."%')"; 
		if(isset($this->apt)) $s .= " and APT_UNIT = '".$this->apt."'"; 
		//return $s;
		$this->query = $s;				
		//return $s;
		$r = $this->runQuery($s);
		$address_list = array(); 
		while($row = oci_fetch_array($r)){
			$rec = array(
					 'address'			=> $row['HOUSE_NUM'].' '.$row['HOUSE_NUMBER_SUFFIX'].' '.$row['STREET_NAME'],
					 'address_unit'		=> $row['APT_UNIT'],
					 'city'				=> $row['CITY'],
					 'zip'				=> $row['POSTAL_CODE'],
					 'address_id'		=> $row['ADDRESS_ID'],
					 'land_use'			=> $row['LANDUSE'],
					 'current'			=> $row['AU_ADDRESS_STATUS'],
					 'status'			=> $row['GIS_ADDRESS_STATUS'],
					 'footprint_id'		=> $row['FOOTPRINT_ID']
			);
			if ($rec['address_unit'] == '<NULL>'){
				$rec['address_unit'] = '';
			}
			array_push($this->validaddress,$rec);
		}
		
		oci_free_statement($r);
		//get Contract data;
		for($x = 0; $x < count($this->validaddress); $x++){
			$s = "select cue_type from sm.cue_tracker where  add_id_status like ('Active%')
                and cue_type not like '%L%'
                and cue_type not like 'R%'
				and address_id = ".$this->validaddress[$x]['address_id'];
				//return $s;
			$r = $this->runQuery($s);
			while($row = oci_fetch_array($r)){
				$this->validaddress[$x]['ctype'] = $row['CUE_TYPE'];	
			}
			if (!isset($this->validaddress[$x]['ctype']))$this->validaddress[$x]['ctype'] = 'None';
		
			oci_free_statement($r);
			//$DB = ""; //Clear Oracle Connection and connect to mysql for current orders
			
			$s = "select * from utopia_order where aid = ".$this->validaddress[$x]['address_id']." and lock_changes = 0 and order_status like '%COMPLETE%'";
			$r = $this->DB2->runMyQuery($s);
			if($r->num_rows > 0)$this->validaddress[$x]['pending'] = 1;
			else $this->validaddress[$x]['pending'] = 0;
			mysqli_free_result($r);
		}
	}
	
	public function validateAddress(){
		$this->check_address();
		if(isset($this->validaddress)){
			$this->logresults();
			return $this->validaddress;
		}else return -1;
	}
	public function getQuery(){
		return $this->query;	
	}
	public function getPrivates(){
		echo "House ".$this->house."<br />";
		echo "Suffix ".$this->num_suffix."<br />";
		echo "Street ".$this->street."<br />";
		echo "city ".$this->city."<br />";
	}
	public function dumpValidAddress($flag = 1){
		$output = '';
		for ($x = 0; $x<count($this->validaddress); $x++){
			foreach($this->validaddress[$x] as $key=>$value){
				if ($flag == 1) echo $key .' ='.$value.'<br />';
				else $output .= $key.' = '.$value."\n";					
			}
		}
		if ($flag != 1)return $output;
	}
	
	public function setAddress($input){
		$ip = $input[0];
		//echo $ip.'<br>';
		$this->DB2 = new utopiaMysql($ip,'wordpress','Go4Utopia','wordpress');
		$a = rtrim($input[1]);
		$apt = rtrim($input[3]);
		$this->address_1 = explode(' ',$a);
		
		//Check number of elements in street address
		
		$selements = count($this->address_1);
		
		// Assign City
		
		$this->city = rtrim($input[2]);
		$this->house = $this->address_1[0]; //the first element will always be the house number.
		if (isset($apt) && $apt != 'NONE') $this->apt = $apt;
		//$DB = new myOracle();
		
		//Build out directional array
		
		$s = "select distinct street_typ from osp.address where street_typ is not null and street_typ != 'WEST'";
		$r = $this->runQuery($s);
		while($row = oci_fetch_row($r)){
			array_push($this->rtypes,$row[0]);
		}
		oci_free_statement($r);
		//Setup for two elements
		if($selements == 2){
			$this->street = $this->address_1[1];	
		}else{
			// Need to do some work if we have more than 2 elements in the street variable
			//First lets check for a suffix
			if($this->compareSuffix($this->address_1[1]) == 1){
				$ele = explode('.',$this->address_1[1]);
				$this->num_suffix = implode($ele);
			}else $this->street = $this->address_1[1];
			
			//Now lets run through elements 3 through N to assemble the address

			for($x = 2; $x<count($this->address_1); $x++){
				if($this->compareStype($this->address_1[$x]) == 1){
					$this->street_type = $this->address_1[$x];
				}else{ 
					if(!isset($this->street) or $this->street == '')$this->street = $this->address_1[$x];
					else $this->street .= ' '.$this->address_1[$x];
				}//If its not a street type it is part of the address
			}				
		}
		return $this->validateAddress();
	}
}//End of class


?>
