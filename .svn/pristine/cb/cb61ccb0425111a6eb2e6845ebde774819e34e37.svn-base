<?php
	include_once 'Common/simpleIntrestChart.php';
	include_once 'Common/myOracle.php';
	if(isset($_GET['P']) && isset($_GET['i']) && isset($_GET['t']) && isset($_GET['pmnt'])){
		$C = new simpleIntrestChart($_GET['P'],$_GET['i'],$_GET['t'],$_GET['pmnt']);
		$C->buildPaymentTable();
	}else echo 'not enough values passed';
 ?>