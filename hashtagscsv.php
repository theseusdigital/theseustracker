<?php
session_start();
require_once 'includes/functions.php';

if(!isset($_SESSION['user']))
{
    redirect_to("index.php");
}
require 'includes/connection.php';

$user = $_SESSION['user'];
$username = $user['username'];

$todaydate = date("Y-m-d");

if(isset($_POST['hashtagdata'])){
	$hashtagdata = $_POST['hashtagdata'];
	$handlename = $hashtagdata['handlename'];
	$hashtagname = $hashtagdata['hashtagname'];
	$platformid = $hashtagdata['platformid'];
	$platformname = $hashtagdata['platformname'];
	$since = $hashtagdata['since'];
	$until = $hashtagdata['until'];
	$_SESSION['since'] = $since;
    $_SESSION['until'] = $until;
    $_SESSION['hashtagname'] = $hashtagname;
	$_SESSION['handlename'] = $handlename;
	$_SESSION['hashtagplatform'] = $platformname;
}
else{
	if(isset($_SESSION['since'])){
		$since = $_SESSION['since'];
	    $until = $_SESSION['until'];
	    $sincestr = date_format(date_create($since),"dMY");
	    $untilstr = date_format(date_create($until),"dMY");
	    $hashtagname = $_SESSION['hashtagname'];
	    $handlename = $_SESSION['handlename'];
	    $platformname = $_SESSION['hashtagplatform'];

	    $facebookquery = "select h.name,count(fp.id) as posts from tracker_facebookhandlepost fp
							 inner join tracker_handle h on fp.handle_id = h.id
							 where fp.published >= '$since 00:00:00' and fp.published <= '$until 23:59:59'
							 and fp.hashtags like '%$hashtagname%'
							 group by h.name order by posts desc";
  		$facebooknumbers = mysql_query($facebookquery);
  		$fbhandles = mysql_numrows($facebooknumbers);

  		$twitterquery = "select h.name,count(ht.tweet_id) as tweets from tracker_handletweet ht
  						 inner join tracker_handle h on ht.handle_id = h.id where ht.text not like 'RT %'
  						  and ht.entities_hashtags like '%$hashtagname%' and ht.created_at >= '$since 00:00:00'
  						   and ht.created_at <= '$until 23:59:59' group by h.name order by tweets desc";
  		$twitternumbers = mysql_query($twitterquery);
  		$twhandles = mysql_numrows($twitternumbers);

	}
	$out = fopen('php://output', 'w');
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header("Content-Disposition: attachment; filename=HASHTAG_".$hashtagname."_".$sincestr."_".$untilstr.".csv");

	fputcsv($out, array($since." to ".$until));
	fputcsv($out, array(""));

	if($fbhandles > 0){
		fputcsv($out, array("#$hashtagname on FACEBOOK"));
		fputcsv($out, array("Handle","Posts"));
		while($row = mysql_fetch_array($facebooknumbers)) {
			fputcsv($out, array($row['name'],$row["posts"]));
		}
		fputcsv($out, array(""));
		fputcsv($out, array(""));
	}

	if($twhandles > 0){
		fputcsv($out, array("#$hashtagname on TWITTER"));
		fputcsv($out, array("Handle","Tweets"));
		while($row = mysql_fetch_array($twitternumbers)) {
			fputcsv($out, array($row['name'],$row["tweets"]));
		}
		fputcsv($out, array(""));
		fputcsv($out, array(""));
	}

	fclose($out);
}

?>