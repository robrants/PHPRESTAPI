// JavaScript Document

var masterads = angular.module('masterads',['ui.bootstrap']);
masterads.filter('startFrom', function() {
	"use strict";
    return function(input, start) {
        if(input) {
            start = +start; //parse to int
            return input.slice(start);
        }
        	return [];
    	};
	});
masterads.controller('loadADS',function($scope,$http,$modal,$timeout){
	"use strict";
	$scope.chassie = {};
	$scope.stack = {};
	$scope.sh = 1;
	$scope.adsStatus = '';
	var URL = 'http://192.168.253.13/Applications/MasterADS/';
	var obj = 'Reports/';
	var verb = 'getAllADS';	
	
	$http.get(URL+obj+verb).success(function(data){
		$scope.chassie = data;
		console.log(data.length);
		$scope.currentPage = 1; //current page
        $scope.entryLimit = 20; //max no of items to display in a page
        $scope.filteredItems = $scope.chassie.length; //Initially for no filter  
        $scope.totalItems = $scope.chassie.length;
		$scope.sh = 1;
		console.log($scope.sh);
	});
	
	$scope.filter = function() {
        	$timeout(function() { 
            	$scope.filteredItems = $scope.filtered.length;
        	}, 10);
    };
    $scope.sort_by = function(predicate) {
        	$scope.predicate = predicate;
        	$scope.reverse = !$scope.reverse;
    };
	
	$scope.exportChassies = function(){
		verb = 'exportADS';		
		var exportURL = URL+obj+verb;
		if($scope.search !== undefined){exportURL = exportURL+'/'+$scope.search;}
		$http.get(exportURL).success(function(data){
			if(data !== 1){alert('Export Failed');}
		});
	};
	
	$scope.pullChassies = function(){
		//verb = 'getAllADS';
		console.log('This is what you get: '+$scope.adsStatus);
		var chasURL = URL+obj+verb;
		if($scope.adsStatus !== undefined){chasURL = chasURL+'/0/'+$scope.adsStatus;}
		$http.get(chasURL).success(function(data){
			$scope.chassie = data;
			$scope.stack = {};
			$scope.sh = 1;
		});
	};
	
	$scope.addADS = function(){
		var addForm = $modal.open({
			templateUrl: 'modal/addADS.html',
			controller: 'addADSCTL',
			size:'lg'
		});
		addForm.result.then(function(){
			obj = 'Reports/';
			verb = 'getAllADS';
			$scope.pullChassies();
		});
	};
	$scope.updateADS = function(p){
		console.log(p);
		var addForm = $modal.open({
			templateUrl: 'modal/addADS.html',
			controller: 'updateADSCTL',
			size:'lg',
			resolve:{
				chassie: function(){return $scope.chassie[p];}
			}
		});
		addForm.result.then(function(){
			obj = 'Reports/';
			verb = 'getAllADS';
			$scope.pullChassies();
		});
	};
	$scope.deleteADS = function(p){
		obj = 'Crud/';
		verb = 'deleteChassie';
		$http.get(URL+obj+verb+'/'+p).success(function(data){
			obj = 'Reports/';
			verb = 'getAllADS';
			$scope.pullChassies();
		});
	};
	
	$scope.StackMng = function(p){
		var obj = 'Reports/';
		var verb = 'getStack';
		$scope.currentSwitch = $scope.chassie[p];
		console.log($scope.chassie[p].switch_id);
		$http.get(URL+obj+verb+"/"+$scope.chassie[p].switch_id).success(function(data){
			$scope.stack = data;
			console.log($scope.stack);
			$scope.sh = 2;
		});
	
	};
	
	$scope.getStack = function(p){
		var obj = 'Reports/';
		var verb = 'getStack';
		//$scope.currentSwitch = $scope.chassie[p];
		//console.log($scope.chassie[p].switch_id);
		$http.get(URL+obj+verb+"/"+p).success(function(data){
			$scope.stack = data;
			//console.log($scope.stack);
			$scope.sh = 2;
		});
	
	};
	
	
	$scope.addStack = function(){
		var stackForm = $modal.open({
			templateUrl: 'modal/addStack.html',
			controller: 'AddSTACK',
			resolve:{
			switchid: function(){return $scope.currentSwitch.switch_id;}
			},
			size: 'lg'
		});
		stackForm.result.then(function(p){
			$scope.getStack(p);
		});
	};
	
	$scope.updateStack = function(p){
		var stackForm = $modal.open({
			templateUrl: 'modal/addStack.html',
			controller: 'updateSTACK',
			resolve:{
				stackid: function(){return $scope.stack[p];}
			},
			size: 'lg'
		});
		stackForm.result.then(function(p){
			console.log(p);
			$scope.getStack(p);
		});
	};
});

