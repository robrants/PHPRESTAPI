<?php
include '../Common/myOracle.php';
include '../Common/UtopiaErrors.php';
$DB = new myOracle();
$sql = "select switch_id,switch1_serial,switch1_mac,switch2_serial,switch2_mac,switch3_serial,switch3_mac,switch4_serial,switch4_mac,switch5_serial,switch5_mac,switch6_serial,switch6_mac,switch7_serial,switch7_mac,switch8_serial,switch8_mac from osp.ads_stack sk, inv.switch m
where m.network_name = sk.network_name";
$stack = array();
$x = 0;
$results = $DB->runQuery($sql);
while($row = oci_fetch_array($results)){
	
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH1_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH1_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH2_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH2_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH3_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH3_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH4_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH4_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH5_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH5_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH6_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH6_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH7_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH7_MAC'];
	$x++;
	$stack[$x]['switch_id'] = $row['SWITCH_ID'];
	$stack[$x]['serial'] = $row['SWITCH8_SERIAL'];
	$stack[$x]['mac'] = $row['SWITCH8_MAC'];
	$x++;
}

foreach($stack as $rec){
	$insert = "insert into blackr.adsstack (stackid,switch_id,serial,mac,date_created,created_by) values(blackr.seq_adsstack.nextval,".$rec['switch_id'].",'".$rec['serial']."','".$rec['mac']."',sysdate,6150)";
	$r = $DB->runInsert($insert);
	if(!$r) exit;
}
