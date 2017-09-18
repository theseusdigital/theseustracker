<?php
session_start();
    require_once '../includes/functions.php';
    if(!isset($_SESSION['user']))
    {
        redirect_to("../index.php");
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" ng-app="chartApp">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Social Tracker</title>
<link rel="icon" type="image/png" href="../images/favicon.ico" />
<link href="../css/style.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.12.4.js"></script>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/themes/dark-unica.js"></script>

<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- AngularJS Library Scripts -->
<script src="js/angular.min.js"></script>
<script src="js/angular-ui-router.js"></script>

<!-- AngularJS Application Control Scripts -->
<script src="js/services.js"></script>
<script src="js/controllers.js"></script>
<script src="js/app.js"></script>

<script type="text/javascript">
  Highcharts.setOptions({
	    lang: {
	        thousandsSep: ','
	    }
	});
</script>

<style type="text/css">
  .ui-autocomplete{
    z-index: 1100;
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
  }

  #brandnumbers{
    display: none;
  }
</style>
</head>

<body ui-view>

</body>
</html>
