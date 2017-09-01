<?php
include_once('Common/myOracle.php');
include_once('Common/assignIfindex.php');

$work = new assignIfindex();

$check = $work->pullPortsAdid();

echo $check;
?>