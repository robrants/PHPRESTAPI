<?php
class portsObj{
	public $portIfindex; //unique id for each port combine with switch_id for pk
	public $port; // port table
	public $portType; //port table
	public $portDescr; //port table
	public $lldpUid; //switch to switch dcs/rcs?
	public $remoteIP; //remote switch to switch table
	public $svid;
	public $sdp;
	public $remoteIfindex; //remote port
	public $remoteSwitchId; //remote switch
	public $speed;
	public $mtu;
	public $statechange;
	public $admin_status;
	public $physical_status;
	public $port_state;
}
?>