<?php 
	error_reporting(E_ALL ^ E_DEPRECATED);
	date_default_timezone_set('Asia/Kolkata');
	$connect = mysql_connect('localhost','tracker','tracker123')
	or die('Could Not Connect:'.mysql_error());
	mysql_select_db('theseus_social') or die('Could not select DB');
?>