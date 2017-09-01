<?php
class dropFiberTag extends myOracle{
	private $dropFiberTag = array();
	private $DHCP;
	private $sysUtil;
	private $dropTag;
	
	public function __construct(){
		parent::__construct();
		$this->DHCP = new UtopiaDHCP();
		$this->sysUtil = new sysUtils();		
	}
	
	
	public function getreport($drop){
		$this->dropTag = $drop[0];
		$sql ="select 
					a.address_id,
        			a.drop_cable_tag,
        			OSP.GET_DISPLAY_ADDRESS(a.address_id) address,
        			ms.network_name,
        			a.switch_slot,
					a.switch_card,
					a.switch_port,
					a.origin_splice_drawer,
					a.origin_splice_tray,
					a.origin_splice_slot,
					a2.attribute2 mac,
					s1.CustType,
					s1.short_name 
				from 
					osp.address a,  
					(select s.address_id,
						case when p.description like '%SBP%' then 'SBP'
						when upper(p.description) like '%GOLD%' then 'GOLD'
						else 'RESIDENTIAL'
						end CustType,
						sub.subscriber_id,
						o.short_name 
					from 
						sm.service s, sm.product p, sm.organization o, sm.subscriber sub
					where 
						p.product_id = s.product_id and
						s.subscriber_id = sub.subscriber_id and
						o.organization_id = sub.service_provider_id and
						p.product_type = 5 and
						s.SERVICE_END_DATE is null) s1,
					blackr.masterads ms,
					INV.ASSET_STATUS a1,
					INV.ASSET a2
				where 
					a.switch_id = ms.switch_id(+) and
					a.address_id = s1.address_id(+)
					and a.address_id = a1.address_id(+)
					and a1.asset_id = a2.asset_id(+)
					and a.drop_cable_tag like '".$this->dropTag."%'";
		//echo $sql."<br>";				
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array(
					'address_id'	=> $row['ADDRESS_ID'],
					'dropCableTag'	=> $row['DROP_CABLE_TAG'],
					'address'		=> $row['ADDRESS'],
					'network'		=> $row['NETWORK_NAME'],
					'switch_slot'	=> $row['SWITCH_SLOT'],
					'switch_card'	=> $row['SWITCH_CARD'],
					'switch_port'	=> $row['SWITCH_PORT'],
					'drawer'		=> $row['ORIGIN_SPLICE_DRAWER'],
					'tray'			=> $row['ORIGIN_SPLICE_TRAY'],
					'splice_slot'	=> $row['ORIGIN_SPLICE_SLOT'],
					'mac'			=> $row['MAC'],
					'custType'		=> $row['CUSTTYPE'],
					'subscriber'	=> $row['SUBSCRIBER_ID'],
					'isp'			=> $row['SHORT_NAME']
			);
			array_push($this->dropFiberTag,$rec);		
		}
		//echo 'rec pulled<br>';
		if(isset($this->dropFiberTag)){
			$x = 0;
			foreach($this->dropFiberTag as $s){
				//echo $s['mac'].'<br>';
				$IP = $this->DHCP->getIP($s['mac']);
				//echo 'IP is'.$IP.'<br>';
				if(isset($IP)){
					if($this->sysUtil->ping($IP)){
						$this->dropFiberTag[$x]['IP'] = $IP;
						$this->dropFiberTag[$x]['pinged'] = 'Up';
					}else{
						$this->dropFiberTag[$x]['IP'] = $IP;
						$this->dropFiberTag[$x]['pinged'] = 'Down';
					}
				}
				$x++;
			}
		}
		$header = array('ADID','DROP CABLE TAG','ADDRESS','NETWORK','SLOT','CARD','PORT','DRAWER','TRAY','SLOT','MAC','CUST TYPE','SUBSCRIBER','ISP','IP','PINGED');
		$filename = 'dropFiberTag.csv';
		$title = 'AddressIds on Drop Fiber';
		$this->sysUtil->exportReport($filename,$this->dropFiberTag,$header,$title);
	}
}
?>