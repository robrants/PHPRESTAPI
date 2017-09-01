<?php
class ETI_Start_Bal_Push extends myOracle{
	//private $csv_file;
	private $csv;
	private $commercial = array(0 => array('gpid' => 'BOX ELDER COUNT', 'eticustid' => '0100002','etilocid' => 'D000013'), 
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
								18 => array('gpid' => 'WEBERBASIN', 'eticustid' => '0100026','etilocid' => 'D000037'),
							   	19 => array('gpid' => 'LINDON CITY', 'eticustid' => '0000003','etilocid' => 'D000003'),
								20 => array('gpid' => 'MURRAY CITY', 'eticustid' => '0000005','etilocid' => 'D000005'),
								21 => array('gpid' => 'TREMONTON CITY', 'eticustid' => '0000011','etilocid' => 'D000011'),
								22 => array('gpid' => 'SYRINGA', 'eticustid' => '0001014','etilocid' => 'SP00014'),
								23 => array('gpid' => 'TELESPHERE', 'eticustid' => '0001015','etilocid' => '0001015'),
								24 => array('gpid' => 'SUMMIT', 'eticustid' => '0001012','etilocid' => 'SP00012'),
								25 => array('gpid' => 'YIPTEL', 'eticustid' => '0001020','etilocid' => 'SP00020'),
								26 => array('gpid' => 'BEEHIVECOMM', 'eticustid' => '0001002','etilocid' => 'SP00002'),
								27 => array('gpid' => 'BEELINE', 'eticustid' => '0100001','etilocid' => 'D000012'),
								28 => array('gpid' => 'INTEGRA', 'eticustid' => '0001008','etilocid' => 'SP00008'),
								29 => array('gpid' => 'SUMOFIBER', 'eticustid' => '0001013','etilocid' => 'SP00013'),
								30 => array('gpid' => 'VIVINT', 'eticustid' => '0100025','etilocid' => 'D000036'),
								31 => array('gpid' => 'CENTRACOM', 'eticustid' => '0001004','etilocid' => 'SP00004'),
								32 => array('gpid' => 'PAETEC', 'eticustid' => '0001018','etilocid' => 'SP00018'),
								33 => array('gpid' => 'UHC', 'eticustid' => '0100021','etilocid' => 'D000032'),
								34 => array('gpid' => 'WEST VALLEY CIT', 'eticustid' => '0000007','etilocid' => 'D000007'),
								35 => array('gpid' => 'BRIGHAM.NET', 'eticustid' => '0001003','etilocid' => 'SP00003'),
								36 => array('gpid' => 'CENTERVILLE CIT', 'eticustid' => '0000001','etilocid' => 'D000001'),
								37 => array('gpid' => 'MIDVALE CITY', 'eticustid' => '0000004','etilocid' => 'D000004'),
								38 => array('gpid' => 'LAYTON CITY', 'eticustid' => '0000002','etilocid' => 'D000002'),
								39 => array('gpid' => 'PAYSON CITY', 'eticustid' => '0000009','etilocid' => 'D000009'),
								40 => array('gpid' => 'UTAH BROADBAND', 'eticustid' => '0100017','etilocid' => 'D000028'),
								41 => array('gpid' => 'SENAWAVE', 'eticustid' => '0001011','etilocid' => 'SP00011'),
								42 => array('gpid' => 'UEN', 'eticustid' => '0100019','etilocid' => 'D000030'),
								43 => array('gpid' => 'UVU', 'eticustid' => '0100023','etilocid' => 'D000034'),
								44 => array('gpid' => 'YONDOO', 'eticustid' => '0001021','etilocid' => 'SP00021'),
								45 => array('gpid' => 'FIBERNET', 'eticustid' => '0001005','etilocid' => 'SP00005'),
								46 => array('gpid' => 'FIRST DIGITAL', 'eticustid' => '0001006','etilocid' => 'SP00006'),
								47 => array('gpid' => 'INFOWEST', 'eticustid' => '0001007','etilocid' => 'SP00007'),
								48 => array('gpid' => 'MOZY', 'eticustid' => '0100010','etilocid' => 'D000021'),
								49 => array('gpid' => 'OREM CITY', 'eticustid' => '0000006','etilocid' => 'D000006'),
								50 => array('gpid' => 'RIGIDTECH', 'eticustid' => '0001010','etilocid' => 'SP00010'),
								51 => array('gpid' => 'XMISSION', 'eticustid' => '0001019','etilocid' => 'SP00019'),
								52 => array('gpid' => 'VERACITY', 'eticustid' => '0001016','etilocid' => 'SP00016'),
								53 => array('gpid' => 'VOONAMI', 'eticustid' => '0001017','etilocid' => 'SP00017'),
								54 => array('gpid' => '1WIRE', 'eticustid' => '0001001','etilocid' => 'SP00001'),
								55 => array('gpid' => 'NETPRO', 'eticustid' => '0001009','etilocid' => 'SP00009'),
								56 => array('gpid' => 'UIT', 'eticustid' => '0100022','etilocid' => 'D000033'));
	private $chargeCodes = array('ZZBBLO','ZZBBLE','ZZBBSP');
	private $transformed = array();	
	private $insert = "insert into blackr.startbal (eticustid,gpcustid,doctype,bbdd,bbd,trxamt,bb,chargecode,desc) values(";
	public function __construct($file){
		parent::__construct();
		//build out the CSV file from given file
		$csv = array_map('str_getcsv', file($file));
    	array_walk($csv, function(&$a) use ($csv) {
      		$a = array_combine($csv[0], $a);
    	});
  		array_shift($csv);
		$this->csv = $csv;
	}
	
