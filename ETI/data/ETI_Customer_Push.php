<?php
class ETI_Customer_Push extends myOracle{
	private $csv_file;
	private $csv;	
	private $insert = 'insert into blackr.custmerge (customerid,individual,Title,FirstName,middlename,LastName,SIN,DateOfBirth,placeofwork,AdrCategory,address1,aptdesc,aptno,city,state,country,zip,ADRSCODE,CustomerClassID,PaymentTermID,phone1,phone2,phone3,fax,email,IPassword,TaxSchedule,ContactPerson,PrintCP,PenaltyCharged,DepositRequired,BilledCustomer,BudgetEligibility,CollectionExempt,PPP,CustUDF1,CustUDF2,CustUDF3,CUSTUDF4) values (';
	
	public function __construct($file){
		parent::__construct();
		//build out the CSV file from given file
		$csv = array_map('str_getcsv', file($file));
    	array_walk($csv, function(&$a) use ($csv) {
      		$a = array_combine($csv[0], $a);
    	});
  		array_shift($csv);
		$this->csv = $csv;
	}
	/*customerid,
	individual,
	Title,
	FirstName,
	middlename,
	LastName,
	SIN,
	DateOfBirth,
	placeofwork,
	AdrCategory,
	address1,
	aptdesc,
	aptno,
	UnitDesignator,
	city,
	state,
	country,
	zip,
	ADRSCODE,
	CustomerClassID,
	PaymentTermID,
	phone1,
	phone2,
	phone3,
	fax,
	email,
	IPassword,
	TaxSchedule,
	ContactPerson,
	PrintCP,
	PenaltyCharged,
	DepositRequired,
	BilledCustomer,
	BudgetEligibility,
	CollectionExempt,
	PPP,
	CustUDF1,
	CustUDF2,
	CustUDF3
	*/
	private function Insert_Customer(){		
		foreach ($this->csv as $val){
			$u = $this->insert;
			//$key = array_keys($val);
			//print_r($key);
			$u .= $val['CUSTOMERNUMBER'].",".
				$val['INDIVIDUAL'].",".
				$val['TITLE'].",'".
				$val['FIRSTNAME']."','".
				$val['MIDDLENAME']."','".
				$val['LASTNAME']."','".
				$val['SIN']."','".
				$val['DATEOFBIRTH']."','".
				$val['PLACEOFWORK']."',".
				$val['ADRCATEGORY'].",'".
				$val['ADDRESS1']."','".
				$val['APTDESC']."','".
				$val['APTNO']."','".
				$val['CITY']."','".
				$val['STATE']."','".
				$val['COUNTRY']."','".
				$val['ZIP']."','".
				$val['ADRSCODE']."','".
				$val['CUSTOMERCLASSID']."','".
				$val['PAYMENTTERMID']."','".
				$val['PHONE1']."','".
				$val['PHONE2']."','".
				$val['PHONE3']."','".
				$val['FAX']."','".
				$val['EMAIL']."','".
				$val['IPASSWORD']."','".
				$val['TAXSCHEDULE']."','".
				$val['CONTACTPERSON']."',".
				$val['PRINTCP'].",".
				$val['PENALTYCHARGED'].",'".
				$val['DEPOSITREQUIRED']."',".
				$val['BILLEDCUSTOMER'].",".
				$val['BUDGETELIGIBILITY'].",".
				$val['COLLECTIONEXEMPT'].",".
				$val['PPP'].",".
				$val['CUSTUDF1'].",'".
				$val['CUSTUDF2']."','".
				$val['CUSTUDF3']."','".
				$val['CUSTUDF4']."')";
			if($this->runInsert($u)) continue;
			else exit;
		}
	}
	
	public function addCustomer(){
		if($this->Insert_Customer()) return 1;
		else return -1;
	}
}
?>