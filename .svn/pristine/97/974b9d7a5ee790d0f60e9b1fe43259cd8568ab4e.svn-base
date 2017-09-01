<?php

class myOracle {
	var $ora_user = 'blackrdev';
	var $ora_pass = 'or4cl3';
	var $ora_host = '172.16.1.38'; 
	var $ora_db = 'poss1';
	var $conn;
	public function __construct(){
		$this->conn = oci_connect($this->ora_user,$this->ora_pass,'//'.$this->ora_host.'/'.$this->ora_db);
	
	}
	public function __destruct(){
		oci_close($this->conn);
	}


	public function prep_oracle_string($string){

        $string = str_replace("'", "'||chr(39)||'", $string);

        return $string;
}



	public function runQuery($statement){	
	if (!$this->conn) {
		$e = oci_error();
		error_log("No Connection\n".$e['message'],0);
		return -2;
		//trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}
	
	
	// Prepare the statement
	try{
		if (!$stid = oci_parse($this->conn, $statement)){;
		
			throw new UtopiaErrors(__CLASS__,__METHOD__,__LINE__);
		}
	}catch (UtopiaErrors $e){
		$e->catcherror();	
		return -1;		
	}
	
	

	// Perform the logic of the query
	if(!$r = oci_execute($stid)){
		$e = oci_error();
		error_log($e['message'].' '.$statement,0);
	}

	return $stid;	
	}
	
	public function runInsert($statement){
		
		if (!$this->conn) {
			$e = oci_error();
			error_log($e['message'].' '.$statement."\n",0);
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
		}

	
		$flag = false;
	// Prepare the statement
		
			if(!$stid = oci_parse($this->conn, $statement)){
				error_log($e['message'].' '.$statement."\n",0);
			}else{
				// Perform the logic of the query
		
				if(!$r = oci_execute($stid)){			
					error_log($e['message'].' '.$statement."\n",0);
				}
				else{
					oci_commit($this->conn);
					$flag = true;
				}
			}
		
		
		
		oci_free_statement($stid);
		////oci_close($conn);	
		return $flag;
		
	}
	
}
?>