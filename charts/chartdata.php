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

	$inputdata = array();
	$chartdata = array();

	if(!isset($_SESSION['inputdata'])){
		$chartdata['title'] = "No Data";
		$chartdata['columndata'] = "No Data";
		$chartdata['linedata'] = "No Data";
		$chartdata['days'] = ["No Days"];
	}
	else{
		$inputdata = $_SESSION['inputdata'];
		$chartdata = $inputdata['chartdata'];
	}

	if(isset($_POST['inputdata'])){
		$inputdata = $_POST['inputdata'];

		$since = $inputdata['since'];
		$until = $inputdata['until'];
		$selplatformid = $inputdata['selplatformid'];
		$selmetric = $inputdata['selmetric'];
		$dbmetric = $inputdata['dbmetric'];
		$selhandles = $inputdata['selhandles'];

		$alllinedata = array();
		$allcolumndata = array();
		if($selplatformid == 1){
			$socialmediatable = "tracker_socialmediafacebook";
		}
		else if($selplatformid == 2){
			$socialmediatable = "tracker_socialmediatwitter";
		}
		else if($selplatformid == 3){
			$socialmediatable = "tracker_socialmediayoutube";
		}
		else{
			$socialmediatable = "tracker_socialmediainstagram";
		}
		$alltimemetrics = array("pagelikes","followers","subscribers");
		foreach($selhandles as $brandhandles){
			foreach($brandhandles as $handle){
				$linedata = array();
				$handleid = $handle['id'];
				$handlename = $handle['name'];
				$linedata["name"] = $handlename;
				/*$handledata["marker"] = array("symbol"=>"diamond");*/
				$linedata["data"] = array();
				$query = "select $dbmetric,reportdate from $socialmediatable where handle_id = '$handleid' and reportdate between '$since' and '$until'";
			    $result = mysql_query($query);
			    $pagerecords = mysql_numrows($result);
			    if($pagerecords > 0){
			    	while($row = mysql_fetch_array($result)){
			    		array_push($linedata["data"],(int)$row[$dbmetric]);
			    	}
			    }
			    array_push($alllinedata, $linedata);

			    $columndata = array();
			    if (in_array($dbmetric, $alltimemetrics)){
			    	$alltimequery = "select $dbmetric from $socialmediatable where handle_id = '$handleid' and reportdate = '$until'";
		    		$result = mysql_query($alltimequery);
		    		$row = mysql_fetch_array($result);
			    }
			    else{
			    	$query = "select sum($dbmetric) as $dbmetric from $socialmediatable where handle_id = '$handleid' and reportdate between '$since' and '$until'";
				    $result = mysql_query($query);
				    $row = mysql_fetch_array($result);
			    }
			    $columndata["name"] = $linedata["name"];
			    $columndata["y"] = (int)$row[$dbmetric];
			    array_push($allcolumndata, $columndata);
			}
		}
		$chartdata = array();
		$chartdata['days'] = getDays($since, $until);
		$chartdata['title'] = $selmetric;
		$chartdata['linedata'] = $alllinedata;
		$chartdata['columndata'] = $allcolumndata;

		$inputdata['chartdata'] = $chartdata;
		$_SESSION['inputdata'] = $inputdata;
	}
	echo json_encode($chartdata); 
?>