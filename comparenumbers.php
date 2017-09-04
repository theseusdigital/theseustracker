<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['branddata'])){
		$branddata = $_POST['branddata'];
		$brandid = $branddata['brandid'];
		$brandname = $branddata['brandname'];
		$since = $branddata['since'];
		$until = $branddata['until'];
		$datediff = strtotime($until) - strtotime($since);
		$datediff += 60*60*24;
		$pastsince = date("Y-m-d",strtotime($since) - $datediff);
		$pastuntil = date("Y-m-d",strtotime($until) - $datediff);               
		$days = $datediff/(60*60*24);

		if($days >= 1){
			/*echo $pastsince."::".$pastuntil;*/
			$alldates = array(array($since,$until),array($pastsince,$pastuntil));
			$dataid = $branddata['dataid'];
			$_SESSION['since'] = $since;
			$_SESSION['until'] = $until;
			$handleids = $branddata['brandhandles'];
			$handleids = implode(",", $handleids);
			$handlequery = "select id,name,uniqueid,platform_id from tracker_handle where id in ($handleids)";
		    $result = mysql_query($handlequery);
		    $brandhandles = array();
		    $platform_handles = array();
		    while($row = mysql_fetch_array($result)) {
		    	$brandhandles[$row['id']] = $row;
		    	if(!isset($platform_handles[$row['platform_id']])){
		    		$platform_handles[$row['platform_id']] = array();
		    	}
		    	array_push($platform_handles[$row['platform_id']], $row);
		    }

		    if(!isset($_SESSION['brandsfornumbers'])){
				$_SESSION['brandsfornumbers'] = array();
			}
			$_SESSION['brandsfornumbers'][$dataid] = array("brandid" => $brandid, 
															"brandname" => $brandname,
															"platform_handles" => $platform_handles);

		    // Facebook Handles
		    if(isset($platform_handles[1])){
		    	foreach($platform_handles[1] as $facebookhandle){
			    	$facebook_handle = $facebookhandle['id'];
			    	$fbdata = array();
			    	foreach($alldates as $daterange){
			    		$sinceday = $daterange[0];
			    		$untilday = $daterange[1];
			    		$alltimequery = "select pagelikes from tracker_socialmediafacebook where handle_id = '$facebook_handle' and reportdate = '$untilday'";
			    		$result = mysql_query($alltimequery);
			    		$alltimerow = mysql_fetch_array($result);

			    		$query = "select sum(newpagelikes) as newpagelikes,sum(brandposts) as brandposts,sum(postlikes) as postlikes,sum(comments) as comments,sum(shares) as shares from tracker_socialmediafacebook where handle_id = '$facebook_handle' and reportdate between '$sinceday' and '$untilday'";
					    $result = mysql_query($query);
					    $pagerecords = mysql_numrows($result);
					    $row = mysql_fetch_array($result);
					    $row['pagelikes'] = $alltimerow['pagelikes'];
					    array_push($fbdata, $row);
			    	}
			    	$nowrow = $fbdata[0];
			    	$pastrow = $fbdata[1];
			    	
			    	$growthdata = array();
			    	$growthdata["pagelikespc"] = getGrowth($nowrow['pagelikes'],$pastrow['pagelikes']);
			    	$growthdata["newpagelikespc"] = getGrowth($nowrow['newpagelikes'],$pastrow['newpagelikes']);
			    	$growthdata["brandpostspc"] = getGrowth($nowrow['brandposts'],$pastrow['brandposts']);
			    	$growthdata["postlikespc"] = getGrowth($nowrow['postlikes'],$pastrow['postlikes']);
			    	$growthdata["commentspc"] = getGrowth($nowrow['comments'],$pastrow['comments']);
			    	$growthdata["sharespc"] = getGrowth($nowrow['shares'],$pastrow['shares']);
			      ?>
			      <div class='handletitle' width='25%'><h5>FACEBOOK <?php echo $facebookhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Page Likes</td>
		                <td><?php echo number_format($nowrow['pagelikes']); ?></td>
		                <td><?php echo $growthdata["pagelikespc"]; ?></td>
		            </tr>
			      	<tr>
		                <td class='heading'>New Page Likes</td>
		                <td><?php echo number_format($nowrow['newpagelikes']); ?></td>
		                <td><?php echo $growthdata["newpagelikespc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Posts</td>
		                <td><?php echo number_format($nowrow['brandposts']); ?></td>
		                <td><?php echo $growthdata["brandpostspc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Post Likes</td>
		                <td><?php echo number_format($nowrow['postlikes']); ?></td>
		                <td><?php echo $growthdata["postlikespc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Comments</td>
		                <td><?php echo number_format($nowrow['comments']); ?></td>
		                <td><?php echo $growthdata["commentspc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Shares</td>
		                <td><?php echo number_format($nowrow['shares']); ?></td>
		                <td><?php echo $growthdata["sharespc"]; ?></td>
		            </tr> 
			      </table>
			      <br>
			      <br>
			      <?php 
			    }
		    }
		    else{
		    	//echo "<div class='title'><h5>No Facebook Handles</h5></div><br><br>";
		    	echo "";
		    }
	    
			// Twitter Handles
			if(isset($platform_handles[2])){
				foreach($platform_handles[2] as $twitterhandle){
			    	$twitter_handle = $twitterhandle['id'];
			    	$twdata = array();
			    	foreach($alldates as $daterange){
			    		$sinceday = $daterange[0];
			    		$untilday = $daterange[1];

			    		$alltimequery = "select followers from tracker_socialmediatwitter where handle_id = '$twitter_handle' and reportdate = '$untilday'";
			    		$result = mysql_query($alltimequery);
			    		$alltimerow = mysql_fetch_array($result);

			    		$query = "select sum(newfollowers) as newfollowers,sum(tweets) as tweets,sum(retweets) as retweets,sum(favorites) as favorites from tracker_socialmediatwitter where handle_id = '$twitter_handle' and reportdate between '$sinceday' and '$untilday'";
					    $result = mysql_query($query);
					    $pagerecords = mysql_numrows($result);
					    $row = mysql_fetch_array($result);
					    $row['followers'] = $alltimerow['followers'];
					    array_push($twdata, $row);
			    	}
			    	$nowrow = $twdata[0];
			    	$pastrow = $twdata[1];

			    	$growthdata = array();
			    	$growthdata["followerspc"] = getGrowth($nowrow['followers'],$pastrow['followers']);
			    	$growthdata["newfollowerspc"] = getGrowth($nowrow['newfollowers'],$pastrow['newfollowers']);
			    	$growthdata["tweetspc"] = getGrowth($nowrow['tweets'],$pastrow['tweets']);
			    	$growthdata["retweetspc"] = getGrowth($nowrow['retweets'],$pastrow['retweets']);
			    	$growthdata["favoritespc"] = getGrowth($nowrow['favorites'],$pastrow['favorites']);
			      	?>
				      <div class='handletitle' width='25%'><h5>TWITTER <?php echo $twitterhandle['name']; ?></h5></div>
				      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
				      	<tr>
			                <td class='heading'>Followers</td>
			                <td><?php echo number_format($nowrow['followers']); ?></td>
			                <td><?php echo $growthdata["followerspc"]; ?></td>
			            </tr>
				      	<tr>
			                <td class='heading'>New Followers</td>
			                <td><?php echo number_format($nowrow['newfollowers']); ?></td>
			                <td><?php echo $growthdata["newfollowerspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Tweets</td>
			                <td><?php echo number_format($nowrow['tweets']); ?></td>
			                <td><?php echo $growthdata["tweetspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Retweets</td>
			                <td><?php echo number_format($nowrow['retweets']); ?></td>
			                <td><?php echo $growthdata["retweetspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Favorites</td>
			                <td><?php echo number_format($nowrow['favorites']); ?></td>
			                <td><?php echo $growthdata["favoritespc"]; ?></td>
			            </tr>
				      </table>
				      <br>
				      <br>
			      	<?php 
			    }
			}
			else{
			  	//echo "<div class='title'><h5>No Twitter Handles</h5></div><br><br>";
			  	echo "";
			}

		    // Youtube Handles
		    if(isset($platform_handles[3])){
		    	foreach($platform_handles[3] as $youtubehandle){
			    	$youtube_handle = $youtubehandle['id'];
			    	$ytdata = array();
			    	foreach($alldates as $daterange){
			    		$sinceday = $daterange[0];
			    		$untilday = $daterange[1];

			    		$alltimequery = "select subscribers from tracker_socialmediayoutube where handle_id = '$youtube_handle' and reportdate = '$untilday'";
			    		$result = mysql_query($alltimequery);
			    		$alltimerow = mysql_fetch_array($result);

			    		$query = "select sum(newviews) as newviews,sum(newsubscribers) as newsubscribers,sum(videos) as videos,sum(likes) as likes,sum(dislikes) as dislikes,sum(comments) as comments from tracker_socialmediayoutube where handle_id = '$youtube_handle' and reportdate between '$sinceday' and '$untilday'";
					    $result = mysql_query($query);
					    $pagerecords = mysql_numrows($result);
					    $row = mysql_fetch_array($result);
					    $row['subscribers'] = $alltimerow['subscribers'];
					    array_push($ytdata, $row);
			    	}
			    	$nowrow = $ytdata[0];
			    	$pastrow = $ytdata[1];

			    	$growthdata = array();
			    	$growthdata["subscriberspc"] = getGrowth($nowrow['subscribers'],$pastrow['subscribers']);
			    	$growthdata["newsubscriberspc"] = getGrowth($nowrow['newsubscribers'],$pastrow['newsubscribers']);
			    	$growthdata["newviewspc"] = getGrowth($nowrow['newviews'],$pastrow['newviews']);
			    	$growthdata["videospc"] = getGrowth($nowrow['videos'],$pastrow['videos']);
			    	$growthdata["likespc"] = getGrowth($nowrow['likes'],$pastrow['likes']);
			    	$growthdata["dislikespc"] = getGrowth($nowrow['dislikes'],$pastrow['dislikes']);
			    	$growthdata["commentspc"] = getGrowth($nowrow['comments'],$pastrow['comments']);
				    ?>
				      <div class='handletitle' width='25%'><h5>YOUTUBE <?php echo $youtubehandle['name']; ?></h5></div>
				      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
				      	<tr>
			                <td class='heading'>Subscribers</td>
			                <td><?php echo number_format($nowrow['subscribers']); ?></td>
			                <td><?php echo $growthdata["subscriberspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>New Subscribers</td>
			                <td><?php echo number_format($nowrow['newsubscribers']); ?></td>
			                <td><?php echo $growthdata["newsubscriberspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>New Views</td>
			                <td><?php echo number_format($nowrow['newviews']); ?></td>
			                <td><?php echo $growthdata["newviewspc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Videos</td>
			                <td><?php echo number_format($nowrow['videos']); ?></td>
			                <td><?php echo $growthdata["videospc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Likes</td>
			                <td><?php echo number_format($nowrow['likes']); ?></td>
			                <td><?php echo $growthdata["likespc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Dislikes</td>
			                <td><?php echo number_format($nowrow['dislikes']); ?></td>
			                <td><?php echo $growthdata["dislikespc"]; ?></td>
			            </tr>
			            <tr>
			                <td class='heading'>Comments</td>
			                <td><?php echo number_format($nowrow['comments']); ?></td>
			                <td><?php echo $growthdata["commentspc"]; ?></td>
			            </tr>
				      </table>
				      <br>
				      <br>
				    <?php 
			    }
		    }
		    else{
		    	//echo "<div class='title'><h5>No Youtube Handles</h5></div><br><br>";
		    	echo "";
		    }

		// Instagram Handles
		    if(isset($platform_handles[4])){
		    	foreach($platform_handles[4] as $instagramhandle){
			    	$instagram_handle = $instagramhandle['id'];
			    	$igdata = array();
			    	foreach($alldates as $daterange){
			    		$sinceday = $daterange[0];
			    		$untilday = $daterange[1];

			    		$alltimequery = "select followers from tracker_socialmediainstagram where handle_id = '$instagram_handle' and reportdate = '$untilday'";
			    		$result = mysql_query($alltimequery);
			    		$alltimerow = mysql_fetch_array($result);

			    		$query = "select sum(newfollowers) as newfollowers,sum(posts) as posts,sum(likes) as likes,sum(comments) as comments from tracker_socialmediainstagram where handle_id = '$instagram_handle' and reportdate between '$sinceday' and '$untilday'";
					    $result = mysql_query($query);
					    $pagerecords = mysql_numrows($result);
					    $row = mysql_fetch_array($result);
					    $row['followers'] = $alltimerow['followers'];
					    array_push($igdata, $row);
			    	}
			    	$nowrow = $igdata[0];
			    	$pastrow = $igdata[1];
			    	
			    	$growthdata = array();
			    	$growthdata["followerspc"] = getGrowth($nowrow['followers'],$pastrow['followers']);
			    	$growthdata["newfollowerspc"] = getGrowth($nowrow['newfollowers'],$pastrow['newfollowers']);
			    	$growthdata["postspc"] = getGrowth($nowrow['posts'],$pastrow['posts']);
			    	$growthdata["likespc"] = getGrowth($nowrow['likes'],$pastrow['likes']);
			    	$growthdata["commentspc"] = getGrowth($nowrow['comments'],$pastrow['comments']);
			      ?>
			      <div class='handletitle' width='25%'><h5>INSTAGRAM <?php echo $instagramhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Followers</td>
		                <td><?php echo number_format($nowrow['followers']); ?></td>
		                <td><?php echo $growthdata["followerspc"]; ?></td>
		            </tr>
			      	<tr>
		                <td class='heading'>New Followers</td>
		                <td><?php echo number_format($nowrow['newfollowers']); ?></td>
		                <td><?php echo $growthdata["newfollowerspc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Posts</td>
		                <td><?php echo number_format($nowrow['posts']); ?></td>
		                <td><?php echo $growthdata["postspc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Likes</td>
		                <td><?php echo number_format($nowrow['likes']); ?></td>
		                <td><?php echo $growthdata["likespc"]; ?></td>
		            </tr>
		            <tr>
		                <td class='heading'>Comments</td>
		                <td><?php echo number_format($nowrow['comments']); ?></td>
		                <td><?php echo $growthdata["commentspc"]; ?></td>
		            </tr>
			      </table>
			      <!-- <br>
			      <br> -->
			      <?php 
			    }
		    }
		    else{
		    	//echo "<div class='title'><h5>No Instagram Handles</h5></div><br><br>";
		    	echo "";
		    }
	    }
		else{
			echo "<div class='title'><h5>Select Proper Dates</h5></div><br><br>";
		} 
	  	
	}
?>