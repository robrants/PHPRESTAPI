<?php
class nonConnectRes extends myOracle{
	private $adids = array();			
	private $sysUtil;
	public function __construct(){
		parent::__construct();
		$this->sysUtil = new sysUtils();
	}
	
	
	public function getreport(){		
		$sql ="select a.address_id, OSP.GET_DISPLAY_ADDRESS(a.address_id) address, a.city,
				case when s1.service_end_date is not null then
					'Had Service'
				else 'No Service ever'
				end service
				from 
				osp.address a,
				(select distinct address_id, max(service_end_date) service_end_date 
					from 
					sm.service s,
					(select p.product_id 
						from sm.product p, 
						sm.product_category pc,
						sm.category c
						where p.product_id = pc.product_id and
						pc.category_id = c.category_id and
						c.lookup_id = 1) p1 
					where 
					s.product_id = p1.product_id and
					service_end_date is not null 
					group by address_id 
					order by 1) s1,
				BLACKR.OM_ADDRESS_LOOKUP_MORE_INFO oa
				where
				a.address_id = oa.address_id and
				a.address_id = s1.address_id(+) and
				OA.CITY_GROUP != 'other' and
				oa.GROUP_COLOR = 'GREEN'";
		//echo $sql."<br>";				
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array(
					'address_id'	=> $row['ADDRESS_ID'],					
					'address'		=> $row['ADDRESS'],
					'city'			=> $row['CITY'],
					'service'		=> $row['SERVICE'],					
			);
			array_push($this->adids,$rec);		
		}
		
		$header = array('ADID','ADDRESS','CITY','SERVICE');
		$filename = 'nonConnectRes.csv';
		$title = 'City Addresses without services Residential';
		$this->sysUtil->exportReport($filename,$this->adids,$header,$title);
	}
}
?>