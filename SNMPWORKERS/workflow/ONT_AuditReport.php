<?php
const PATH = '/var/www/mu/Applications/Common/';
include_once PATH.'myOracle.php';
$sql1 = "select o.mac,o.address_id,m.ATTRIBUTE1||' '||m.ATTRIBUTE2 mm,o.uptime from blackr.ontdevice o, inv.model m where o.model_id = m.model_id";
$ONT = array();
$Output = array();
$DB = new myOracle();
$r = $DB->runQuery($sql1);
while($row = oci_fetch_array($r)){
	$rec = array('mac' => $row['MAC'],
				'adid' => $row['ADDRESS_ID'],
				'make' => $row['MM'],
				'uptime' => $row['UPTIME']
				);
	array_push($ONT,$rec);
}

array_walk($ONT, function(&$o) use($ONT,$DB){
	$sql2 = "select * from blackr.ontport where mac = '".$o['mac']."'";
	$r = $DB->runQuery($sql2);
	$o['ports'] = array();
	while($row = oci_fetch_array($r)){
		$rec = array('runDate' 	=> $row['Q_DATE'],
					'portNum' 	=> $row['PORTNUM'],
					'speed'		=> $row['SPEED'],
					'upDown' 	=> $row['UP_DOWN'],
					'vlan'		=> $row['VLAN_NUM'],
					'inb'		=> $row['INBYTES'],
					'outb'		=> $row['OUTBYTES']);
		array_push($o['ports'],$rec);
	}
});
echo json_encode($ONT);
?>
