// JavaScript Document

var DHCP = angular.module('ManageDHCP',['ui.bootstrap'],function($compileProvider){
	// $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|file|telnet):/);
});

DHCP.controller('DHCP',function($scope,$http,$modal){
	"use strict";
	$scope.hosts = {};
	$scope.city = {};
	$scope.leaseSearch = {};
	$scope.lease = {};
	$scope.selectedCity = '';
	$scope.alerts = {show:0,
					 type:'danger',
					 msg:'Request Failed'};
	$scope.input = {};
					 
	var URL = 'http://192.168.253.13/Applications/DHCP/';
	var obj = 'UtopiaDHCP/';
	var verb = 'getStaticList';
	//Pull all current assigned Static and Reserved IPs
	$http.get(URL+obj+verb).success(function(data){
		$scope.hosts = data;
	});
			
	//Add new Reservation or Static IP 
	$scope.addHost = function(){
		
		var sform = $modal.open({
				templateUrl:'modal/addStaticIP.html',
				controller:'addIP'
			});
			sform.result.then(function(input){	
				verb = 'addStatic';
				$http.post(URL+obj+verb,input).success(function(data){
				if(data === '1'){
					$scope.alerts = {show:1, type:'success',msg:'Record Added'};
					$scope.cleardata();
				} else{
					$scope.alerts.show = 1;
					$scope.alerts.type = 'danger';
					$scope.alerts.msg = 'Record Failed';
					$scope.cleardata();
				}
			});
		});
	};
	
	
	//Update Reservation or Static IP
	$scope.updateHost = function(p){
		var sform = $modal.open({
				templateUrl:'modal/editStaticIP.html',
				controller:'updateIP',
				resolve:{ 
					host:function(){return $scope.hosts[p];}
				}
			});
			sform.result.then(function(input){
				verb = 'resetStatic';
				$http.post(URL+obj+verb,input).success(function(data){
					if(data === '1'){
						$scope.alerts = {show:1, type:'success',msg:'Record Added'};
						$scope.cleardata();
					} else{
						$scope.alerts.show = 1;
						$scope.alerts.type = 'danger';
						$scope.alerts.msg = 'Record Failed';
						$scope.cleardata();
					}
				});		
		});
	};
	
	//Remove Reservation or Static IP.
	$scope.removeHost = function(p){
		verb = 'removeStatic';
		$http.post(URL+obj+verb,$scope.hosts[p]).success(function(data){
			if(data === '1'){
				$scope.alerts = {show:1, type:'success',msg:'Record Added'};
				$scope.cleardata();
			} else{
				$scope.alerts.show = 1;
				$scope.alerts.type = 'danger';
				$scope.alerts.msg = 'Record Failed';
				$scope.cleardata();
			}
		});
	};

	//Reset The class
	$scope.cleardata = function(){
		verb = 'getStaticList';
		$http.get(URL+obj+verb).success(function(data){
			$scope.hosts = data;
		});
		$scope.alerts = {show:0,
					 type:'danger',
					 msg:'Request Failed'};
		$scope.input = {};	
		$scope.city = {};
		$scope.selectedCity = '';
	};
	
	$scope.searchLeased = function(){
		verb = 'getStaticFromADID/';
		$http.get(URL+obj+verb+'/'+$scope.leaseSearch.val).success(function(data){
			$scope.lease = {};
			$scope.lease = data;
			console.log($scope.lease);
		});	
	};
	
});

//Models controllers for adding,and updating static IPs

DHCP.controller('addIP',function($scope,$http,$modalInstance){
	"use strict";
	var URL = 'http://192.168.253.13/Applications/DHCP/';
	var obj = 'UtopiaDHCP/';
	var verb = 'getCity';
	$scope.IpInput = {};
	$scope.nextIP = '';
	$scope.mycity = '';
	
	//Get the city data
	
	$http.get(URL+obj+verb).success(function(data){
		$scope.city = data;	
	});
	
	//Update the city to the selected city and pull the next IP for hosts file
	$scope.getNextIP = function(){
		verb = 'getNextIP';
		console.log('Click!');
		$http.get(URL+obj+verb+'/'+$scope.mycity).success(function(data){
			for(var x = 0; x < $scope.city.length; x++) {
				if($scope.mycity === $scope.city[x].city){
					$scope.IpInput.subnet = $scope.city[x].subnet_id;
				}
			}
			console.log(data);
			$scope.IpInput.ip = data;
		});
	};	
	
	$scope.ok = function(){		
		$modalInstance.close($scope.IpInput);	
	};
	
	$scope.cancel = function(){
		$modalInstance.dismiss('Cancel');	
	};
	
});

DHCP.controller('updateIP',function($scope,$http,$modalInstance,host){
	"use strict";
	var URL = 'http://192.168.253.13/Applications/DHCP/';
	var obj = 'UtopiaDHCP/';
	var verb = 'getCity';
	$scope.IpInput = host;
	$scope.nextIP = '';
	$scope.mycity = '';
	//$scope.city = {};
	
	//Get the city data
	
	$http.get(URL+obj+verb).success(function(data){
		$scope.city = data;	
		for(var x =0; x < $scope.city.length; x++){
			console.log( $scope.city[x].subnet_id+' '+$scope.IpInput.subnet);
			if($scope.IpInput.subnet === $scope.city[x].subnet_id){
				$scope.mycity = $scope.city[x].city;		
			}
		}
	});
	
	//console.log($scope.city);
	
	/*for(var x =0; x < $scope.city.length; x++){
		console.log( $scope.city[x].subnet_id+' '+$scope.IpInput.subnet);
		if($scope.IpInput.subnet === $scope.city[x].subnet_id){
			$scope.mycity = $scope.city[x].city;		
		}
	}*/
	
	
	//Update the city to the selected city and pull the next IP for hosts file
	$scope.getNextIP = function(){
		verb = 'getNextIP';
		console.log('Click!');
		$http.get(URL+obj+verb+'/'+$scope.mycity).success(function(data){
			for(var x = 0; x < $scope.city.length; x++) {
				if($scope.mycity === $scope.city.city){
					$scope.IpInput.subnet = $scope.mycity.subnet;
				}
			}
			console.log(data);
			$scope.IpInput.ip = data;
		});
	};	
	
	$scope.ok = function(){		
		$modalInstance.close($scope.IpInput);	
	};
	
	$scope.cancel = function(){
		$modalInstance.dismiss('Cancel');	
	};
	
});
