<?php
class Utopiamysql {
	var $db	;
	var $dbuser;
	var $dbpass;
	var $host;
	var $myconn;
	
	public function __construct($h,$u,$p,$d){
		$this->db = $d;
		$this->dbpass = $p;
		$this->dbuser = $u;
		$this->host = $h;	
	}
	
	public function getConn(){
		$conn = $this->db."-".$this->dbpass."-".$this->dbuser."-".$this->host;
		return $conn;
	}
	
	public function runMyQuery($s){
		$myconn = mysqli_connect($this->host,$this->dbuser,$this->dbpass,$this->db);
		if(mysqli_errno($myconn)){
			return -1;	
		} else{
			if($results = $myconn->query($s))return $results;
			else return -1;
		}	
	}
}
?>