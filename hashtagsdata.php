<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['hashtagdata'])){
		$hashtagdata = $_POST['hashtagdata'];
		$handlename = $hashtagdata['handlename'];
		$hashtagname = $hashtagdata['hashtagname'];
		$platformid = $hashtagdata['platformid'];
		$platformname = $hashtagdata['platformname'];
		$since = $hashtagdata['since'];
		$sincetime = $since." 00:00:00";
		$until = $hashtagdata['until'];
		$sincedate = date_create($since);
		$untildate = date_create($until);
		$datediff = strtotime($until) - strtotime($since);
		$datediff += 60*60*24;               
		$days = $datediff/(60*60*24);
		if($days >= 1){
			$handlename = addslashes($handlename);
			
			$_SESSION['since'] = $since;
			$_SESSION['until'] = $until;
			$_SESSION['hashtagname'] = $hashtagname;
			$_SESSION['handlename'] = $handlename;
			$_SESSION['hashtagplatform'] = $platformname;

	  		$facebookquery = "select h.name,count(fp.id) as posts from tracker_facebookhandlepost fp
							 inner join tracker_handle h on fp.handle_id = h.id
							 where fp.published >= '$since 00:00:00' and fp.published <= '$until 23:59:59'
							 and fp.hashtags like '%$hashtagname%'
							 group by h.name order by posts desc";
	  		$facebooknumbers = mysql_query($facebookquery);
	  		$fbhandles = mysql_numrows($facebooknumbers);

	  		$twitterquery = "select h.name,count(ht.tweet_id) as tweets from tracker_handletweet ht
							 inner join tracker_handle h on ht.handle_id = h.id where ht.text not like 'RT %'
							 and ht.entities_hashtags like '%$hashtagname%'and ht.created_at >= '$since 00:00:00'
							 and ht.created_at <= '$until 23:59:59' group by h.name order by tweets desc";
	  		$twitternumbers = mysql_query($twitterquery);
	  		$twhandles = mysql_numrows($twitternumbers);

	  		?>
	  		<table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
			    <tr>
			    	<?php
			    		if($fbhandles > 0){
			    			?>
			    			<td valign="top" width="50%">
					            <div class='handletitle' width='100%'><h5>#<?php echo $hashtagname; ?> on FACEBOOK</h5></div>
							    <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
							    	<tr>
							    		<td class='heading'>Handle</td>
							    		<td class='heading'>Posts</td>
							    	</tr>
							    	<?php
							    	while($row = mysql_fetch_array($facebooknumbers)) {
							    		?>
							    		<tr>
							                <td><?php echo $row['name']; ?></td>
							                <td><?php echo number_format($row["posts"]); ?></td>
							            </tr>
							    		<?php
							    	}
							    	?>
							    </table>
					        </td>
			    			<?php
			    		}
			    	 ?>
			        <?php
			    		if($twhandles > 0){
			    			?>
			    				<td valign="top" width="50%">
						            <div class='handletitle' width='100%'><h5>#<?php echo $hashtagname; ?> on TWITTER</h5></div>
								    <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
								    	<tr>
								    		<td class='heading'>Handle</td>
								    		<td class='heading'>Tweets</td>
								    	</tr>
								    	<?php
								    	while($row = mysql_fetch_array($twitternumbers)) {
								    		?>
								    		<tr>
								                <td><?php echo $row['name']; ?></td>
								                <td><?php echo number_format($row["tweets"]); ?></td>
								            </tr>
								    		<?php
								    	}
								    	?>
								    </table>
						        </td>
			    			<?php
			    		}
			    	 ?>
			        
			    </tr>
			</table>
	      <br>
	  		<?php

	  		//Hashtag Tweets
	  		if($platformid == 1){
	  			$query = "select fp.fbgraph_id,fp.message,fp.likes,fp.comments,fp.shares,fp.postimg,fp.url,fp.published from tracker_facebookhandlepost fp inner join tracker_handle h on fp.handle_id = h.id where h.name = '$handlename' and published >= '$since 00:00:00' and published <= '$until 23:59:59' and fp.hashtags like '%$hashtagname%' and fanpost = 0 order by fp.published desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='title'><h5><?php echo "@$handlename --> #$hashtagname"; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist">
		            <?php
				        while($row = mysql_fetch_array($result)) {
				          ?>
				          <tr>
				          	<td>
				          		<a href="<?php echo $row['url']; ?>">
				          			<img src="<?php echo $row['postimg']; ?>" width="100" height="100"/>
				          		</a>
				          	</td>
				          	<td>
				          		<table width="100%" cellpadding='0' cellspacing='0' style="border-style:solid;border-width:0px;">
				          			<tr>
				          				<td height="50">
				          					<div style="padding:4px;">
				          						<?php
				          							$posttime = strtotime(date_format(date_create($row['published']),"Y-m-d H:i:s"));
		    										$postage = postage($posttime)." ago";
					          						echo "<div style='text-align:right; padding:4px;'><b>$postage</b></div><br>";
					          						
							          				$message = $row['message'];
							          				echo highlight($hashtagname,unicode_decode($message));
							          			?>
				          					</div>
				          				</td>
				          			</tr>
				          			<tr>
				          				<td>
				          					<div class='postnumbers'>
							          			<?php
							          				echo number_format($row['likes'])." Likes | ".number_format($row['comments'])." Comments | ".number_format($row['shares'])." Shares";
							          			?>
							          		</div>
				          				</td>
				          			</tr>
				          		</table>
				          	</td>
				          </tr>
				          <?php
				          $untiltime = date_format(date_create($row['published']),"Y-m-d H:i:s");
				          $postid = $row['fbgraph_id'];
				          $moreid = "more".$postid;
				        } 
			      	?>
			      	<tr id="morerow">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Handle Posts</h5></div><br><br>";
		  		}
	  		}
	  		else{

	  			$tweeturl = "https://twitter.com/#handle#/status/#tweetid#";
	  			$query = "select ht.id,ht.tweet_id,ht.text,ht.favorite_count,ht.retweet_count,ht.entities_media,ht.created_at,hu.profile_image_url,hu.screen_name
	  					 from tracker_handletweet ht inner join tracker_twitteruser hu on ht.user_id=hu.user_id
	  					  where ht.text not like 'RT %' and hu.screen_name = '$handlename'
	  					   and ht.entities_hashtags like '%$hashtagname%' and ht.created_at >= '$since 00:00:00'
	  					    and ht.created_at <= '$until 23:59:59' order by ht.created_at desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='title'><h5><?php echo "@$handlename --> #$hashtagname"; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist">
		            <?php
				        while($row = mysql_fetch_array($result)) {
				        	$currenturl = str_replace("#handle#",$row['screen_name'],$tweeturl);
				        	$currenturl = str_replace("#tweetid#",$row['tweet_id'],$currenturl);
				          ?>
				          <tr>
				          	<td>
				          		<a href="<?php echo $currenturl; ?>">
				          		<?php
				          		if($row['entities_media'] == ""){
				          			echo "<img src=".str_replace("normal","400x400",$row['profile_image_url'])." width='100' height='100'/>";
				          		}
				          		else{
				          			echo "<img src=".$row['entities_media']." width='100' height='100'/>";
				          		}
				          		?>
				          		</a>
				          	</td>
				          	<td>
				          		<table width="100%" cellpadding='0' cellspacing='0' style="border-style:solid;border-width:0px;">
				          			<tr>
				          				<td height="50">
				          					<div style="padding:4px;">
				          						<?php
				          							$tweettime = strtotime(date_format(date_create($row['created_at']),"Y-m-d H:i:s"));
		    										$tweetage = postage($tweettime)." ago";
					          						echo "<b style='float:right;'>".$tweetage."</b>";
					          						echo "<br><br>";
							          				$message = $row['text'];
							          				echo highlight($hashtagname,unicode_decode($message));
							          			?>
				          					</div>
				          				</td>
				          			</tr>
				          			<tr>
				          				<td>
				          					<div class='postnumbers'>
							          			<?php
							          				echo number_format($row['favorite_count'])." Favorites | ".number_format($row['retweet_count'])." Retweets";
							          			?>
							          		</div>
				          				</td>
				          			</tr>
				          		</table>
				          	</td>
				          </tr>
				          <?php
				          $untiltime = date_format(date_create($row['created_at']),"Y-m-d H:i:s");
				          $tweetid = $row['tweet_id'];
				          $moreid = "more".$tweetid;
				        } 
			      	?>
			      	<tr id="morerow">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Handle Posts</h5></div><br><br>";
		  		}

	  		}
	    
	    }
		else{
			$moreid = false;
			echo "<div class='title'><h5>Select Proper Dates</h5></div><br><br>";
		} 
	  	
	}
?>

<script type="text/javascript">
$(document).ready(function(){
	sincetime = <?php echo json_encode($sincetime); ?>;
	platformid = <?php echo json_encode($platformid); ?>;
    moreid = <?php echo json_encode($moreid); ?>;
    handlename = <?php echo json_encode($handlename); ?>;
    hashtagname = <?php echo json_encode($hashtagname); ?>;
    if(moreid != false){
    	$("#"+moreid).click(function(){
		    untiltime = $(this).attr('until');
		    $(this).html("Loading..");
		    moredata = {
		                sincetime:sincetime, 
		                untiltime:untiltime,
		                platformid:platformid,
		                hashtagname:hashtagname,
		                handlename:handlename
		            }; 
		    $.ajax({
		            type:"POST",
		            url:"hashtagpostmore.php",
		            data:{moredata:moredata},
		            success:function(data){ 
		                $("#morerow").remove();
		            	$('#postlist').append(data);
		            }
		        });
	    });
    }
    
});
  
</script>