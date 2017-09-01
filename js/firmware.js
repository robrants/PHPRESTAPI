// JavaScript Document
var firmware = angular.module('firmware',['ui.bootstrap']);
firmware.controller('FmManage',function($scope,$http){
	"use strict";
	$scope.input = {};
	$scope.input.checked = [];
	$scope.input.tube='firmware';
	$scope.input.rows = 20;
	$scope.input.stamp = 'sysdate';
	$scope.footprint = {};
	$scope.release = {};
	$scope.ont = {};
	$scope.upgrades = [];
	$scope.firmflag = 0;
	console.log('Firmware Flag is set to '+$scope.firmflag);
	var obj = 'CommonQueries/';
	var verb = 'getFootprint/';
	var URL = 'http://192.168.253.13/Applications/Firmware/';
	$http.get(URL+obj+verb).success(function(data){
		$scope.footprint = data;
	});
		
	
	$scope.GetONTs = function(){
		$scope.firmflag = 1;
		obj = 'firmWare/';
		verb = 'getFWByFootprint/';
		if($scope.input.prodtype === undefined){$scope.input.prodtype = 0;}
		$http.get(URL+obj+verb+$scope.input.footprint+'/'+$scope.input.device+'/'+$scope.input.prodtype).success(function(data){
			if(data.length > 0){
				$scope.ont = data;
				$scope.upgrades = [];
				$scope.firmflag = 0;
			}else {$scope.firmflag = 2; $scope.ont = {};}
		});
	};
	
	$scope.GetONTAll = function(){
		$scope.firmflag = 1;
		$http.get(URL+'2&start='+$scope.input.start+'&end='+$scope.input.end+'&device='+$scope.input.device).success(function(data){
			if(data.length > 0){
				$scope.ont = data;
				$scope.upgrades = [];
				$scope.firmflag = 0;
			}else {$scope.firmflag = 2; $scope.ont = {};}
		});
	};
	
	$scope.addONT = function (p){
		var checked = 0;
		for(var i in $scope.upgrades){
			if($scope.upgrades[i] === p){
				console.log('woohoo we already have this one');
				var j = $scope.upgrades.indexOf(p);
				$scope.upgrades.splice(j,1);
				checked = 1;
			}
		}
		if(checked === 0){
			console.log('not found here');
			$scope.upgrades.push(p);
		}
		
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
			$scope.firmflag = 3;
		});
	};
	
	$scope.refresh = function(){
		if($scope.firmflag === 3){
			
		}
	};
	
	$scope.backToStart = function(){
		$scope.firmflag = 0;
		$scope.upgrades = [];			
		$scope.input = {};
		$scope.input.checked = [];
		$scope.input = {};
		$scope.input.tube='firmware';
		$scope.input.rows = 20;
		$scope.input.stamp = 'sysdate';
	};
	
	$scope.UpgradeONT = function(){
		var ont = {};
		var x = 0;
		verb = 'loadQue/';
		$scope.upgrades.forEach(function (p){
			ont[x] = $scope.ont[p];
			console.log(ont[x]);
			x++;			
		});
		$http.post(URL+obj+verb+'firmware',ont).success(function(data){
			console.log(data);
			$scope.upgrades = [];			
			$scope.input = {};
			$scope.input.checked = [];
			$scope.input = {};
			$scope.input.tube='firmware';
			$scope.input.rows = 20;
			$scope.input.stamp = 'sysdate';
			$scope.pullmonitor();										
		});
	};	
});