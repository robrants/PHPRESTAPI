<?php
 include_once('../Common/myOracle.php');
$file = 'LoadFiles/Location_Exclusion_list.csv';
$exlusions = file($file);
$DB = new myOracle();
array_walk($exlusions,function($e) use($exlusions,$DB){
	$insert = "insert into blackr.locationexclusions values($e)";
	$r = $DB->runInsert($insert);
	oci_free_statement($r);
});

echo 'Done';
?>