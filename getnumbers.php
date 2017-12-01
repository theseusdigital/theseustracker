<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['branddata'])){
		$branddata = $_POST['branddata'];
		$brandname = $branddata['brandname'];
		$since = $branddata['since'];
		$until = $branddata['until'];
		$_SESSION['brandname'] = $brandname;
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

	    $_SESSION['platform_handles'] = $platform_handles;

	    // Facebook Handles
	    if(isset($platform_handles[1])){
	    	foreach($platform_handles[1] as $facebookhandle){
		    	$facebook_handle = $facebookhandle['id'];
				$facebookquery = "select * from tracker_socialmediafacebook where handle_id = '$facebook_handle' and reportdate between '$since' and '$until' order by reportdate desc";
			    $result = mysql_query($facebookquery);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0)
			    {
			      ?>
			      <div class='handletitle'><h5>FACEBOOK <?php echo $facebookhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Date</td>
		                <td class='heading'>Page Likes</td>
						<td class='heading'>New Page Likes</td>
						<td class='heading'>Post Likes</td>
						<td class='heading'>Brand Posts</td>
						<td class='heading'>Comments</td>
						<td class='heading'>Shares</td>
		            </tr> 
			      <?php
			        while($row = mysql_fetch_array($result)) {
			          echo "<tr>";
			          echo "<td>".$row['reportdate']."</td>";
			          echo "<td>".number_format($row['pagelikes'])."</td>";
			          echo "<td>".number_format($row['newpagelikes'])."</td>";
			          echo "<td>".number_format($row['postlikes'])."</td>";
			          echo "<td>".number_format($row['brandposts'])."</td>";
			          echo "<td>".number_format($row['comments'])."</td>";
			          echo "<td>".number_format($row['shares'])."</td>";
			          echo "</tr>";
			        } 
			      ?>
			      </table>
			      <br>
			      <br>
			      <?php 
			  	}
			  	else{
			  		echo "<div class='handletitle'><h5>No Facebook Numbers For ".$facebookhandle['name']."</h5></div><br><br>";
			  	}
		    }
	    }
	    else{
	    	echo "<div class='handletitle'><h5>No Facebook Handles For ".$brandname."</h5></div><br><br>";
	    }
	    
		
		// Twitter Handles
		if(isset($platform_handles[2])){
			foreach($platform_handles[2] as $twitterhandle){
		    	$twitter_handle = $twitterhandle['id'];
		        $twitterquery = "select * from tracker_socialmediatwitter where handle_id = '$twitter_handle' and reportdate between '$since' and '$until' order by reportdate desc";
			    $result = mysql_query($twitterquery);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0)
			    {
			      ?>
			      <div class='handletitle'><h5>TWITTER <?php echo $twitterhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Date</td>
		                <td class='heading'>Followers</td>
						<td class='heading'>New Followers</td>
						<td class='heading'>Tweets</td>
						<td class='heading'>Retweets</td>
						<td class='heading'>Favorites</td>
		            </tr> 
			      <?php
			        while($row = mysql_fetch_array($result)) {
			          echo "<tr>";
			          echo "<td>".$row['reportdate']."</td>";
			          echo "<td>".number_format($row['followers'])."</td>";
			          echo "<td>".number_format($row['newfollowers'])."</td>";
			          echo "<td>".number_format($row['tweets'])."</td>";
			          echo "<td>".number_format($row['retweets'])."</td>";
			          echo "<td>".number_format($row['favorites'])."</td>";
			          echo "</tr>";
			        } 
			      ?>
			      </table>
			      <br>
			      <br>
			      <?php 
			  	}
			  	else{
			  		echo "<div class='handletitle'><h5>No Twitter Numbers For ".$twitterhandle['name']."</h5></div><br><br>";
			  	}
		    }
		}
		else{
		  	echo "<div class='handletitle'><h5>No Twitter Handles For ".$brandname."</h5></div><br><br>";
		}
	    

	    // Youtube Handles
	    if(isset($platform_handles[3])){
	    	foreach($platform_handles[3] as $youtubehandle){
		    	$youtube_handle = $youtubehandle['id'];
		        $youtubequery = "select * from tracker_socialmediayoutube where handle_id = '$youtube_handle' and reportdate between '$since' and '$until' order by reportdate desc";
			    $result = mysql_query($youtubequery);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0)
			    {
			      ?>
			      <div class='handletitle'><h5>YOUTUBE <?php echo $youtubehandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Date</td>
		                <td class='heading'>All Time Views</td>
						<td class='heading'>New Views</td>
						<td class='heading'>Subscribers</td>
						<td class='heading'>New Subscribers</td>
						<td class='heading'>Videos</td>
						<td class='heading'>Likes</td>
						<td class='heading'>Dislikes</td>
						<td class='heading'>Comments</td>
		            </tr> 
			      <?php
			        while($row = mysql_fetch_array($result)) {
			          echo "<tr>";
			          echo "<td>".$row['reportdate']."</td>";
			          echo "<td>".number_format($row['alltimeviews'])."</td>";
			          echo "<td>".number_format($row['newviews'])."</td>";
			          echo "<td>".number_format($row['subscribers'])."</td>";
			          echo "<td>".number_format($row['newsubscribers'])."</td>";
			          echo "<td>".number_format($row['videos'])."</td>";
			          echo "<td>".number_format($row['likes'])."</td>";
			          echo "<td>".number_format($row['dislikes'])."</td>";
			          echo "<td>".number_format($row['comments'])."</td>";
			          echo "</tr>";
			        } 
			      ?>
			      </table>
			      <br>
			      <br>
			      <?php 
			  	}
			  	else{
			  		echo "<div class='handletitle'><h5>No Youtube Numbers For ".$youtubehandle['name']."</h5></div><br><br>";
			  	}
		    }
	    }
	    else{
	    	echo "<div class='handletitle'><h5>No Youtube Handles For ".$brandname."</h5></div><br><br>";
	    }

	// Instagram Handles
	    if(isset($platform_handles[4])){
	    	foreach($platform_handles[4] as $instagramhandle){
		    	$instagram_handle = $instagramhandle['id'];
				$instagramquery = "select * from tracker_socialmediainstagram where handle_id = '$instagram_handle' and reportdate between '$since' and '$until' order by reportdate desc";
			    $result = mysql_query($instagramquery);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0)
			    {
			      ?>
			      <div class='handletitle'><h5>INSTAGRAM <?php echo $instagramhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			      	<tr>
		                <td class='heading'>Date</td>
		                <td class='heading'>Followers</td>
						<td class='heading'>New Followers</td>
						<td class='heading'>Posts</td>
						<td class='heading'>Likes</td>
						<td class='heading'>Comments</td>
						<td class='heading'>Views</td>
		            </tr> 
			      <?php
			        while($row = mysql_fetch_array($result)) {
			          echo "<tr>";
			          echo "<td>".$row['reportdate']."</td>";
			          echo "<td>".number_format($row['followers'])."</td>";
			          echo "<td>".number_format($row['newfollowers'])."</td>";
			          echo "<td>".number_format($row['posts'])."</td>";
			          echo "<td>".number_format($row['likes'])."</td>";
			          echo "<td>".number_format($row['comments'])."</td>";
			          echo "<td>".number_format($row['views'])."</td>";
			          echo "</tr>";
			        } 
			      ?>
			      </table>
			      <br>
			      <br>
			      <?php 
			  	}
			  	else{
			  		echo "<div class='handletitle'><h5>No Instagram Numbers For ".$instagramhandle['name']."</h5></div><br><br>";
			  	}
		    }
	    }
	    else{
	    	echo "<div class='handletitle'><h5>No Instagram Handles For ".$brandname."</h5></div><br><br>";
	    }
	  	
	}
?>