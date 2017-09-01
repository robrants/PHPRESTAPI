<?php
 include_once '../Common/myOracle.php';

 	$DB = new myOracle();
 	$insert = 'Insert into blackr.product_catalog (productid,isp,description,unit_price,charge_code) values(blackr.seq_product.nextval';
 	$file = 'prods.csv';
 	$csv = array_map('str_getcsv', file($file));
    array_walk($csv, function(&$a) use ($csv) {
      		$a = array_combine($csv[0], $a);
    });
  	array_shift($csv); # remove column header
	//print_r($csv);
	foreach ($csv as $val){		
		$i = $insert.",'".$val['SERVICE_PROVIDER']."','".$val['DESCRIPTION']."',".$val['UNIT_AMOUNT'].",'".$val['CHARGE_CODE']."')";
		if($r=$DB->runInsert($i)){
			unset($i);
		}else {
			echo $i;
			exit();}
	}

	echo 'DONE';
	
?>