<?php
class automateMASTERADS{
	public function __construct(){		
		$discover = new SwitchDiscover();
		$cleanup = new MasterADSCleanup();
		$crawl = new validateMasterADS();
		
		if($discover->pullCore() == 1){
			echo "We made it through Discovery\n";
			if($crawl->crawlNetwork() == 1){
				$finished = $cleanup->cleanup();
				return $finished;
			}else return -1;
		}else return -1;
	}
}
?>