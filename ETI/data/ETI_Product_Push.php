<?php
class ETI_Product_Push extends myOracle{
	private $csv_file;
	private $csv;
	private $update = 'update blackr.product_catalog set';	
	private $insert = 'Insert into blackr.product_catalog (productid,isp,description,unit_price,charge_code) values(blackr.seq_product.nextval';
	
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
	
	private function update_Product(){		
		foreach ($this->csv as $val){
			$u = $this->update;
			$u .= " SERVICE_ID = '".trim($val['SERVICE_ID'])."' where upper(ISP) = upper('".$val['ISP']."') and DESCRIPTION = '".$val['DESCRIPTION']."'";
			if($this->runInsert($u)) continue;
			//else exit;
		}
	}
	
	public function runUpdate(){
		if($this->update_Product()) return 1;
		else return -1;
	}
}
?>