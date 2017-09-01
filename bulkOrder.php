<?php
 include_once 'Common/myOracle.php';
 $adid = array(50273,
50334,
49122,
49067,
49758,
50335,
49305,
50278,
49691,
50277,
50280,
50281,
50053,
49400,
50264,
49321,
49682,
50267,
50265,
50260,
50263,
50261,
50257,
50258,
49505,
50262,
50259,
49177,
50248,
49979,
49454,
50255,
50252,
49957,
50286,
49894,
49885,
50285,
49621,
50282,
49140,
50246,
50247,
50245,
49624,
50243,
50242,
49051,
49678,
50295,
49945,
50292,
49972,
49145,
50236,
50239,
50238,
50233,
50235,
49105,
50302,
50009,
50303,
49486,
49716,
50226,
50229,
50225,
50230,
49693,
50312,
50307,
50306,
50308,
49416,
50309,
50318,
50314,
50319,
49865,
49086,
49152,
50317,
50320,
49413,
50321,
50323,
49948,
50326,
49112);

 $DB = new myOracle();
 $subscriber_id = 47298;
 $order_id;
 $order_item;
 $sup_id;
 $order_header = array('subscriber_id' => 47298, 'contact_fname' => 'Jason','contact_lname' => 'Sucher', 'contact_phone1' =>'801-235-7368');
 
$order = "insert into sm.order_header (order_header_id,address_id,subscriber_id,contact_fname,contact_lname,contact_phone1,order_type,status,status_date,created_by,date_created) values(";
 
$supplement = "insert into sm.ORDER_SUPPLEMENT (order_supplement_id,order_header_id,created_by,date_created) values(";

$item = "insert into sm.order_item (order_item_id,order_header_id,supplement_added,product_id,quantity,description,status,status_date,created_by,date_created) values(";

 foreach($adid as $id){
	 //First get the next ids for each table
	 $sql = "select sm.seq_order_header.nextval from dual";
	 $sql1 = "select sm.seq_order_item.nextval from dual";
	 $sql2 = "select sm.seq_order_supplement.nextval from dual";
	 $r = $DB->runQuery($sql);
	 $order_id = oci_fetch_row($r);
	 oci_free_statement($r);
	 $r = $DB->runQuery($sql2);
	 $sup_id = oci_fetch_row($r);
	 oci_free_statement($r);
	 $r = $DB->runQuery($sql1);
	 $order_item = oci_fetch_row($r);
	 oci_free_statement($r);
	 $orderSql = $order;
	 $orderSql .= $order_id[0].",".$id.",".$subscriber_id.",'".$order_header['contact_fname']."','".$order_header['contact_lname']."','".$order_header['contact_phone1']."',1,1,sysdate,6150,sysdate)";
	 $supplementSql = $supplement;
	 $supplementSql .= $sup_id[0].",".$order_id[0].",6150,sysdate)";
	 $itemSql = $item;
	 $itemSql .= $order_item[0].",".$order_id[0].",".$sup_id[0].",19313,1,'50/50 Bulk Product - 5 year',1,sysdate,6150,sysdate)";
	 
	 if($r = $DB->runInsert($orderSql)){
		 if($r = $DB->runInsert($supplementSql)){
			 if($r = $DB->runInsert($itemSql))continue;
			 else exit(-1);
		 } else exit(-1);
	 } else exit(-1);
 }
echo 1;
?>