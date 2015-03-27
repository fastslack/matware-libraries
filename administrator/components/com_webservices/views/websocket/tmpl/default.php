<?php
/**
 * @version       $Id: 
 * @package       Matware.Libraries
 * @subpackage    Webservices
 * @copyright     Copyright (C) 1996 - 2015 Matware - All rights reserved.
 * @author        Matias Aguirre
 * @email         maguirre@matware.com.ar
 * @link          http://www.matware.com.ar/
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-2.0-standalone.html
 */
defined('_JEXEC') or die;

?>
<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>

<div ng-app="myApp" ng-controller="myCtrl">
	<h1>The time is: <b>{{stats.time}}</b></h1>
</div>

<script language="javascript" type="text/javascript">

	var wsUri = "ws://localhost:8888";

	var myApp = angular.module('myApp', []);

	myApp.controller('myCtrl', function ($scope) {
		$scope.stats = [];

		// Define Websocket
		websocket = new WebSocket(wsUri);

		websocket.onopen = function(evt) {
			onOpen(evt)
		};

		websocket.onclose = function(evt) {
			onClose(evt)
		};

		websocket.onmessage = function(evt) {
			onMessage(evt)
		};

		websocket.onerror = function(evt) {
			onError(evt)
		};

		// Define WebSocket functions
		function onOpen(evt) {
		  console.log("CONNECTED");
		  doSend("webservices:time");
		}

		function onClose(evt) {
		  console.log("DISCONNECTED");
		}

		function onMessage(evt) {
			//console.log(evt.data);

			parse = JSON.parse(evt.data);

		  $scope.stats = JSON.parse(evt.data);
			$scope.$apply();

			doSend("webservices:time");
		}

		function onError(evt) {
		  console.log('<span style="color: red;">ERROR:</span> ' + evt.data);
		}

		function doSend(message) {
		  //console.log("SENT: " + message);
		  websocket.send(message);
		}

	});

</script>