masterads.controller('addADSCTL',function($scope,$http,$modalInstance){
	"use strict";
	$scope.input = {};	
	$scope.footprint = {};
	$scope.mm = {};
	var URL = 'http://192.168.253.13/Applications/MasterADS/';	
	var obj = 'CommonQueries/';
	var verb = 'getswitchModel';
	$http.get(URL+obj+verb).success(function(data){
		$scope.mm = data;
		verb = 'getFootprint';
		$http.get(URL+obj+verb).success(function(data){
			$scope.footprint = data;
			obj = 'Crud/';
			verb = 'addChassie';
		});
	});
	
	$scope.ok = function(){
		$http.post(URL+obj+verb+"/0",$scope.input).success(function(data){
			if (data === '1'){ $modalInstance.close('1');}
			else{$modalInstance.close('0');}
		});
	};
});
	
masterads.controller('updateADSCTL',function($scope,$http,$modalInstance,chassie){
	"use strict";
	console.log(chassie);
	delete chassie.index;
	delete chassie.switchdesc;
	$scope.input = chassie;	
	$scope.footprint = {};
	$scope.mm = {};
	var URL = 'http://192.168.253.13/Applications/MasterADS/';	
	var obj = 'CommonQueries/';
	var verb = 'getswitchModel';
	$http.get(URL+obj+verb).success(function(data){
		$scope.mm = data;
		verb = 'getFootprint';
		$http.get(URL+obj+verb).success(function(data){
			$scope.footprint = data;
			obj = 'Crud/';
			verb = 'addChassie';
		});
	});	
	$scope.ok = function(){
		$http.post(URL+obj+verb+'/'+$scope.input.switch_id,$scope.input).success(function(data){
			if (data === '1'){ $modalInstance.close('1');}
			else{$modalInstance.close('0');}
		});
	};	
});
masterads.controller('AddSTACK',function($scope,$http,$modalInstance,switchid){
	"use strict";
	$scope.input = {};
	$scope.input.switch_id = switchid;
	$scope.mm = {};
	var URL = 'http://192.168.253.13/Applications/MasterADS/';	
	var obj = 'CommonQueries/';
	var verb = 'getswitchModel';
	$http.get(URL+obj+verb).success(function(data){
		$scope.mm = data;
		obj = 'Crud/';
		verb = 'addStack';
	});
	$scope.ok = function(){
		$http.post(URL+obj+verb+'/0',$scope.input).success(function(data){
			if(data === 'true'){$modalInstance.close($scope.input.switch_id);}
			else{$modalInstance.close('0');}
		});
	};
});
masterads.controller('updateSTACK',function($scope,$http,$modalInstance,stackid){
	"use strict";
	console.log(stackid);
	//$scope.index = stackid.index;
	delete stackid.index;
	//console.log('index is: '+$scope.index);
	$scope.input = stackid;
	$scope.mm = {};	
	var URL = 'http://192.168.253.13/Applications/MasterADS/';	
	var obj = 'CommonQueries/';
	var verb = 'getswitchModel';
	
	$http.get(URL+obj+verb).success(function(data){
		$scope.mm = data;
		obj = 'Crud/';
		verb = 'addStack';		
	});	
	
	$scope.ok = function(){
		$http.post(URL+obj+verb+'/'+$scope.input.stackid,$scope.input).success(function(data){
			if (data === 'true'){ $modalInstance.close($scope.input.switch_id);}
			else{$modalInstance.close('0');}																
		});
	};
});
