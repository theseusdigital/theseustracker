<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['moredata'])){
		$moredata = $_POST['moredata'];
		$searchterm = $moredata['searchterm'];
		$hashtagid = $moredata['hashtagid'];
		$hashtagname = $moredata['hashtagname'];
		$posttypes = $moredata['posttypes'];
		$sincetime = $moredata['sincetime'];
		$untiltime = $moredata['untiltime'];
		$searchvalue = addslashes($searchterm);

		$tweeturl = "https://twitter.com/#handle#/status/#tweetid#";
  		$query = "select ht.id,ht.tweet_id,ht.text,ht.favorite_count,ht.retweet_count,ht.entities_media,ht.created_at,hu.profile_image_url,hu.screen_name,ht.active from tracker_hashtagtweet ht inner join tracker_hashtaguser hu on ht.user_id=hu.user_id where ht.hashtag_id = '$hashtagid' and ht.text like '%$searchvalue%' and ht.created_at >= '$sincetime' and ht.created_at < '$untiltime' and ht.active in ($posttypes) order by ht.created_at desc limit 10";
	    $result = mysql_query($query);
	    $pagerecords = mysql_numrows($result);
	    if($pagerecords > 0){
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
	          							echo "<b style='float:left;'>@".$row['screen_name']."</b>";
		          						$tweettime = strtotime(date_format(date_create($row['created_at']),"Y-m-d H:i:s"));
										$tweetage = postage($tweettime)." ago";
		          						echo "<b style='float:right;'>".$tweetage."</b>";
		          						echo "<br><br>";
				          				$message = $row['text'];
				          				echo highlight($searchvalue,unicode_decode($message));
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
				          			<span style="float:right;">
				          				Active:
				          				<input type="checkbox" id="<?php echo $row['id']; ?>" class="tweetchecks" <?php echo ($row['active'] == 1 ? 'checked' : '')?> >
				          				&nbsp;
				          			</span>
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
	      			<div id="<?php echo $moreid; ?>" hashtagid="<?php echo $hashtagid; ?>" hashtagname="<?php echo $hashtagname; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
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

?>
<script type="text/javascript">
$(document).ready(function(){
	sincetime = <?php echo json_encode($sincetime); ?>;
    moreid = <?php echo json_encode($moreid); ?>;
    searchterm = <?php echo json_encode($searchterm); ?>;
    posttypes = <?php echo json_encode($posttypes); ?>;
    if(moreid != false){
    	$("#"+moreid).click(function(){
		    untiltime = $(this).attr('until');
		    hashtagid = $(this).attr('hashtagid');
		    hashtagname = $(this).attr('hashtagname');
		    $(this).html("Loading..");
		    moredata = {
		                sincetime:sincetime, 
		                untiltime:untiltime,
		                hashtagid:hashtagid,
		                hashtagname:hashtagname,
		                searchterm:searchterm,
		                posttypes:posttypes
		            }; 
		    $.ajax({
		            type:"POST",
		            url:"hashtagmore.php",
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
