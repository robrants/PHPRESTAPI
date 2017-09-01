<?php

class sysUtils  {
	var $class = 'sysUtils';
	var $func = '';
	
	public function getOrgid(){
		$s = "select organization_id,short_name from sm.organization order by short_name";
		$conn = new myOracle();
		$r = $conn->runQuery($s);
		$o = array();
		while($row = oci_fetch_array($r)){
			$rec = array('org_id' 	=> $row['ORGANIZATION_ID'],
				'short_name' 	=>	$row['SHORT_NAME']);
			array_push($o,$rec);
		}
		return $o;
	}
	
	public function getIPfromADID($a){
		$this->func = 'getIPfromADID';
		$conn = new myOracle();
		$s = "select 	
					a.address_id,
					s.ip_address
				from 
					osp.address a,
					inv.switch s
				where 
					a.switch_id = s.switch_id and
					a.address_id = ".$a;
		$r = $conn->runQuery($s);
		if($r){
			$ret = oci_fetch_row($r);
			$ip = $ret[1];
			return $ip;
		}else {
			$ERROR = new UtopiaErrors();
			$e = $ERROR->catchError($this->class,$this->func,$s);
			if($e == -99) {
				$em = $ERROR->userErrorMessage(100); 
			}else{
					$em = $ERROR->userErrorMessage(-1);
			}
			return $em;
		};
	
	}
	
	public function ping($h){
		exec(sprintf('ping -c 1 -W 5 %s', escapeshellarg($h)), $res, $rval);
        return $rval === 0;	
	}

	public function PingAddress($a){

		$this->func='PingAddress';		
		$ip = $this->getIPfromADID($a);
		
		if ($ip == ''){ return 2;}
		$cmd = 'ping -qw 3 '.$ip;
		exec($cmd,$o);
		$d = split(",",$o[3]);
		$data = array();
		list($data['sent'], $garbage) = split(' ',$d[0],2);
		list($g1,$data['received'],$garbage) = split(' ',$d[1],3);
		$data['ploss'] = $d[2];
		return $data;						
	}
	
	public function PingAll($a){
		$ret = array();
		for($x=0; $x<count($a); $x++){
			if($a[$x]['status'] == 'IMPACTED'){
				$resp = $this->PingAddress($a[$x]['impacted']);
				$ret[$x]['address'] = $a[$x]['impacted'];			
				if ($resp['ploss'] == '100' || $resp == 2){
					$ret[$x]['test'] = 'FAIL';
				}else $ret[$x]['test'] = 'PASS';
			}
		}	
		return $ret;
	}
	public function truncdate($d){
		$date = split('T',$d);
		return $date[0];	
	}
	public function mergeDateTime($d,$h,$min,$meridian){
		$date = split('T',$d);
		if ($meridian === 'PM')$h+=12;
		$dateTime = $date[0]." ".$h.":".$min;
		return $dateTime;		
	}
	
	public function exportReport($report,$data,$header){
		
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$report);
		if($FILE = fopen('php://output', 'w')) {
			fputcsv($FILE,$header);
			foreach($data as $row){
				fputcsv($FILE,$row);
			}
		}else print "$report\n";
		fclose($FILE);
		/*$reportBook = new \PHPExcel();
		$reportBook->getProperties()
					->setCreator('Robert Black')
					->setTitle($title)
					->setLastModifiedBy('Robert Black')
					->setDescription($title);
		$reportSheet = $reportBook->getSheet(0);	
		$reportSheet->fromArray($header,'NULL','A2');
		$reportSheet->fromArray($data,'NULL','A3');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename='.$report);
		header('Cache-Control: max-age=0');
		$writter = PHPExcel_IOFactory::createWriter($reportBook,'Excel5');
		$writter->save('php://output');	
		*/
		return 1;
	}
	
	public function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}
	
	public function dateNow($f){
		
		$date = getdate();
		switch ($f){
		
			case 0:
				$now = $date['mon']."/".$date['mday']."/".$date['year'];
				break;
			case 1:
				$now = $date['mon']."/".$date['mday']."/".$date['year']." ".$date['hours'].":".$date['minutes'].":".$date['seconds'];
				break;
			case 2:
				$now = $date['month'];
				break;
			case 3:
				$now = $date['weekday'];
				break;
			case 4:
				$now = $date['year'];
				break;
			case 5:
				$now = $date['wday'];
				break;						
		}
			return $now;
	}
	
	public function backupTable($t){
		$s = 'select * from '.$t;
		$conn = new myOracle();
		$r = $conn->runQuery($s);
		$results = array();
		$rec = 0;
		while ($row = oci_fetch_row($r)){
			$reults[$rec] = implode(',',$row);
			$rec++;			
		}
		oci_free_statement($r);
		return $results;
	}
	
	public function getFootprint ($i){
		$s = 'select footprintid from osp.footprintsite where siteid ='.$i;
		$r = $this->runQuery($s);
		$footprint = oci_fetch_row($r);
		return $footprint[0];
	}
	
	public function formatMac($m,$f){
		$m = trim($m);
		if($f == 1){
			if (strlen($m) == 15){
				//cisco mac	
				$mac = implode(explode('.',$m));				
			}
			else if(strlen($m) == 17) {
				$mac = implode(explode("-",$m));
				$mac = implode(explode( ":", $mac ));
			} else $mac = $m;
		}else {
			if(strlen($m) == 12){
				//add colons
				$x=2;
				$mac = substr($m,0,2);
				while ($x <strlen($m)){
					$mac = $mac.':'.substr($m,$x,2);
					$x+=2;
				}
			} else if(strlen($m) == 15){
				//cisco mac					
				$tempMac = explode('.',$m);
				for($x=0; $x<3;$x++){
					for($y=1; $y < 4; $x++){
						if($x == 0 and $y == 1){
							$mac = substr($tempMac[$x],$y,2);
						} else $mac = $mac.":".$mac.substr($tempMac[$x],$y,2);
					}
				}
			}
			else if (strlen($m) == 17){
				$mac = implode(':',explode("-",$m));
				$mac = implode(':',explode( ":", $mac ));
				//$mac = implode(':',explode('-', $m ));			
			} else $mac = $m;
		
		}
		return $mac;
	}
	//Search the lookup table by the lookup_type_id and get the needed drop down options for a form
	public function getLookups($lid,$dbflag){
		if (isset($dbflag) == 'ORA') {
			$s = "select DROPVAL,DESCRIPTION from blackr.ticket_dropdown where dropdown_id = ".$lid;
			$conn = new myOracle();
			$r = $conn->runQuery($s);
			$output = array();
			while ($row = oci_fetch_array($r)){
				$rec = array('lid' 	=> $row['DROPVAL'],
							 'desc'	=> $row['DESCRIPTION']
				);
				array_push($output,$rec);
			}
			$conn = "";
		}else {
			$conn = new Utopiamysql('192.168.253.6','root','mysqlr00t','tools');
			$s =  "select content from i_quicklists where qname = '".$lid."'";
			$r = $conn->runMyQuery($s);
			$list = mysqli_fetch_row($r);
			$listarray = explode('\n',$list);
			for($x = 0;$x<count($listarray);$x++){
				$row = explode(',',$listarray[$x]);
				$l = array('lid'	=> $row[0],
							'desc'	=> $row[1]);
				array_push($output,$l);
			}
			$conn="";
		}
		return $output;
	}
	//End Class	
}
?>