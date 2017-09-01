<?php
class snmpDriver extends snmpData{
	private $host;
	private $community;

	public function __construct($ip,$comm){
		parent::__construct();		
		$this->host = $ip;
		$this->community = $comm;
	}
	
	public function snmpGetter($oid){
		$SNMP = new SNMP(1,$this->host,$this->community);		
		return $SNMP->walk($oid);
	}
	
	public function snmpSetter($oid){
		$SNMP = new SNMP(1,$this->host,$this->community);
		return $SNMP->walk($oid);
	}
}