	private function validGPCustCheck($GPID){
		$GPID = trim($GPID);
		$custsql = "select count(1) from blackr.custmerge where custudf2 = '".$GPID."'";
		$cueSQL = "select count(1)
				from 
					(select address_id,last_name,cue_type from sm.cue_tracker where add_id_status = 'Active') c,
					(select distinct address_id from sm.service s where service_end_date is null) s
				where
					c.address_id = s.address_id and c.last_name is not null and
					c.CUE_TYPE in ('L-MtM',
					'LTO - 2 Year',
					'RMM',
					'L- PB 2 Yr',
					'R2Y',
					'Lease - 1 Year',
					'L- NB 2 Yr',
					'R1Y',
					'Lease - 2 Year') and				
				last_name = '".$GPID."'";
		
		
		$r = $this->runQuery($custsql);	
		$row = oci_fetch_array($r);
		oci_free_statement($r);
		if($row[0] == 1){
			$r2 = $this->runQuery($cueSQL);
			$Cuerow = oci_fetch_row($r2);
			oci_free_statement($r2);
			return $Cuerow[0];
		}else return 0;
		//error_log('Is it valid: '.$row[0],0);
		//return $row[0];		
	}
	private function comCheck($rec){
		$com = $this->commercial;
		$tmp = array();
		$gpcustid = trim($rec['GPCUSTID']);
		array_walk($com,function(&$a,$gpcustid) use($com,$gpcustid,&$tmp,$rec){
				if(trim($a['gpid']) == trim($gpcustid)){
					$tmp['BBMISTYPE'] = 'ZZBBSP';
					$tmp['GPCUSTID'] = $a['gpid'];
					$tmp['ETICUSTID'] = $a['eticustid'];
					$tmp['LOCATIONID'] = $a['etilocid'];
					$tmp['BBTRANSDESC'] = $rec['BBTransDesc'];
					$tmp['BBAMOUNT'] = $rec['BBAmount'];
					$tmp['BBD'] = $rec['BBDate'];
					$tmp['BBDD'] = $rec['BBDDate'];
				}
			});
		if(count($tmp) > 0) return $tmp;
		else return 0;
	}
	private function cityCheck($rec){
		$tmp = array();
		$gpuser = trim($rec['GPCUSTID']);
		error_log($gpuser.'end',0);
		
		$city = array(0 => array('gpid' => 'UIACENTERVILLE', 'custid' => '0000001', 'locid' => 'CUE0001'),
					  1 => array('gpid' => 'UIALAYTON', 'custid' => '0000002', 'locid' => 'CUE0002'),
					  2 => array('gpid' => 'UIALINDON', 'custid' => '0000003', 'locid' => 'CUE0003'),
					  3 => array('gpid' => 'UIAMIDVALE', 'custid' => '0000004', 'locid' => 'CUE0004'),
					  4 => array('gpid' => 'UIAMURRAY', 'custid' => '0000005', 'locid' => 'CUE0005'),
					  5 => array('gpid' => 'UIAOREM', 'custid' => '0000006', 'locid' => 'CUE0006'),
					  6 => array('gpid' => 'UIAWESTVALLEY', 'custid' => '0000007', 'locid' => 'CUE0007'));
		$tmp = array();
		
		array_walk($city,function($a,$gpuser) use($city,$gpuser,$rec,&$tmp){
			if(trim($gpuser) == trim($a['gpid'])){
					error_log('Match Found',0);
					$tmp['BBMISTYPE'] = 'ZZBBLO';
					$tmp['GPCUSTID'] = $a['gpid'];
					$tmp['ETICUSTID'] = $a['custid'];
					$tmp['LOCATIONID'] = $a['locid'];
					$tmp['BBTRANSDESC'] = $rec['BBTransDesc'];
					$tmp['BBAMOUNT'] = $rec['BBAmount'];
					$tmp['BBD'] = $rec['BBDate'];
					$tmp['BBDD'] = $rec['BBDDate'];
				print_r($tmp);
				echo '<BR>';
			}
		});		
		if(count($tmp) > 0) return $tmp;
		return 0;
	}
	private function buildBBCustinfo($GPID){
		$GPID = trim($GPID);
		echo 'checking for '.$GPID.'<BR>';
		$sql = "select customerid,custudf1 locationid from blackr.custmerge where custudf2 = '".trim($GPID)."'";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_row($r)){			
			$rec = array('ETICUSTID' => $row[0],
						'LOCATIONID'	=> $row[1]);
		}
		if(isset($rec)){
			return $rec;
		}else return false;
		
	}
	
	private function writeFailedLineItems($GP){
		$GP['GPCUSTID'] = trim($GP['GPCUSTID']);
		echo 'GP Rec is <br>';
		echo $GP['GPCUSTID'].'++<br>';
		//print_r($GP);
		echo '<Br>';
		$insert = "insert into blackr.failedBB (GPCUSTID,BBDD,BBD,BB,DESCR)"; 
		$insert .= "values('".$GP['GPCUSTID']."','".$GP['BBDDate']."','".$GP['BBDate']."',".$GP['BBAmount'].",'".$GP['BBTransDesc']."')";
		//echo $insert.'<br>';
		return $this->runInsert($insert);
	}
	
	private function validateChargeCode($GPID){
		$GPID = trim($GPID);
		$sql = "select  
					case when cue_type in('10Y',
										'120 Months',
										'20Y',
										'240 Months') then
											'ZZBBLO'
										else 'ZZBBLE'
					end Chargecode	
				from sm.cue_tracker where
					cue_type in
						('10Y',
						'120 Months',
						'20Y',
						'240 Months', 
						'L-MtM',
						'LTO - 2 Year',
						'RMM',
						'L- PB 2 Yr',
						'R2Y',
						'Lease - 1 Year',
						'L- NB 2 Yr',
						'R1Y',
						'Lease - 2 Year') and last_name = '".trim($GPID)."'";
		$r = $this->runQuery($sql);
		$chargecode = oci_fetch_row($r);
		oci_free_statement($r);
		return $chargecode[0];		
	}
	private function csvTransform(){		
		error_log('Starting Transformation',0);
		foreach($this->csv as $rec){		
			//Check for Commercial			
			/*if($LineItem = $this->comCheck($rec) != 0) {array_push($this->transformed,$LineItem);
			error_log('Commerical Found',0);													   
			//Check for City
			}elseif($LineItem = $this->cityCheck($rec) != 0){ 
				error_log('City Customer Found',0);
				array_push($this->transformed,$LineItem);				
			}*/
			
			$LineItem = $this->comCheck($rec);
			if(!is_array($LineItem)){
				$LineItem = $this->cityCheck($rec);				
				if(!is_array($LineItem)){
					$LineItem = $this->checkService($rec);
					if(!is_array($LineItem)){
						if($check = $this->validGPCustCheck(trim($rec['GPCUSTID'])) == 1){
							error_log('Doing Transformation based on Cue_type for '.$rec['GPCUSTID'],0);
							$LineItem = $this->buildBBCustinfo(trim($rec['GPCUSTID']));
							$LineItem['BBMISTYPE'] = $this->validateChargeCode(trim($rec['GPCUSTID']));
							$LineItem['BBTRANSDESC'] = $rec['BBTransDesc'];
							$LineItem['BBAMOUNT'] = $rec['BBAmount'];
							$LineItem['BBD'] = $rec['BBDate'];
							$LineItem['BBDD'] = $rec['BBDDate'];
							$LineItem['GPCUSTID'] = trim($rec['GPCUSTID']);
							array_push($this->transformed,$LineItem);
						}else{
							$this->writeFailedLineItems($rec);
						}
					}else{
						array_push($this->transformed,$LineItem);
					}
				}else{
					array_push($this->transformed,$LineItem);
				}
				
			}else {				
				array_push($this->transformed,$LineItem);				
			}			
		}
		return true;
	}
	
	private function checkService($rec){
		preg_match('/SP U/',trim($rec['BBTransDesc']),$match);
		//error_log($match);
		if(count($match) > 0){
			$LineItem['BBMISTYPE'] = 'ZZBBSP';
			$LineItem['BBTRANSDESC'] = $rec['BBTransDesc'];
			$LineItem['BBAMOUNT'] = $rec['BBAmount'];
			$LineItem['BBD'] = $rec['BBDate'];
			$LineItem['BBDD'] = $rec['BBDDate'];
			$LineItem['GPCUSTID'] = trim($rec['GPCUSTID']);
			return $LineItem;
		}
		return 0;
	}
	
	private function csvLoad(){
		$insert = 'insert into blackr.startbal (ETICUSTID,LOCATIONID,GPCUSTID,DOCTYPE,BBDD,BBD,BB,DESCR) values(';
		$trans = $this->transformed;
		if(array_walk($trans,function(&$a) use($trans,$insert){
			$i = $insert;
			$i .= "'".$a['ETICUSTID']."','".$a['LOCATIONID']."','".trim($a['GPCUSTID'])."','".$a['BBMISTYPE']."','".$a['BBDD']."','".$a['BBD']."',".$a['BBAMOUNT'].",'".$a['BBTRANSDESC']."')";
			$r = $this->runInsert($i);
			if(!$r) return false;			
		})) return true;
	}
	
	public function runETL(){
		//return $this->csvTransform();
		if($this->csvTransform()){
			if($this->csvLoad()){
				return true;
			}
			return false;
		}
		return false;
	}
}
?>