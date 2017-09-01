<?php
//Load up dependencies
class custbyswitch extends myOracle{
	private $custsonSwitch = array();
	private $DHCP;
	private $sysUtil;
	private $switch;
	
	public function __construct(){
		parent::__construct();
		$this->DHCP = new UtopiaDHCP();
		$this->sysUtil = new sysUtils();		
	}
	
	
	public function getreport($switch){
		$this->switch = $switch[0];
		$sql ="select 
    		a.address_id,
			a.switch_slot,
			a.switch_card,
			a.switch_port,
			as2.attribute2 mac,
			a.origin_splice_drawer,
			a.origin_splice_tray,
			a.origin_splice_slot,
			p.description serv,
			oh.contact_fname||' '||oh.contact_lname customer,
			o.short_name isp,
			m.ATTRIBUTE1 || ' '|| m.ATTRIBUTE2 ONT
		from 
    		osp.address a,
			blackr.masterads sw,
			INV.ASSET_STATUS as1,
			inv.asset as2,
			INV.MODEL m,
			sm.service s,
			sm.product p,
			sm.organization o,
			sm.order_item oi,
			sm.order_header oh,
			sm.subscriber sub
		where 
			a.address_id = s.ADDRESS_ID and
			a.switch_id = sw.switch_id and
			as1.ADDRESS_ID = a.address_id and
			as1.ASSET_ID = as2.ASSET_ID and
			as2.MODEL_ID = m.MODEL_ID and
			s.product_id = p.product_id and
			s.subscriber_id = sub.subscriber_id and
			sub.service_provider_id = o.organization_id and
			s.order_item_id = oi.order_item_id and
			oi.order_header_id = oh.order_header_id and
			sw.switch_id = ".$this->switch." and 
			s.service_end_date is null and
			m.ASSET_TYPE = 2 and
			as1.STATUS = 1 and
			as1.STATUS_DATE = (select max(as3.status_date) from INV.ASSET_STATUS as3 where as3.address_id = as1.address_id and as3.status = 1) and
			p.DESCRIPTION not like ('%Discount%')
		order by a.switch_slot,a.switch_card,a.switch_port";
		//echo $sql."<br>";
		$header = array('ADID','MAC','SLOT','CARD','PORT','DRAWER','TRAY','SPLCIE SLOT','SERVICE','CUSTOMER','ISP','ONT');
		$filename = 'cust_from_switch.xls';
		$title = 'Customers from Switch';
		$r = $this->runQuery($sql);
		while($row = oci_fetch_array($r)){
			$rec = array(
					'adid' 			=> $row['ADDRESS_ID'],
					'mac'			=> $row['MAC'],
					'slot'			=> $row['SWITCH_SLOT'],
					'card'			=> $row['SWITCH_CARD'],
					'port'			=> $row['SWITCH_PORT'],
					'drawer'		=> $row['ORIGIN_SPLICE_DRAWER'],
					'tray'			=> $row['ORIGIN_SPLICE_TRAY'],
					'splice_slot'	=> $row['ORIGIN_SPLICE_SLOT'],
					'serv'			=> $row['SERV'],
					'customer'		=> $row['CUSTOMER'],
					'isp'			=> $row['ISP'],
					'ont'			=> $row['ONT']
			);
			array_push($this->custsonSwitch,$rec);		
		}
		//echo 'rec pulled<br>';
		if(isset($this->custsonSwitch)){
			$x = 0;
			foreach($this->custsonSwitch as $s){
				//echo $s['mac'].'<br>';
				$IP = $this->DHCP->getIP($s['mac']);
				//echo 'IP is'.$IP.'<br>';
				if(isset($IP)){
					if($this->sysUtil->ping($IP)){
						$this->custsonSwitch[$x]['IP'] = $IP;
						$this->custsonSwitch[$x]['pinged'] = 'Up';
					}else{
						$this->custsonSwitch[$x]['IP'] = $IP;
						$this->custsonSwitch[$x]['pinged'] = 'Down';
					}
				}
				$x++;
			}
		}
		$header = array('ADID','MAC','SLOT','CARD','PORT','DRAWER','TRAY','SPLCIE SLOT','SERVICE','CUSTOMER','ISP','ONT','IP','PINGED');
		$filename = 'cust_from_switch.csv';
		$title = 'Customers from Switch';
		$this->sysUtil->exportReport($filename,$this->custsonSwitch,$header,$title);
	}
}
?>