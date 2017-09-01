// JavaScript Document

var swtch = angular.module('switch',['ui.bootstrap']);
	swtch.controller('buildSwitch',function($scope,$http){
		"use strict";
		var URL = 'http://192.168.253.13/Applications/SNMPWORKERS/';
		var obj = 'snmpData/';
		var verb = 'getNetworks/';
		$scope.switchid = '';
		$scope.flag=0;
		$http.get(URL+obj+verb).success(function(data){
			$scope.switchs = data;
		});
		$scope.loadSwitch = function(){
			console.log($scope.switchid);
			obj = 'buildSwitch/';
			verb = 'buildSwitch/';
			$http.get(URL+obj+verb+$scope.switchid).success(function(data){
				$scope.sw = data;
				$scope.flag = 1;
			});
		};
		$scope.backtoForm = function(){
			$scope.flag=0;
		};
});