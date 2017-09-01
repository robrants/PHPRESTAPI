<?php
const PATH = '/var/www/mu/Applications/';
const APP = PATH.'SNMPWORKERS/';
include_once PATH.'Common/myOracle.php';
include_once PATH.'Common/Utopiamysql.php';
include_once PATH.'Common/sysUtils.php';
include_once PATH.'Common/traits/crudBuilder.php';
include_once PATH.'DHCP/data/UtopiaDHCP.php';
include_once APP.'data/snmpData.php';
include_once APP.'control/snmpDriver.php';
include_once APP.'data/ONT_Data.php';
include_once APP.'control/ONT_ZHONE.php';



$AUDIT = new ONT_ZHONE();
if($check = $AUDIT->RunAuditONTs() == 1) echo 'All Done No crashy';
else echo 'BOOM No Go boss!';
?>