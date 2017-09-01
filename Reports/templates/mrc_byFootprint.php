<?php
class mrc_byFootprint extends myOracle{
	private $custData = array();
	private $sysUtils;
	public function __construct(){
		parent::__construct();
		$this->sysUtils = new sysUtils();
	}
	
	private function getData(){
		$sql = "select 
					count(distinct b2.address_id) cnt,sum(b2.total+b2.billing_amount) as total,a.footprint_id 
				from
					osp.address a,
					(select distinct b.address_id, total,nvl(q.billing_amount,0) billing_amount
					from 
						BILLING.billing_daily_more_info b,
						(select address_id,billing_amount from sm.cue_tracker q where add_id_status = 'Active') q
					where
					b.address_id = q.address_id(+) ) b2
				where
					a.address_id = b2.address_id
				group by a.footprint_id
				order by footprint_id";
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array('cnt' 		=> $row['CNT'],
						'total'		=> $row['TOTAL'],
						'footprint'	=> $row['FOOTPRINT_ID']);
			array_push($this->custData,$rec);
		}
	}
	
	public function runReport(){
		$this->getData();
		$header = array('COUNT','TOTAL','FOOTPRINT');
		$filename = 'mrcbyfootprint.csv';
		$title = 'MRC by Footprint';
		$this->sysUtils->exportReport($filename,$this->custData,$header,$title);
	}
}
?>