<?php
include_once('../Common/myOracle.php');
include_once('../ETI/data/ETI_Customer_Push.php');
$filePath = 'LoadFiles/resCusts.csv';
$CUST = new ETI_Customer_Push($filePath);
$ret = $CUST->addCustomer();
echo $ret;
?>