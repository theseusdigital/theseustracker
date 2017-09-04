<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['tweetchecks'])){
		$tweetchecks = json_decode($_POST['tweetchecks']);
		$checked_ids = array();
		$unchecked_ids = array();
		foreach($tweetchecks as $tweet){
			foreach($tweet as $tweetid => $checkstatus){
				//echo $checkstatus;
				if($checkstatus == true){
					array_push($checked_ids, $tweetid);
				}
				else{
					array_push($unchecked_ids, $tweetid);
				}
			}
		}
		$checked_count = count($checked_ids);
		$unchecked_count = count($unchecked_ids);
		$checked_ids = implode(",", $checked_ids);
		$unchecked_ids = implode(",", $unchecked_ids);
		$checkquery = "update tracker_hashtagtweet set active = 1 where id in ($checked_ids)";
    	$result = mysql_query($checkquery);

    	$uncheckquery = "update tracker_hashtagtweet set active = 0 where id in ($unchecked_ids)";
    	$result = mysql_query($uncheckquery);
		/*echo($checked_ids);
		echo($unchecked_ids);*/
		echo "$checked_count Checked - $unchecked_count Unchecked";
	}

?>