<?php

class firmWareData extends myOracle{

	
	public function __construct(){
		parent::__construct();
	}
	
	public function pullCompletedUpgrades($rows,$start){
		if($start == 'sysdate') $con = ' and trunc(stamp) = trunc(sysdate)';
		else $con = " and trunc(stamp) >= (sysdate - ".$start.")";
		$sql = "select * from blackr.applog where class = 'firmWare'  and rownum <= ".$rows.$con.' order by stamp desc';
		$r = $this->runQuery($sql);
		$data = array();
		while($row = oci_fetch_array($r)){
			$rec = array('stamp' 	=> $row['STAMP'],
						'class'		=> $row['CLASS'],
						'method'	=> $row['METHOD'],
						'msg'		=> $row['DATA']);
			array_push($data,$rec);
		}
		return $data;
	}
	
	public function getFirmwareFootprint($f,$d,$p){
		$orderby = 'order by o.short_name';
		$prod = " and p.description like '%".$p."%'";
		//Disable for testing of DEV001 devices
		$getMacs = "select 
						distinct a1.address_id, o.short_name, a.attribute2 mac, m.attribute2 model
					from
						inv.asset a,
						inv.asset_status a1,
						inv.model m,
						sm.product p,
						sm.subscriber sub,
						sm.organization o,
						(select distinct address_id, product_id,subscriber_id from SM.SERVICE where service_end_date is null) s,
						(select distinct address_id from osp.address where footprint_id = '".$f."') ad
					where
						a.asset_id = a1.asset_id and
						a.MODEL_ID = m.MODEL_ID and
						a1.address_id = ad.address_id and
						a1.address_id = s.address_id and
						s.subscriber_id = sub.subscriber_id and
						sub.service_provider_id = o.organization_id and
						s.product_id = p.product_id and
						a1. status = 1 and
						a1.status_date = (select max(a2.status_date) from inv.asset_status a2 where a2.address_id = a1.address_id and a2.status = 1)					
						and m.attribute1 = '".$d."'";
		if($p != 0){$getMacs .= $prod;}
		$getMacs .= $orderby; 
		//Dev001 query
		/*$getMacs = "select distinct a1.address_id, a.attribute2 mac, m.attribute2 model from inv.asset a,
					(select * from inv.asset_status where address_id in (
					select address_id from osp.address where footprint_id = 'DEV001')) a1,
					inv.model m
					where
					a.asset_id = a1.asset_id and
					m.model_id = a.model_id";*/
		
		$r = $this->runQuery($getMacs);
		$Zhones = array();
		while($row = oci_fetch_array($r)){
	
			$rec = array('adid' => $row['ADDRESS_ID'],
						'mac'	=> $row['MAC'],
						'model'	=> $row['MODEL'],
						'ISP'	=> $row['SHORT_NAME']);
			array_push($Zhones,$rec);
		}
		oci_free_statement($r);
		return $Zhones;
	}
	
	public function getfirmwareAll($start,$end,$d){
		$getMacs = "select 
						distinct a1.address_id, o.short_name,a.attribute2 mac, m.attribute2 model,ad.footprint_id
					from
						inv.asset a,
						inv.asset_status a1,
						inv.model m,						
						sm.subscriber sub,
						sm.organization o,
						(select distinct address_id, product_id,subscriber_id from SM.SERVICE where service_end_date is null) s,
						(select distinct address_id,footprint_id from osp.address) ad
					where
						a.asset_id = a1.asset_id and
						a.MODEL_ID = m.MODEL_ID and
						a1.address_id = ad.address_id and
						a1.address_id = s.address_id and						
						sub.subscriber_id = s.subscriber_id and
						o.organization_id = sub.service_provider_id and
						a1. status = 1 and
						a1.status_date = (select max(a2.status_date) from inv.asset_status a2 where a2.address_id = a1.address_id and a2.status = 1)						
						and m.attribute1 = '".$d."'
						and rownum between ".$start." and ".$end;
		
		$r = $this->runQuery($getMacs);
		$Zhones = array();
		while($row = oci_fetch_array($r)){
	
			$rec = array('adid' 		=> $row['ADDRESS_ID'],
						'mac'			=> $row['MAC'],
						'model'			=> $row['MODEL'],
						'footprint' 	=> $row['FOOTPRINT_ID'],
						//'ISP'			=> $row['SHORT_NAME']
						);
			array_push($Zhones,$rec);
		}
		oci_free_statement($r);
		return $Zhones;
	}
	
	public function getRelease($d){
		$Frelease = array();
		$rsql = "select f.version,f.url,m.attribute2 model 
				from blackr.firmware f, inv.model m 
				where
					f.model_id = m.model_id and 
					m.ATTRIBUTE1 = '".$d."'";
		$r = $this->runQuery($rsql);
		while($row = oci_fetch_array($r)){
			$rec = array('version' 	=> $row['VERSION'],
						'url'		=> $row['URL'],
						'model'		=> $row['MODEL']);
			array_push($Frelease,$rec);			
		}
		return $Frelease;
	}
	
	public function buildONT($ONT){
		//Pass in IP and model (Attribute2 from inv.model table)
		//echo 1;
		$sql = "select * from BLACKR.FIRMWARE fm,
					inv.model m
				where 
					m.model_id = fm.model_id and
					m.attribute2 = '".$ONT['model']."' and
					fm.version = '".$ONT['upgrade']."'";
		$r = $this->runQuery($sql);
		$rec = oci_fetch_array($r);		
		$ONT['URL'] = $rec['URL'];
		//echo json_encode($ONT);
		return $ONT;
	}
	
	public function getResZones(){
		$data = array();
		$sql = "select q1.address_id,q1.description, q.mac
				from
					(select m.attribute1,a1.address_id,a.ATTRIBUTE2 mac from
						inv.asset a,
						inv.asset_status a1,
						inv.model m
					where
						a.asset_id = a1.asset_id and
						a.MODEL_ID = m.model_id and
						m.asset_type = 2 and
						m.attribute1 = 'Zhone' and
						a1.status = 1 and
						a1.STATUS_DATE = (select max(status_date) from inv.asset_status a2 where a2.status = 1 and a2.address_id = a1.address_id)) q,
					(select s.address_id, p.description from 
						sm.service s,
						sm.product p,
						sm.product_category pc,
						SM.CATEGORY c
					where
						s.product_id = p.product_id and
						p.product_id = pc.product_id and
						pc.category_id = c.category_id and
						p.product_type = 5 and
						p.product_id not in (17882,17883,17884,17885,17886,17894,17898,17910,17911,17912,17913,19026,19035,15549,15558,
						15559,15560,15552,15556,15548,15550,15551,15553,15555,15557,15561,15562,15603,19082) and
						p.DESCRIPTION not like('%(LEGACY)') and
						p.description not like ('%Legacy%') and
						c.lookup_id = 1 and
						s.service_end_date is null) q1
				where
					q.address_id = q1.address_id and
					rownum < 10";
		$r = $this->runQuery($sql);
		$count = 0;
		while($row = oci_fetch_array($r)){
			if($count< 10) $count++;
			else break;
			$rec = array('make' => $row['DESCRIPTION'],
						'adid'	=> $row['ADDRESS_ID'],
						'mac'	=> $row['MAC']);
			array_push($data,$rec);
		}
		return $data;
	}
}

?>