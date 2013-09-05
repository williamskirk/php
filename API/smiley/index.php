<?php
include("restUtil.php");

if(isset($_GET)){
	if(isset($_GET['api'])){
		$_SERVER['PHP_AUTH_USER'] = $_GET['api'];
	}
	if(isset($_GET['key'])){
		$_SERVER['PHP_AUTH_PW'] = $_GET['key'];
	}
}

if(count($_GET) > 0){
	if(!isset($_GET['content'])){
		die(RestUtils::sendResponse(401));
	}
}

if(count($_POST) > 0){
	if(!isset($_POST['content'])){
		die(RestUtils::sendResponse(401));
	}
}

$data = RestUtils::processRequest();

RestUtils::sendResponse(200, json_encode($data), 'json');  

?>