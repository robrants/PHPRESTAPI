// JavaScript Document
var inHouseFirmWare = angular.module('Fware',['ui.bootstrap']);
inHouseFirmWare.controller('FwareMng',function($scope,$http){
	"use strict";
	$scope.input = {};
	$scope.input.tube='firmware';
	var obj = 'firmWare/';
	var verb = 'loadQue/';
	var URL = 'http://192.168.253.13/Applications/Firmware/';
	$scope.fiflag = 1;
	$scope.addONTs = function(){
		obj = 'firmWare/';
		verb = 'loadQue/';
		console.log(verb+' '+obj);
		var ontURL = 'tftp://10.250.255.5/zhone/24xxa/301289.img';
		var ont = {};
		if(typeof $scope.input.IP1 !== 'undefined'){
			console.log($scope.input.IP1);
			ont[0] = {};
			ont[0].IP = $scope.input.IP1;
			ont[0].URL = ontURL;
		}
		if(typeof $scope.input.IP2 !== 'undefined'){
			console.log($scope.input.IP2);
			ont[1] = {};
			ont[1].IP = $scope.input.IP2;
			ont[1].URL = ontURL;
		}
		if(typeof $scope.input.IP3 !== 'undefined'){
			ont[2] = {};
			ont[2].IP = $scope.input.IP3;
			ont[2].URL = ontURL;
		}
		if(typeof $scope.input.IP4 !== 'undefined'){
			ont[3] = {};
			ont[3].IP = $scope.input.IP4;
			ont[3].URL = ontURL;
		}
		if(typeof $scope.input.IP5 !== 'undefined'){
			ont[4] = {};
			ont[4].IP = $scope.input.IP5;
			ont[4].URL = ontURL;
		}
		$http.post(URL+obj+verb+'firmware',ont).success(function(data){
			if(data === 1){alert('Jobs Loaded To Que');}
			$scope.input = {};
			$scope.input.tube = 'firmware';
			$scope.input.stamp = 'sysdate';
			$scope.pullmonitor();
		});
	};
	
	
	$scope.pullmonitor = function(){
		obj = 'firmWareMonitor/';
		verb = 'pullJobStats/';
		$scope.que = {};
		$http.post(URL+obj+verb,$scope.input).success(function(data){
			$scope.que = data.queu;
			//console.log($scope.que.current-jobs-ready);
			$scope.monitor = data.completed;
			console.log($scope.monitor);
			$scope.fiflag = 3;
		});
	};
	
	$scope.backToStart = function(){
		$scope.fiflag = 1;
	};
});