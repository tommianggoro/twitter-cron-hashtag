<?php
/*
	$server = "localhost";
	
	$username = "root";
	$password = "";
	$database = "allnewchip";
	*/
	
	$server = "localhost";
	$username = "root";
	$password = "tommianggoro";
	
	$database = "gom_microsite_new";
	
	
	mysql_connect($server,$username,$password) or die("Koneksi database gagal !");
	mysql_select_db($database) or die("<h2>Database tidak ditemukan !</h2>");
	
	define('TODAY', time());
		
	if(!defined('UPLOAD_PATH'))define('UPLOAD_PATH', 'upload');
	
?>
