<?php
class switchObj{
	public $switch_id; //masterads table PK <- pulled from db set by db
	public $local_ip; //masterads table
	public $network_name;
	public $ports = array();
	public $lags = array();
	public $vlan_services = array();
	public $stack = array();
	public $make;	//switch_type
	public $firmware; //masterads table
}
?>