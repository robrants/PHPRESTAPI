<?php
	class customerUpreport extends myOracle{
		private $switches = array();
		private $DHCP;
		private $sysUtil;
		public function __construct(){
			parent::__construct();
			$this->DHCP = new UtopiaDHCP();
			$this->sysUtil = new sysUtils();
		}
		
		private function getCusts($switch){			
			$sql = "select 
    		a.address_id,
			sw.network_name,
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
			as1.STATUS = 1 and
			as1.STATUS_DATE = (select max(as3.status_date) from INV.ASSET_STATUS as3 where as3.address_id = as1.address_id and as3.status = 1) and
			p.DESCRIPTION not like ('%Discount%')
		order by a.switch_slot,a.switch_card,a.switch_port";
			$r = $this->runQuery($sql);
			
			while($row = oci_fetch_array($r)){
				$rec = array(
					'switch_name'	=> $row['NETWORK_NAME'],
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
				array_push($this->switches,$rec);
			}
			
			if(isset($this->switches)){
				$x=0;
				foreach($this->switches as $s){
					$IP = $this->DHCP->getIP($s['mac']);
					if(isset($IP)){
						if($this->sysUtil->ping($IP)){
							$this->switches[$x]['IP'] = $IP;
							$this->switches[$x]['pinged'] = 'Up';
						}else{
							$this->switches[$x]['IP'] = $IP;
							$this->switches[$x]['pinged'] = 'Down';
						}
					}
					$x++;
				}
			}
		}
		
		private function pullSwitchList($conditions,$flag){
			$sql = 'select switch_id from blackr.masterads';
			if($flag == 1){
				//Use for pullign an entire footprint
				$sql .= 'm, osp.footprint_seq f where m.footprint_seq = f.footprint_seq and f.footprint_id = '.$conditions;
			}elseif($flag == 2){
				//Use for list of switches
				$listarray = explode(',',$conditions);
				$first = 0;
				foreach($listarray as $l){
					if($first == 0){
						$list = "'".$l."'";
						$first = 1;
					}else $list .= ",'".$l."'";					
				}
				$sql .= ' where network_name in ('.$list.')';
			}
			$r = $this->runQuery($sql);
			$x=0;
			while($row = oci_fetch_row($r)){
				$switches[$x] = $row[0];
				$x++;
			}			
			return $switches;
		}
		
		public function BuildReport($input){
			$mySwitches = $this->pullSwitchList($input['condition'],$input['flag']);
			foreach($mySwitches as $switch){
				$this->getCusts($switch);
			}
			$header = array('NETWORK NAME','ADID','MAC','SLOT','CARD','PORT','DRAWER','TRAY','SPLCIE SLOT','SERVICE','CUSTOMER','ISP','ONT','IP','PINGED');
			$filename = 'cust_UpDown.csv';
			$title = 'Customers Up Down Report';
			$this->sysUtil->exportReport($filename,$this->switches,$header,$title);
		}
		
	}
?>