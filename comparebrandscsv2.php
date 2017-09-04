<?php
	session_start();
	require 'includes/functions.php';
	require 'includes/connection.php';

	if(isset($_POST['branddata'])){
		$branddata = $_POST['branddata'];
		$since = $branddata['since'];
		$until = $branddata['until'];
		$_SESSION['since'] = $since;
		$_SESSION['until'] = $until;
	}
	else{
		if(isset($_SESSION['brandsfornumbers'])){

			$since = $_SESSION['since'];
            $until = $_SESSION['until'];
			$datediff = strtotime($until) - strtotime($since);
			$datediff += 60*60*24;
			$pastsince = date("Y-m-d",strtotime($since) - $datediff);
			$pastuntil = date("Y-m-d",strtotime($until) - $datediff);               
			$days = $datediff/(60*60*24);
			$alldates = array(array($since,$until),array($pastsince,$pastuntil));
			$brandsfornumbers = $_SESSION['brandsfornumbers'];

			$sincestr = date_format(date_create($since),"dMY");
            $untilstr = date_format(date_create($until),"dMY");

			$out = fopen('php://output', 'w');
	        // output headers so that the file is downloaded rather than displayed
	        header('Content-Type: text/csv; charset=utf-8');
	        header('Content-Disposition: attachment; filename=BRANDS_'.$sincestr.'_'.$untilstr.'.csv');

	    	$fbmetrics = array("Page Likes","New Page Likes","Posts","Post Likes","Comments","Shares");
	    	$twmetrics = array("Followers","New Followers","Tweets","Retweets","Favorites");
	    	$ytmetrics = array("Subscribers","New Subscribers","New Views","Videos","Likes","Dislikes","Comments");
	    	$igmetrics = array("Followers","New Followers","Posts","Likes","Comments");
	    	$platform_map = array("facebook"=>$fbmetrics,"twitter"=>$twmetrics,
	    							"youtube"=>$ytmetrics,"instagram"=>$igmetrics);
	    	$csvdata = array();
	    	foreach($platform_map as $platform=>$platmetrics){
	    		if(!isset($csvdata[$platform])){
	    			$csvdata[$platform] = array();
	    		}
	    		$csvdata[$platform]['titles'] = array();
	    		$csvdata[$platform]['metrics'] = array();
	    		$csvdata[$platform]['numbers'] = array();
	    		foreach($platmetrics as $metric){
	    			$csvdata[$platform]['numbers'][$metric] = array();
	    		}
	    	}

	    	$csvplatform = "facebook";
			foreach($brandsfornumbers as $dataid=>$brand){
				$brandname = $brand['brandname'];
				$platform_handles = $brand['platform_handles'];

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
				    	$growthdata["pagelikespc"] = getGrowthCSV($nowrow['pagelikes'],$pastrow['pagelikes']);
				    	$growthdata["newpagelikespc"] = getGrowthCSV($nowrow['newpagelikes'],$pastrow['newpagelikes']);
				    	$growthdata["brandpostspc"] = getGrowthCSV($nowrow['brandposts'],$pastrow['brandposts']);
				    	$growthdata["postlikespc"] = getGrowthCSV($nowrow['postlikes'],$pastrow['postlikes']);
				    	$growthdata["commentspc"] = getGrowthCSV($nowrow['comments'],$pastrow['comments']);
				    	$growthdata["sharespc"] = getGrowthCSV($nowrow['shares'],$pastrow['shares']);

				    	array_push($csvdata[$csvplatform]['titles'], array("Facebook ".$facebookhandle['name'],"",""));
				    	array_push($csvdata[$csvplatform]['metrics'], array("Metric","Score","Percentage Change %"));
				    	array_push($csvdata[$csvplatform]['numbers']["Page Likes"], array("Page Likes",$nowrow['pagelikes'],$growthdata["pagelikespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["New Page Likes"], array("New Page Likes",$nowrow['newpagelikes'],$growthdata["newpagelikespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Posts"], array("Posts",$nowrow['brandposts'],$growthdata["brandpostspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Post Likes"], array("Post Likes",$nowrow['postlikes'],$growthdata["postlikespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Comments"], array("Comments",$nowrow['comments'],$growthdata["commentspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Shares"], array("Shares",$nowrow['shares'],$growthdata["sharespc"]));
				    }
			    }
			    else{
			    	array_push($csvdata[$csvplatform]['titles'], array("No Facebook Handles For ".$brandname,"",""));
				    array_push($csvdata[$csvplatform]['metrics'], array("","",""));
				    foreach($fbmetrics as $metric){
				    	array_push($csvdata[$csvplatform]['numbers'][$metric], array("","",""));
		    		}
			    }
			}


			$csvplatform = "twitter";
			foreach($brandsfornumbers as $dataid=>$brand){
				$brandname = $brand['brandname'];
				$platform_handles = $brand['platform_handles'];

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
				    	$growthdata["followerspc"] = getGrowthCSV($nowrow['followers'],$pastrow['followers']);
				    	$growthdata["newfollowerspc"] = getGrowthCSV($nowrow['newfollowers'],$pastrow['newfollowers']);
				    	$growthdata["tweetspc"] = getGrowthCSV($nowrow['tweets'],$pastrow['tweets']);
				    	$growthdata["retweetspc"] = getGrowthCSV($nowrow['retweets'],$pastrow['retweets']);
				    	$growthdata["favoritespc"] = getGrowthCSV($nowrow['favorites'],$pastrow['favorites']);

				    	array_push($csvdata[$csvplatform]['titles'], array("Twitter ".$twitterhandle['name'],"",""));
				    	array_push($csvdata[$csvplatform]['metrics'], array("Metric","Score","Percentage Change %"));
				    	array_push($csvdata[$csvplatform]['numbers']["Followers"], array("Followers",$nowrow['followers'],$growthdata["followerspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["New Followers"], array("New Followers",$nowrow['newfollowers'],$growthdata["newfollowerspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Tweets"], array("Tweets",$nowrow['tweets'],$growthdata["tweetspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Retweets"], array("Retweets",$nowrow['retweets'],$growthdata["retweetspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Favorites"], array("Favorites",$nowrow['favorites'],$growthdata["favoritespc"])); 
				    }
				}
				else{
					array_push($csvdata[$csvplatform]['titles'], array("No Twitter Handles For ".$brandname,"",""));
				    array_push($csvdata[$csvplatform]['metrics'], array("","",""));
				    foreach($twmetrics as $metric){
				    	array_push($csvdata[$csvplatform]['numbers'][$metric], array("","",""));
		    		}
				}
			}

			$csvplatform = "youtube";
			foreach($brandsfornumbers as $dataid=>$brand){
				$brandname = $brand['brandname'];
				$platform_handles = $brand['platform_handles'];

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
				    	$growthdata["subscriberspc"] = getGrowthCSV($nowrow['subscribers'],$pastrow['subscribers']);
				    	$growthdata["newsubscriberspc"] = getGrowthCSV($nowrow['newsubscribers'],$pastrow['newsubscribers']);
				    	$growthdata["newviewspc"] = getGrowthCSV($nowrow['newviews'],$pastrow['newviews']);
				    	$growthdata["videospc"] = getGrowthCSV($nowrow['videos'],$pastrow['videos']);
				    	$growthdata["likespc"] = getGrowthCSV($nowrow['likes'],$pastrow['likes']);
				    	$growthdata["dislikespc"] = getGrowthCSV($nowrow['dislikes'],$pastrow['dislikes']);
				    	$growthdata["commentspc"] = getGrowthCSV($nowrow['comments'],$pastrow['comments']);

				    	array_push($csvdata[$csvplatform]['titles'], array("Youtube ".$youtubehandle['name'],"",""));
				    	array_push($csvdata[$csvplatform]['metrics'], array("Metric","Score","Percentage Change %"));
				    	array_push($csvdata[$csvplatform]['numbers']["Subscribers"], array("Subscribers",$nowrow['subscribers'],$growthdata["subscriberspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["New Subscribers"], array("New Subscribers",$nowrow['newsubscribers'],$growthdata["newsubscriberspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["New Views"], array("New Views",$nowrow['newviews'],$growthdata["newviewspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Videos"], array("Videos",$nowrow['videos'],$growthdata["videospc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Likes"], array("Likes",$nowrow['likes'],$growthdata["likespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Dislikes"], array("Dislikes",$nowrow['dislikes'],$growthdata["dislikespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Comments"], array("Comments",$nowrow['comments'],$growthdata["commentspc"]));
				    }
			    }
			    else{
			    	array_push($csvdata[$csvplatform]['titles'], array("No Youtube Handles For ".$brandname,"",""));
				    array_push($csvdata[$csvplatform]['metrics'], array("","",""));
				    foreach($ytmetrics as $metric){
				    	array_push($csvdata[$csvplatform]['numbers'][$metric], array("","",""));
		    		}
			    }
			}

			$csvplatform = "instagram";
			foreach($brandsfornumbers as $dataid=>$brand){
				$brandname = $brand['brandname'];
				$platform_handles = $brand['platform_handles'];

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
				    	$growthdata["followerspc"] = getGrowthCSV($nowrow['followers'],$pastrow['followers']);
				    	$growthdata["newfollowerspc"] = getGrowthCSV($nowrow['newfollowers'],$pastrow['newfollowers']);
				    	$growthdata["postspc"] = getGrowthCSV($nowrow['posts'],$pastrow['posts']);
				    	$growthdata["likespc"] = getGrowthCSV($nowrow['likes'],$pastrow['likes']);
				    	$growthdata["commentspc"] = getGrowthCSV($nowrow['comments'],$pastrow['comments']);

				    	array_push($csvdata[$csvplatform]['titles'], array("Instagram ".$instagramhandle['name'],"",""));
				    	array_push($csvdata[$csvplatform]['metrics'], array("Metric","Score","Percentage Change %"));
				    	array_push($csvdata[$csvplatform]['numbers']["Followers"], array("Followers",$nowrow['followers'],$growthdata["followerspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["New Followers"], array("New Followers",$nowrow['newfollowers'],$growthdata["newfollowerspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Posts"], array("Posts",$nowrow['posts'],$growthdata["postspc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Likes"], array("Likes",$nowrow['likes'],$growthdata["likespc"]));
				    	array_push($csvdata[$csvplatform]['numbers']["Comments"], array("Comments",$nowrow['comments'],$growthdata["commentspc"]));

				    }
			    }
			    else{
			    	array_push($csvdata[$csvplatform]['titles'], array("No Instagram Handles For ".$brandname,"",""));
				    array_push($csvdata[$csvplatform]['metrics'], array("","",""));
				    foreach($igmetrics as $metric){
				    	array_push($csvdata[$csvplatform]['numbers'][$metric], array("","",""));
		    		}
			    }
			}

			foreach(array("facebook","twitter","youtube","instagram") as $platform){
				$platformdata = $csvdata[$platform];
				foreach(array("titles","metrics") as $rowkey){
					$csvrow = array();
					foreach($platformdata[$rowkey] as $rowdata){
						$csvrow = array_merge($csvrow, $rowdata);
						$csvrow = array_merge($csvrow, array("",""));
					}
					fputcsv($out, $csvrow);
				}

				foreach($platformdata["numbers"] as $rowkey=>$rowdata){
					$csvrow = array();
					foreach($rowdata as $metricdata){
						$csvrow = array_merge($csvrow, $metricdata);
						$csvrow = array_merge($csvrow, array("",""));
					}
					fputcsv($out, $csvrow);
				}
				fputcsv($out, array(""));
				fputcsv($out, array(""));
			}

			fclose($out);
		}
		else{

		}
		
	}

?>