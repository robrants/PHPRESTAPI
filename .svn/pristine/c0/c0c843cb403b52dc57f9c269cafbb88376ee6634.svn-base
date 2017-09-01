// JavaScript Document
var mapper = angular.module('mapper',['ui.bootstrap']);
mapper.controller('mapProduct',function($scope,$http){
	"use strict";
	var URL = 'http://192.168.253.13/Applications/ETI/productMapper/';	
	var method = 'getOldISPs/';
	$scope.input = {};
	$http.get(URL+method).success(function(data){
		$scope.isps = data;
	});
	
	$scope.pullProducts = function(){
		method = 'getOldProducts/'+$scope.input.ptype+'/'+$scope.input.isp;
		$http.get(URL+method).success(function(data){
			$scope.oldProdList = data.oldProds[0];
			$scope.newProdList = data.newProds[0];
			console.log($scope.oldProdList.length);
			//method = 'getNewProducts/';			
		});
	};
	
	$scope.mapProd = function(p){
		var oldProd = $scope.oldProdList[p].productid;
		method = 'mapProducts/'+oldProd+'/'+$scope.input.newProd;
		$http.get(URL+method).success(function(data){
			if(data === true) {alert('Mapped');}
			else {alert('Failed to Map');}
			$scope.pullProducts();
		});
	};
});