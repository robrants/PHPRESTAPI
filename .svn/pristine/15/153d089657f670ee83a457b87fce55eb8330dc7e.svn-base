// JavaScript Document
var Report = angular.module('Report',[]);
Report.controller('getData',function($scope,$http){
	"use strict";
	$scope.results = {};
	var URL = 'http://192.168.253.13/Applications/Reports/';
	var obj = 'custCountControl';
	var verb = 'getCustCountByFootprint';
	$http.get(URL+obj+verb).success(function(data){
		$scope.results = data;
	});
});