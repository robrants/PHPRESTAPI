<?php

  Class MasterADSCleanup extends myOracle{
	  private $runid = array();
	  private $data = array();	  
	  
	  private $getOldLocal = "select lp.* from BLACKR.LOCALPORTS lp where lp.runid not in (select max(mr.runid) from BLACKR.MASTERADSRUNCOUNT mr where mr.switch_id = lp.switch_id)";
	  private $cleanLocal = "delete from BLACKR.LOCALPORTS lp where lp.runid not in (select max(mr.runid) from BLACKR.MASTERADSRUNCOUNT mr where mr.switch_id = lp.switch_id)";
	  
	  private $getOldRemote = "select rp.* from blackr.remoteports rp where rp.runid not in (select max(mr.runid) from blackr.masteradsruncount mr where mr.switch_id = rp.local_Switch_id)";
	  private $cleanRemote = "delete from blackr.remoteports rp where rp.runid not in (select max(mr.runid) from blackr.masteradsruncount mr where mr.switch_id = rp.local_Switch_id)";
	  
	  private $getOldLags = "select lg.* from blackr.lags lg where lg.runid not in (select max(mr.runid) from blackr.masteradsruncount mr where mr.switch_id = lg.switch_id)";
	  private $cleanlags = "delete from blackr.lags lg where lg.runid not in (select max(mr.runid) from blackr.masteradsruncount mr where mr.switch_id = lg.switch_id)";
	  //Pull the needed runids for lags to ports cleanup
	  private $getLagRunids = "select lg.runid from blackr.lags lg where lg.runid not in (select max(mr.runid) from blackr.masteradsruncount mr where mr.switch_id = lg.switch_id)";
	  
	  private $getOldLagPorts = "select * from blackr.lagstoports where runid in (";
	  private $cleanLagPorts = "delete from blackr.BLACKR.LAGSTOPORTS where runid in (";
	  
	  private $getOldVlans = "select vs.* from blackr.vlanservices vs where vs.runid not in (select max(mr.runid) from BLACKR.MASTERADSRUNCOUNT mr where mr.switch_id = vs.switch_id)";
	  private $cleanVlans = "delete from blackr.vlanservices vs where vs.runid not in (select max(mr.runid) from BLACKR.MASTERADSRUNCOUNT mr where mr.switch_id = vs.switch_id)";
	  
	  private $getOldSwitchSlots = "select v.* from BLACKR.switchslots v where runid not in (select max(m.runid) from BLACKR.MASTERADSRUNCOUNT m where M.SWITCH_ID = v.switch_id)";
	  private $cleanSwitchSlots = "delete from BLACKR.switchslots v where runid not in (select max(m.runid) from BLACKR.MASTERADSRUNCOUNT m where M.SWITCH_ID = v.switch_id)";
	  
	  public function __construct(){
		  parent::__construct();
		  
	  }
	  	  
	  //Audit Insert function
	  private function auditDelete($tableName){
		  foreach($this->data as $item){
			  $first = 0;
			  $oldvals;
			  foreach($item as $key => $val){
				  if(!is_int($key)){
					  if($first === 0) {
						  $oldvals = $key.'->'.$val;
						  $first = 1;
					  }else $oldvals .='|'.$key.'->'.$val;
				  }				  
			  }		
			  $insert = "insert into BLACKR.TABLEAUDITS (updatestamp,tablename,colname,oldval) values(sysdate,'$tableName','DeleteRecord','$oldvals')";
			  $r = $this->runInsert($insert);
			  if(!$r) return false;
		  }
		  return true;
	  }
	  
	  private function localports(){
		  $r = $this->runQuery($this->getOldLocal);
		  while($row = oci_fetch_array($r)){
			  array_push($this->data,$row);
		  }
		  oci_free_statement($r);
		  $check = $this->auditDelete('blackr.localports');
		  if($check){
			  return $this->runInsert($this->cleanLocal);			  
		  }
	  }
	  
	  private function remoteports(){
		  $r = $this->runQuery($this->getOldRemote);
		  while($row = oci_fetch_array($r)){
			  array_push($this->data,$row);
		  }
		  oci_free_statement($r);
		  $check = $this->auditDelete('blackr.remoteports');
		  if($check){
			  return $this->runInsert($this->cleanRemote);			  
		  }
	  }
	  
	  private function vlanServices(){
		  $r = $this->runQuery($this->getOldVlans);
		  while($row = oci_fetch_array($r)){
			  array_push($this->data,$row);
		  }
		  oci_free_statement($r);
		  $check = $this->auditDelete('blackr.vlanservices');
		  if($check){
			  return $this->runInsert($this->cleanVlans);			  
		  }
	  }
	  
	  private function switchSlots(){
		  $r = $this->runQuery($this->getOldSwitchSlots);
		  while($row = oci_fetch_array($r)){
			  array_push($this->data,$row);
		  }
		  oci_free_statement($r);
		  $check = $this->auditDelete('blackr.switchslots');
		  if($check){
			  return $this->runInsert($this->cleanSwitchSlots);			  
		  }
	  }
	  
	  private function Lags(){
		  $r = $this->runQuery($this->getLagRunids);
		  while($row = oci_fetch_row($r)){
			  array_push($this->runid,$row);
		  }
		  oci_free_statement($r);
		  $rid = implode(',',$this->runid);
		  error_log($rid.' Runids',0);
		  $sql = $this->getOldLagPorts."'".$rid."')";
		  $r = $this->runQuery($sql);
		  while($row = oci_fetch_array($r)){
			  array_push($this->data,$row);
		  }
		  oci_free_statement($r);
		  $check = $this->auditDelete('blackr.lagstoports');
		  if($check){
			  if($rid == ''){$check2 = true;}
			  else{
				  $sql2 = $this->cleanLagPorts."'".$rid."')";
				  $check2 = $this->runInsert($sql2);
			  }
			  if($check2){
				  $r = $this->runQuery($this->getOldLags);
				  while($row = oci_fetch_array($r)){
					  array_push($this->data,$row);
				  }
				  oci_free_statement($r);
				  $check3 = $this->auditDelete('blackr.lags');
				  if($check3){
					  error_log('We are running the cleanlags method',0);
					  return $this->runInsert($this->cleanlags);
				  }
				  else return -1;
			  }
		  }
	  }
	  
	  public function cleanup(){
		  $check = $this->localports();
		  if($check) $check = $this->remoteports();
		  if($check) $check = $this->Lags();
		  error_log($check.' Lags',0);
		  if($check) $check = $this->vlanServices();
		  if($check) $check = $this->switchSlots();
		  return $check;
	  }
	  
	  
  }