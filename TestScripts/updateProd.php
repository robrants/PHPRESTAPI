<?php
include_once('../Common/myOracle.php');
include_once('../ETI/data/ETI_Product_Push.php');
$filePath = 'LoadFiles/productCatalog.csv';
$CUST = new ETI_Product_Push($filePath);
$ret = $CUST->runUpdate();
echo $ret;
?>