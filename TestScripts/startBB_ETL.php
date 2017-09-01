<?php
include_once('../Common/myOracle.php');
include_once('../ETI/data/ETI_Start_Bal_Push.php');
$filePath = 'LoadFiles/BBtrans.csv';
$TRANS = new ETI_start_Bal_Push($filePath);
echo $TRANS->runETL();
?>