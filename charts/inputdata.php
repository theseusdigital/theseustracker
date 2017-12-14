<?php
session_start();
    require_once '../includes/functions.php';
    if(isset($_GET['logout']))
    {
        if(isset($_SESSION['user'])){
            session_destroy();
            redirect_to("../index.php?logout=1");
        }
    }
    if(!isset($_SESSION['user']))
    {
        redirect_to("../index.php");
    }
    require '../includes/connection.php';

    $user = $_SESSION['user'];
    $username = $user['username'];

    $todaydate = date("Y-m-d");
    $weekdiff = 60*60*24*6;
    $daydiff = 60*60*24;
    $until = date("Y-m-d",strtotime($todaydate) - $daydiff);
    $since = date("Y-m-d",strtotime($until) - $weekdiff);

    $inputdata = array();

    if(isset($_SESSION['inputdata']))
    {
        $inputdata['since'] = $_SESSION['inputdata']['since'];
        $inputdata['until'] = $_SESSION['inputdata']['until'];
        $inputdata['selplatform'] = $_SESSION['inputdata']['selplatform'];
        $inputdata['selplatformid'] = $_SESSION['inputdata']['selplatformid'];
        $inputdata['selbrands'] = $_SESSION['inputdata']['selbrands'];
        $inputdata['selhandles'] = $_SESSION['inputdata']['selhandles'];
        $inputdata['selmetric'] = $_SESSION['inputdata']['selmetric'];
        $inputdata['refresh'] = False;
    }
    else{
    	$inputdata['since'] = $since;
        $inputdata['until'] = $until;
        $inputdata['selplatform'] = "facebook";
        $inputdata['selplatformid'] = 1;
        $inputdata['selbrands'] = "History TV18,National Geographic,Colors,Star Plus,";
        $inputdata['selhandles'] = array();
        $inputdata['selmetric'] = "Page Likes";
        $inputdata['refresh'] = True;
    }

    $handlemapquery = "select th.id,th.name,th.uniqueid,th.platform_id,bh.brand_id from tracker_handle th inner join tracker_brand_handle bh on th.id=bh.handle_id order by th.id";
    $result = mysql_query($handlemapquery);
    $brandhandlemap = array();
    while($row = mysql_fetch_array($result)) {
        $brand_id = $row['brand_id'];
        $platform_id = $row['platform_id'];
        if(!isset($brandhandlemap[$brand_id])){
            $brandhandlemap[$brand_id] = array();
        }
        if(!isset($brandhandlemap[$brand_id][$platform_id])){
            $brandhandlemap[$brand_id][$platform_id] = array();
        }
        array_push($brandhandlemap[$brand_id][$platform_id], $row);
    }

    $platformquery = "select id,name from tracker_platform where active = 1 order by id";
    $result = mysql_query($platformquery);
    $platform_map = array();
    while($row = mysql_fetch_array($result)) {
        $platform_map[$row['name']] = $row['id'];
    }

    $brandquery = "select id,name from tracker_brand";
    $result = mysql_query($brandquery);
    $brand_map = array();
    while($row = mysql_fetch_array($result)) {
      $brand_map[$row['name']] = $row['id'];
    }
    asort($brand_map);

    if(!isset($_SESSION['metric_map'])){
        $metric_map = array();
        $metric_map["facebook"] = array("Page Likes"=>"pagelikes",
                                        "New Page Likes"=>"newpagelikes",
                                        "Post Likes"=>"postlikes",
                                        "Brand Posts"=>"brandposts",
                                        "Comments"=>"comments",
                                        "Shares"=>"shares");
        $metric_map["twitter"] = array("Followers"=>"followers",
                                        "New Followers"=>"newfollowers",
                                        "Tweets"=>"tweets",
                                        "Retweets"=>"retweets",
                                        "Favorites"=>"favorites");
        $metric_map["youtube"] = array("Subscribers"=>"subscribers",
                                        "New Subscribers"=>"newsubscribers",
                                        "New Views"=>"newviews",
                                        "Videos"=>"videos",
                                        "Likes"=>"likes",
                                        "Dislikes"=>"dislikes",
                                        "Comments"=>"comments");
        $metric_map["instagram"] = array("Followers"=>"followers",
                                        "New Followers"=>"newfollowers",
                                        "Posts"=>"posts",
                                        "Likes"=>"likes",
                                        "Views"=>"views",
                                        "Comments"=>"comments");
        foreach($metric_map as $platform => $metrics){
            $metric_map[$platform]["metrics"] = array_keys($metrics);
        }
        $_SESSION['metric_map'] = $metric_map;
    }
    else{
        $metric_map = $_SESSION['metric_map'];
    }

    $inputdata["brandhandlemap"] = $brandhandlemap;
    $inputdata["brand_map"] = $brand_map;
    $inputdata["brand_names"] = array_keys($brand_map);
    $inputdata["platform_map"] = $platform_map;
    $inputdata["platform_names"] = array_keys($platform_map);
    $inputdata["metric_map"] = $metric_map;
    echo json_encode($inputdata);
?>