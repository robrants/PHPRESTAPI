<?php
class CustomerData extends myOracle{
	/*
		This class is used to build and manage Customer specific data such as name, phone, email, billing address, and status
	*/
	private $custInfo = array();
	public function __construct(){
		parent::__construct();
	}
	
	private function addCustomer($input){
		$insert = "insert into blackr.customer (cusid,date_Created,status,firstname,lastname,phone1,phone2,email) values(blackr.seq_custnum.nextval,sysdate,'pending',";
		if(count($input) >= 4) { //Ensure we have all required fields
			$insert .= "'".$input['firstname']."','".$input['lastname']."','".$input['phone1']."',";
			if(isset($input['phone2'])) $insert .= "'".$input['phone2']."',";
			else $insert .= "'N/A',";
			$insert .= "'".$input['email']."')";
			return $this->runInsert($insert);
		}else return -1;
	}
	
	private function addBIllingAdid($input){
		$insert = "insert into blackr.billing_address (bill_adid,Custid,address1,address2,city,state,zip) values(blackr.seq_billadid.nextval,)";
	}
}
?>