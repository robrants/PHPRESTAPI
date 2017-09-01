<?php
error_reporting(E_WARNING);
ini_set('display_errors',1);
$ip =$_GET['ip'];
$SNMP = new SNMP(SNMP::VERSION_2c,$ip,'ut0p1a5nmp');
$results = $SNMP->get('iso.3.6.1.2.1.2.2.1.2.1');
print_r($results);
$SNMP->close();

?>