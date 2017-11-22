<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['moredata'])){
		$moredata = $_POST['moredata'];
		$handlename = $moredata['handlename'];
		$platformid = $moredata['platformid'];
		$hashtagname = $moredata['hashtagname'];
		$sincetime = $moredata['sincetime'];
		$untiltime = $moredata['untiltime'];

		if($platformid == 1){
	    	// Facebook Posts
	  		$query = "select fp.fbgraph_id,fp.message,fp.likes,fp.comments,fp.shares,fp.postimg,fp.url,fp.published from tracker_facebookhandlepost fp inner join tracker_handle h on fp.handle_id = h.id where h.name = '$handlename' and published >= '$sincetime' and published < '$untiltime' and fp.hashtags like '%$hashtagname%' and fanpost = 0 order by published desc limit 3";
		    $result = mysql_query($query);
		    $pagerecords = mysql_numrows($result);
		    if($pagerecords > 0){
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
		          					<?php
		          						$posttime = strtotime(date_format(date_create($row['published']),"Y-m-d H:i:s"));
	    								$postage = postage($posttime)." ago";
		          						echo "<div style='text-align:right; padding:4px;'><b>$postage</b></div><br>";
		          						
				          				$message = $row['message'];
				          				echo highlight($hashtagname,unicode_decode($message));
				          			?>
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
		      <?php 
	  		}
	  		else{
	  			$moreid = false;
	  			?>
	  			<tr>
		      		<td colspan="2" style="padding:0px; border:none;">
		      			<div class="loadmore">No More Posts</div>
		      		</td>
		      	</tr>
	  			<?php
	  		}
	    }

	    if($platformid == 2){
		    	// Twitter Posts
		    	$tweeturl = "https://twitter.com/#handle#/status/#tweetid#";
		  		$query = "select ht.id,ht.tweet_id,ht.text,ht.favorite_count,ht.retweet_count,ht.entities_media,ht.created_at,hu.profile_image_url,hu.screen_name
		  				 from tracker_handletweet ht inner join tracker_twitteruser hu on ht.user_id=hu.user_id
		  				  where ht.text not like 'RT %' and hu.screen_name = '$handlename' and ht.entities_hashtags like '%$hashtagname%'
		  				   and ht.created_at >= '$sincetime' and ht.created_at < '$untiltime' order by ht.created_at desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){
			        while($row = mysql_fetch_array($result)) {
			        	$currenturl = str_replace("#handle#",$handlename,$tweeturl);
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
			          					<?php
			          						$posttime = strtotime(date_format(date_create($row['created_at']),"Y-m-d H:i:s"));
	    									$postage = postage($posttime)." ago";
			          						echo "<div style='text-align:right; padding:4px;'><b>$postage</b></div><br>";

					          				$message = $row['text'];
					          				echo highlight($hashtagname,unicode_decode($message));
					          			?>
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
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			?>
		  			<tr>
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div class="loadmore">No More Posts</div>
			      		</td>
			      	</tr>
		  			<?php
		  		}
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
		                /*$(datasection).html(data);*/
		                //$('#'+loadmoreid+"row").remove();
		                $("#morerow").remove();
		            	$('#postlist').append(data);
		            }
		        });
	    });
    }
});
  
</script>
