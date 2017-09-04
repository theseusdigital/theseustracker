<?php
	session_start();
	require '../includes/functions.php';
	require '../includes/connection.php';

	if(isset($_GET['hashtagdata'])){
		$hashtagdata = $_GET['hashtagdata'];
		$hashtagid = $hashtagdata['hashtagid'];
		$hashtagname = $hashtagdata['name'];
		$since = $hashtagdata['sincedate'];

		$hashtagquery = "select ht.tweet_id,ht.text,ht.created_at,ht.entities_media,hu.user_id,hu.name,hu.screen_name,hu.profile_image_url,hu.verified from tracker_hashtagtweet ht inner join tracker_hashtaguser hu on ht.user_id = hu.user_id where ht.hashtag_id = $hashtagid and ht.created_at > '$since 00:00:00' and active = 1 order by ht.created_at desc limit 10";
	    $result = mysql_query($hashtagquery);
	    $hashtagtweets = array();
	    while($row = mysql_fetch_array($result)) {
	    	$tweet = array();
	    	$tweet['userid'] = $row['user_id'];
	    	$tweet['tweetid'] = $row['tweet_id'];
	    	if($row['entities_media'] == ""){
	    		$tweet['image'] = str_replace("normal","400x400",$row['profile_image_url']);
	    	}
	    	else{
	    		$tweet['image'] = $row['entities_media'];
	    	}
	    	$tweet['message'] = unicode_decode($row['text']);
	    	$tweet['name'] = unicode_decode($row['name']);
	    	$tweet['screenname'] = unicode_decode($row['screen_name']);
	    	$tweettime = strtotime(date_format(date_create($row['created_at']),"Y-m-d H:i:s"));
	    	$tweet['age'] = postage($tweettime)." ago";
	    	$tweet['tweeturl'] = "https://twitter.com/".$row['screen_name']."/status/".$row['tweet_id'];
	        array_push($hashtagtweets, $tweet);
	    }

	    // if no tweets
	    if(count($hashtagtweets) == 0){
	    	$tweet = array();
	    	$tweet['image'] = "images/default.jpg";
	    	$tweet['message'] = "No Tweets";
	    	$tweet['name'] = "No Tweets";
	    	$tweet['screenname'] = "notweets";
	    	$tweet['age'] = "no tweets";
	    	$tweet['tweeturl'] = "";
	        array_push($hashtagtweets, $tweet);
	    }

	    //filling extra tweet slots
	    if(count($hashtagtweets)<10){
	    	$emptytweets = 10 - count($hashtagtweets);
	    	$extratweets = array_slice($hashtagtweets, 0, $emptytweets);
	    	while(count($hashtagtweets)<10){
	    		$hashtagtweets = array_merge($hashtagtweets, $extratweets);
	    	}
	    }
	    $hashtagtweets = array_slice($hashtagtweets, 0, 10);

	    // highlighting hashtag in those tweets
	    $tweetcolors = array('blue','red','orange','violet');
	    $colorid = 0;
	    $resulttweets = array();
	    foreach($hashtagtweets as $tweet){
	    	if($colorid > 3){
	    		$colorid = 0;
	    	}
	    	$tweet['message'] = highlight_tweet($hashtagname,$tweet['message'],$tweetcolors[$colorid]);
	    	array_push($resulttweets, $tweet);
	    	$colorid += 1;
	    }

	    $_SESSION['hashtagtweets'] = $resulttweets;
	    $_SESSION['hashtagname'] = $hashtagname;
	    echo json_encode($resulttweets);
	}

?>