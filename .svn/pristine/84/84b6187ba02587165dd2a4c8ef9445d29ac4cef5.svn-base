<?php
	trait crudBuilder {
		function parseVals($data){
			$first = 0;
			$vals = array_reduce($data,function($carry,$item) use(&$first){
				//error_log($first,0);
				if($first == 0){
					$carry = "'".$item."'";
					//error_log('Value is '.$item,0);
					$first = 1;
				} else {
					$carry .= ",'".$item."'";
					//error_log('Value is '.$carry,0);
				}
				
				return $carry;
			});
			//error_log('Returning '.$vals);
			return $vals;
		}
		function buildInsert($table,$data,$pk,$exceptions=null){
			$keys = '';
			$vals = '';
			if($pk != 0){
				$keys = key($pk);
				$vals = $pk[$keys];
			}
			$first = 0;
			$keys .= implode(',',array_keys($data));
			$vals .= array_reduce($data,function($carry,$item) use(&$first){						
						if(preg_match('/[a-zA-Z\.\:\-\_\/]/',$item)){
							if($first == 0){
								$carry .= "'".$item."'";
								$first = 1;
							}else {$carry .= ",'".$item."'";}
						}else {
							if($first == 0){
								$carry .= "$item";
								$first = 1;
							}else{$carry .= ",$item";}
						}
						return $carry;
					});
			$insert = "insert into $table ($keys) values($vals)";
			return $insert;
		}
		function buildUpdate($table,$data,$where){			
			$keyvals;
			
			foreach($data as $key => $item){
				error_log('Begin Parsing '.$key.' = '.$item,0);
				if(preg_match('/[a-zA-Z\.\:\-\_\/]/',$item)){
					$item = "'".$item."'";	
				}
				
				if (is_null($item) || $item == ' ' || $item == ''){
					continue;
				}elseif(isset($keyvals) and $keyvals != ''){
					error_log('We have a value set',0);
					$keyvals .= ','.$key."=".$item;
				}else $keyvals = $key."=".$item;
			}
			//error_log('$keyvals = '.$keyvals,0);
			$update = "update ".$table." set ".$keyvals.$where;
			return $update;
		}
		function buildDelete($table,$where){
			$delete = "delete from ".$table.$where;
			return $delete;
		}
		
		function scrubData($input){
			$returnarray = array();
			if(is_array($input)){
				foreach($input as $key => $val){
					//error_log($val,0);
					if(count($tmp = explode('\"',$val)) > 1){
						
						/*error_log('Count for \" explode is '.count($tmp),0);
						foreach($tmp as $val){
							error_log($val,0);
						}*/
						$returnarray[$key] =  $tmp[1];
					}elseif(count($tmp = explode('"',$val)) > 1){
						//error_log('Input for '.$key.' is '.$tmp[1],0);
						//error_log('Count for this is '.count($tmp),0);
						$returnarray[$key] =  $tmp[1];
					}elseif(count($tmp = explode(':',$val)) > 1){
						$returnarray[$key] = implode(':',array_slice($tmp,0,count($tmp)-1));
					}else{
						$returnarray[$key] = $val;
					}
					//error_log($key.' value is '.$returnarray[$key],0);
				}
				//error_log('Total Elements in scrubed data '.count($returnarray),0);
				return $returnarray;
			}else exit;
		}
		
		function validateData($input){
			
			$returnarray = array();
			foreach($input as $key => $val){	
				error_log($key.' '.$val,0);
				if(is_null($val) || $val == ' ' || $val == ''){
					error_log('No value found',0);
					continue;
				}else{
					$returnarray[$key] = $val;
					error_log('adding columns '.$key.' with value '.$returnarray[$key],0);
				}
			
			
			}
			return $returnarray;
		}
	}
?>