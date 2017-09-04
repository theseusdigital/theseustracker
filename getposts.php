<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['branddata'])){
		$branddata = $_POST['branddata'];
		$searchterms = $branddata['searchterms'];
		$platformid = $branddata['platformid'];
		$platformname = $branddata['platformname'];
		$_SESSION['platformname'] = $platformname;
		$brandid = $branddata['brandid'];
		$brandname = $branddata['brandname'];
		$since = $branddata['since'];
		$sincetime = $since." 00:00:00";
		$until = $branddata['until'];
		$sincedate = date_create($since);
		$untildate = date_create($until);
		$datediff = strtotime($until) - strtotime($since);
		$datediff += 60*60*24;
		/*$pastsince = date("Y-m-d",strtotime($since) - $datediff);
		$pastuntil = date("Y-m-d",strtotime($until) - $datediff);*/                  
		$days = $datediff/(60*60*24);
		if($days >= 1){
			$dataid = $branddata['dataid'];
			$searchvalue = addslashes($searchterms[$dataid]);
			if(!isset($_SESSION['brandsforposts'])){
				$_SESSION['brandsforposts'] = array();
			}
			$_SESSION['brandsforposts'][$dataid] = array("brandid" => $brandid, "brandname" => $brandname);
			$_SESSION['since'] = $since;
			$_SESSION['until'] = $until;
			$handleid = $branddata['handleid'];
			$handlequery = "select id,name,uniqueid,platform_id from tracker_handle where id = $handleid";
		    $result = mysql_query($handlequery);
		    $brandhandle = mysql_fetch_array($result);

		    if($platformid == 1){
		    	// Facebook Posts
		    	$handleid = $brandhandle['id'];
		    	$handlename = $brandhandle['name'];
		  		$query = "select fbgraph_id,message,likes,comments,shares,postimg,url,published from tracker_facebookhandlepost where handle_id = '$handleid' and published >= '$since 00:00:00' and published <= '$until 23:59:59' and message like '%$searchvalue%' and fanpost = 0 order by published desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='handletitle'><h5><?php echo strtoupper($platformname)." ".$brandhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist<?php echo $dataid; ?>">
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
				          					<?php
				          						$posttime = strtotime(date_format(date_create($row['published']),"Y-m-d H:i:s"));
	    										$postage = postage($posttime)." ago";
				          						echo "<div style='text-align:right; padding:4px;'><b>$postage</b></div><br>";
				          						
						          				$message = $row['message'];
						          				echo highlight($searchvalue,unicode_decode($message));
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
				          $moreid = "more".$dataid.$postid;
				        } 
			      	?>
			      	<tr id="morerow<?php echo $dataid; ?>">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" dataid="<?php echo $dataid; ?>" handleid="<?php echo $handleid; ?>" handlename="<?php echo $handlename; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Facebook Posts</h5></div><br><br>";
		  		}
		    }

		    if($platformid == 2){
		    	// Twitter Posts
		    	$tweeturl = "https://twitter.com/#handle#/status/#tweetid#";
		    	$handleid = $brandhandle['id'];
		    	$handlename = $brandhandle['name'];
		  		$query = "select ht.tweet_id,ht.text,ht.favorite_count,ht.retweet_count,ht.entities_media,ht.created_at,tu.profile_image_url from tracker_handletweet ht inner join tracker_twitteruser tu on ht.user_id=tu.user_id where ht.handle_id = '$handleid' and ht.text like '%$searchvalue%' and ht.created_at >= '$since 00:00:00' and ht.created_at <= '$until 23:59:59' order by ht.created_at desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='handletitle'><h5><?php echo strtoupper($platformname)." ".$brandhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist<?php echo $dataid; ?>">
		            <?php
				        while($row = mysql_fetch_array($result)) {
				        	$currenturl = str_replace("#handle#",$brandhandle['name'],$tweeturl);
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
						          				echo highlight($searchvalue,unicode_decode($message));
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
				          $moreid = "more".$dataid.$tweetid;
				        } 
			      	?>
			      	<tr id="morerow<?php echo $dataid; ?>">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" dataid="<?php echo $dataid; ?>" handleid="<?php echo $handleid; ?>" handlename="<?php echo $handlename; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Twitter Posts</h5></div><br><br>";
		  		}
		    }

		    if($platformid == 3){
		    	// Youtube Posts
		    	$videothumbnail = "https://i.ytimg.com/vi/#youtubeid#/mqdefault.jpg";
		    	$videourl = "https://youtube.com/watch?v=#youtubeid#";
		    	$handleid = $brandhandle['id'];
		    	$handlename = $brandhandle['name'];
		  		$query = "select yv.youtubeid,yv.title,yv.views,yv.likes,yv.comments,yv.published,yc.subscribers from tracker_youtubechannelvideo yv inner join tracker_youtubechannel yc on yv.handle_id=yc.handle_id where yv.handle_id = '$handleid' and yv.published >= '$since 00:00:00' and yv.published <= '$until 23:59:59' and yv.title like '%$searchvalue%' order by yv.published desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='handletitle'><h5><?php echo strtoupper($platformname)." ".$brandhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist<?php echo $dataid; ?>">
		            <?php
				        while($row = mysql_fetch_array($result)) {
				          ?>
				          <tr>
				          	<td>
				          		<a href="<?php echo str_replace("#youtubeid#",$row['youtubeid'],$videourl); ?>">
				          			<img src="<?php echo str_replace("#youtubeid#",$row['youtubeid'],$videothumbnail); ?>" width="100" height="100"/>
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

						          				$message = $row['title'];
						          				echo highlight($searchvalue,unicode_decode($message));
						          			?>
				          				</td>
				          			</tr>
				          			<tr>
				          				<td>
				          					<div class='postnumbers'>
							          			<?php
							          				echo number_format($row['views'])." Views | ".number_format($row['likes'])." Likes | ".number_format($row['comments'])." Comments";
							          			?>
							          		</div>
				          				</td>
				          			</tr>
				          		</table>
				          	</td>
				          </tr>
				          <?php
				          $untiltime = date_format(date_create($row['published']),"Y-m-d H:i:s");
				          $youtubeid = $row['youtubeid'];
				          $moreid = "more".$dataid.$youtubeid;
				        } 
			      	?>
			      	<tr id="morerow<?php echo $dataid; ?>">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" dataid="<?php echo $dataid; ?>" handleid="<?php echo $handleid; ?>" handlename="<?php echo $handlename; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Youtube Posts</h5></div><br><br>";
		  		}
		    }

		    if($platformid == 4){
		    	// Facebook Posts
		    	$handleid = $brandhandle['id'];
		    	$handlename = $brandhandle['name'];
		  		$query = "select postid,caption,likes,comments,postimg,url,published from tracker_instagramhandlepost where handle_id = '$handleid' and published >= '$since 00:00:00' and published <= '$until 23:59:59' and caption like '%$searchvalue%' order by published desc limit 3";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){

			      ?>
			      <div class='handletitle'><h5><?php echo strtoupper($platformname)." ".$brandhandle['name']; ?></h5></div>
			      <table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table' id="postlist<?php echo $dataid; ?>">
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
				          					<?php
				          						$posttime = strtotime(date_format(date_create($row['published']),"Y-m-d H:i:s"));
	    										$postage = postage($posttime)." ago";
				          						echo "<div style='text-align:right; padding:4px;'><b>$postage</b></div><br>";

						          				$message = $row['caption'];
						          				echo highlight($searchvalue,unicode_decode($message));
						          			?>
				          				</td>
				          			</tr>
				          			<tr>
				          				<td>
				          					<div class='postnumbers'>
							          			<?php
							          				echo number_format($row['likes'])." Likes | ".number_format($row['comments'])." Comments";
							          			?> 
							          		</div>
				          				</td>
				          			</tr>
				          		</table>
				          	</td>
				          </tr>
				          <?php
				          $untiltime = date_format(date_create($row['published']),"Y-m-d H:i:s");
				          $postid = $row['postid'];
				          $moreid = "more".$dataid.$postid;
				        } 
			      	?>
			      	<tr id="morerow<?php echo $dataid; ?>">
			      		<td colspan="2" style="padding:0px; border:none;">
			      			<div id="<?php echo $moreid; ?>" dataid="<?php echo $dataid; ?>" handleid="<?php echo $handleid; ?>" handlename="<?php echo $handlename; ?>" until="<?php echo $untiltime; ?>" class="loadmore" title="Load More Posts">Load More</div>
			      		</td>
			      	</tr>
			      </table>
			      <?php 
		  		}
		  		else{
		  			$moreid = false;
		  			echo "<div class='title'><h5>No Instagram Posts</h5></div><br><br>";
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
    searchterms = <?php echo json_encode($searchterms); ?>;
    if(moreid != false){
    	$("#"+moreid).click(function(){
	    	dataid = $(this).attr('dataid');
		    untiltime = $(this).attr('until');
		    handleid = $(this).attr('handleid');
		    handlename = $(this).attr('handlename');
		    searchvalue = searchterms[dataid];
		    console.log(dataid);
		    $(this).html("Loading..");
		    moredata = {
		                sincetime:sincetime, 
		                untiltime:untiltime,
		                dataid:dataid,
		                platformid:platformid,
		                handleid:handleid,
		                handlename:handlename,
		                searchval:searchvalue
		            }; 
		    $.ajax({
		            type:"POST",
		            url:"handlemore.php",
		            data:{moredata:moredata},
		            success:function(data){ 
		                /*$(datasection).html(data);*/
		                //$('#'+loadmoreid+"row").remove();
		                $("#morerow"+dataid).remove();
		            	$('#postlist'+dataid).append(data);
		            }
		        });
	    });
    }
    
});
  
</script>