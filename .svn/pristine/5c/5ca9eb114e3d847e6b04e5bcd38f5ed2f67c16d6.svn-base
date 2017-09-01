// JavaScript Document
var tempManager = angular.module('tempManager',['ui.bootstrap']);
		
		tempManager.controller('tmpMgr',function($scope,$http){
			"use strict";
			$scope.temeratures = {};
			$scope.details = {};
			$scope.input = {};
			$scope.vdetail = 0;
			var obj = '';
			var verb = '';
			var URL = 'tmpPolling.php?fsfunc=';
			$http.get(URL+'2').success(function(data){
				$scope.temeratures = data;
				for(var x in $scope.temeratures.probs){
					if ($scope.temeratures.probs[x].temp >= 90){
						$scope.temeratures.probs[x].class = 'red';	
					}else if ($scope.temeratures.probs[x].temp < 90 && $scope.temeratures.probs[x].temp >= 85){
						$scope.temeratures.probs[x].class = 'orange';
					}else if ($scope.temeratures.probs[x].temp < 85 && $scope.temeratures.probs[x].temp >= 80){
						$scope.temeratures.probs[x].class = 'yellow';
					}else{
						$scope.temeratures.probs[x].class = 'green';	
					}
				}
				
				for(x in $scope.temeratures.ads){
					if ($scope.temeratures.ads[x].temp >= 140){
						$scope.temeratures.ads[x].class = 'red';	
					}else if ($scope.temeratures.ads[x].temp < 140 && $scope.temeratures.ads[x].temp >= 130){
						$scope.temeratures.ads[x].class = 'orange';
					}else if ($scope.temeratures.ads[x].temp < 130 && $scope.temeratures.ads[x].temp >= 120){
						$scope.temeratures.ads[x].class = 'yellow';
					}else if($scope.temeratures.ads[x].temp < 69){
						$scope.temeratures.ads[x].class = 'blue1';	
					}
				}
				setTimeout(function(){
   					window.location.reload(1);
				}, 60000);
			});	
							
		});