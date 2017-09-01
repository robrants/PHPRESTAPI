<?php
$commercial = array(0 => array('gpid' => 'BOX ELDER COUNT', 'eticustid' => '0100002','etilocid' => 'D000013'), 
								1 => array('gpid' => 'HEXCEL', 'eticustid' => '0100003','etilocid' => 'D000014'),
								2 => array('gpid' => 'HONEYVILLE', 'eticustid' => '0100004','etilocid' => 'D000015'),
								3 => array('gpid' => 'JORDAN EDWARDS', 'eticustid' => '0100005','etilocid' => 'D000016'),
								4 => array('gpid' => 'KAYS CREEK', 'eticustid' => '0100006','etilocid' => 'D000017'),
								5 => array('gpid' => 'LANDMARKHOSPITA', 'eticustid' => '0100007','etilocid' => 'D000018'),
								6 => array('gpid' => 'LDS SEMINARIES', 'eticustid' => '0100008','etilocid' => 'D000019'),
								7 => array('gpid' => 'MAVERIK CENTER', 'eticustid' => '0100037','etilocid' => 'D000038'),
								8 => array('gpid' => 'MIDVALLEY', 'eticustid' => '0100009','etilocid' => 'D000020'),
								9 => array('gpid' => 'ORBITAL ATK', 'eticustid' => '0100012','etilocid' => 'D000023'),
								10 => array('gpid' => 'OVERSTOCK', 'eticustid' => '0100013','etilocid' => 'D000024'),
								11 => array('gpid' => 'PERRY CITY', 'eticustid' => '0000010','etilocid' => 'D000010'),
								12 => array('gpid' => 'SHRIVER HS', 'eticustid' => '0100014','etilocid' => 'D000025'),
								13 => array('gpid' => 'TREMONTONFAIR', 'eticustid' => '0100015','etilocid' => 'D000026'),
								14 => array('gpid' => 'UCAN', 'eticustid' => '0100016','etilocid' => 'D000027'),
								15 => array('gpid' => 'UTA', 'eticustid' => '0100020','etilocid' => 'D000031'),
								16 => array('gpid' => 'UTAH CO AUDITOR', 'eticustid' => '0100018','etilocid' => 'D000029'),
								17 => array('gpid' => 'UVUBUSINESS', 'eticustid' => '0100024','etilocid' => 'D000035'),
								18 => array('gpid' => 'WEBERBASIN', 'eticustid' => '0100026','etilocid' => 'D000037'));

array_walk($commercial, function(&$a) use ($commercial){
	echo $a['gpid'].'<br>';
});
?>