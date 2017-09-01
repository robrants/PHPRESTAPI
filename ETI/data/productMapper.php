<?php
 //include_once '../../TestScripts/Common/myOracle.php';
 class productMapper extends myOracle{
	 private $ISP = array();
	 private $OLDISP;
	 private $productType;
	 private $oldProducts = array();
	 private $newProducts = array();
	 
	 public function __construct(){
		 parent::__construct();
	 }
	 
	 private function mapProduct($old,$new){
		 $insert = "insert into blackr.product_map values($old,$new)";
		 return $this->runInsert($insert);
	 }
	 
	 private function buildOldProducts(){
		 $sql = "select distinct p.product_id,p.description from 
		 			sm.product p,
					sm.product_category pc,
					sm.category c,
					sm.pnp_sp pnp,
					sm.organization o,
					sm.service s
				where
					p.product_id = pc.product_id and
					pc.category_id = c.category_id and
					p.pnp_sp_id = pnp.pnp_sp_id and
					pnp.service_provider_id = o.organization_id and
					p.product_id = s.product_id and
					c.lookup_id = $this->productType and
					s.service_end_date is null and
					p.product_id not in (select oldproduct_id from blackr.product_map) and
					upper(p.description) not like '%DISCOUN%' and
					o.organization_id = $this->OLDISP";
		 $r = $this->runQuery($sql);
		 while($row = oci_fetch_array($r)){
			 $rec = array('productid' => $row['PRODUCT_ID'],
						 'product' => $row['DESCRIPTION']);
			 array_push($this->oldProducts,$rec);
		 }
	 }
	 
	 private function buildNewProducts(){
		 $sql = "select productid,description from blackr.product_catalog where upper(ISP) = upper('".$this->ISP['isp']."')";
		 error_log($sql,0);
		 $r = $this->runQuery($sql);
		 while($row = oci_fetch_array($r)){
			 $rec = array('productid' => $row['PRODUCTID'],
						 'product' => $row['DESCRIPTION']);
			 array_push($this->newProducts,$rec);
		 }
	 }
	 
	 /*private function setNewISPList(){
		 $newISPs = array();
		 $sql = 'select * from blackr.isp';
		 $r = $this->runQuery($sql);
		 while($row = oci_fetch_array($r)){
			 $rec = array('isp_id' 	=> $row['ISP_ID'],
						 'isp' 		=> $row['ISP']);
			 array_push($newISPs,$rec);
		 }
		 return $newISPs;
	 }*/
	 
	 private function setOldISPList(){
		 $oldISPs = array();
		 $sql ="select organization_id isp_id, upper(short_name) ISP from sm.organization";
		$r = $this->runQuery($sql);
		 while($row = oci_fetch_array($r)){
			 $rec = array('isp_id' 	=> $row['ISP_ID'],
						 'isp' 		=> $row['ISP']);
			 array_push($oldISPs,$rec);
		 }
		 return $oldISPs;
	 }
	 
	 private function setNewISP(){
		 $sql = "select 
		 			i.*
				from
					blackr.isp i,
					blackr.isp_rollup i1
				where
					i.isp_id = i1.isp_id and
					i1.old_isp_id =  $this->OLDISP";
		 $r = $this->runQuery($sql);		 
		 while($row = oci_fetch_array($r)){
			 $this->ISP = array('isp_id' => $row['ISP_ID'],
						 'isp'		=> $row['ISP']);
			 
		 }		 
	 }
	 
	 
	 public function getOldISPs(){
		 return $this->setOldISPList();
	 }
	 
	 /*public function getNewISPs(){
		 return $this->setNewISPList();	 
	 }*/
	 
	 public function getOldProducts($input){
		 $this->productType = $input[0];
		 $this->OLDISP = $input[1];
		 $this->setNewISP();
		 $this->buildOldProducts();
		 $this->buildNewProducts();
		 $products = array('oldProds' => array($this->oldProducts), 'newProds' => array($this->newProducts));
		 return $products;
	 }
	 	 	 
	 public function getNewProducts(){		 
		 return $this->newProducts;
	 }
	
	 public function mapProducts($input){
		 return $this->mapProduct($input[0],$input[1]);
	 }
	 
 }
?>