<?php
session_start();
    require_once 'includes/functions.php';
    if(isset($_GET['logout']))
    {
        if(isset($_SESSION['user'])){
            session_destroy();
            redirect_to("index.php?logout=1");
        }
    }
    if(!isset($_SESSION['user']))
    {
        redirect_to("index.php");
    }
    require 'includes/connection.php';

    $user = $_SESSION['user'];
    $username = $user['username'];

    if(isset($_SESSION['hashtagname']))
    {
        $hashtag_name = $_SESSION['hashtagname'];
    }
    else{
        $hashtag_name = "";
    }

    $hashtagquery = "select id,name from tracker_hashtag where active = 1";
    $result = mysql_query($hashtagquery);
    $hashtag_map = array();
    while($row = mysql_fetch_array($result)) {
      $hashtag_map[$row['name']] = $row['id'];
    }
    asort($hashtag_map);

    $searchterm = "";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Social Tracker</title>
<link rel="icon" type="image/png" href="images/favicon.ico" />
<link href="css/style.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.12.4.js"></script>
<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<style type="text/css">
  .ui-autocomplete {
    z-index: 1100;
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .loadmore {
    background-color:#abf2f5;
    border:1px solid #33b7bc;
    font-size:15px;
    /*border-bottom:none;*/
    font-weight:bold;
    text-align:center;
    /*text-transform:uppercase;*/
    padding-top:6px;
    padding-bottom:6px;
    width:100%;
    cursor:pointer;
}

.postnumbers {
    font-size: 13px;
    background-color:#abf2f5;
    border:1px solid #33b7bc;
    /*border-bottom:none;*/
    font-weight:bold;
    text-align:center;
    text-transform:uppercase;
    padding-top:6px;
    padding-bottom:6px;
    width:100%;
}

.hashtaglink{
    font-size: 15px;
    cursor:pointer;
    color:#009999;
}

.hashtaglink:hover{
    font-size: 16px;
}

.inputboxes{
    height:20px;
}
</style>
</head>

<body>
<!-- wrapper starts -->
<div id="wrapper">
    <div id="topbar">
        <div class="toplinks" align="right"></div> <br /><br />
        <div class="report-heading">
            <h1>Social <font color="#009999">Tracker</font></h1>
            <!-- <h1> <img src="images/logo-404-big.png" alt="" style="vertical-align:bottom" /> Social Tracker</h1> -->
        </div>
        <!--<div class="client-logo" align="center"><img src="images/logo-kxip.png" alt=""  /></div>-->
        <div class="clear"></div>
    </div><!-- <br /><br /> -->

<div class="pagebuttons">
    <b>Moderate Posts</b>
    <div class="menuwrap">
        <span class="menuarea">
            <a class="menu" href="#">Menu</a>
            <div class="dropdown">
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="comparebrands.php">Compare Brands</a></li>
                    <li><a href="compareposts.php">Compare Posts</a></li>
                    <li><a href="socialwall">Social Wall</a></li>
                </ul>
            </div>
            <a class="logoutlink" href="home.php?logout=1">Logout</a>  
        </span>
    </div>
</div>
<div class="date">      
    <label>Date from:</label> <input type='text' name='since' id='datepicker' value="<?php if(isset($_SESSION['since'])){ echo $_SESSION['since']; }?>"  style='width:100;' />  &nbsp; &nbsp; &nbsp;
    <label>Date to:</label> <input type='text' name='until' id='datepicker2' value="<?php if(isset($_SESSION['until'])){ echo $_SESSION['until']; }?>"  /> &nbsp; &nbsp; &nbsp;
    <input type="button" value="Generate" name="generate" onclick="Generate()" id="generate"/> 
    &nbsp;
    <input type="button" value="Save Changes" name="save" onclick="saveChecks()" id="savechecks"/>
    &nbsp; &nbsp; 
    <!-- <input type="button" value="Download .CSV" name="csv" onclick="CSV()" />&nbsp; &nbsp; -->
    &nbsp;<img id="loader" style="display:none;" src="images/loader.gif" width="15" height="15">
    &nbsp; <b id="hashtagerror" style="display:none;">Choose Hashtag</b>
</div>
<br/>
<br/>

<table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
    <tr>
        <td valign="top" width="100%">
            <div>
                <br/>
                Hashtag: 
                <input type="text" id="hashtagpicker" class="inputboxes" placeholder=" Choose Hashtag" value="<?php if(isset($_SESSION['hashtagname'])){ echo $_SESSION['hashtagname']; }?>">
                &nbsp;
                Search: 
                <input type="text" id="searchterm" class="inputboxes" placeholder=" Search">
                &nbsp;
                <select name="posttype" id="posttype">
                  <option>Active</option>
                  <option>Inactive</option>
                  <option selected="selected">All</option>
                </select>
                <br/><br/>
            </div>
            <div id="hashtagposts">
            
            </div>
        </td>
    </tr>
</table>

 

<br />
<div class="powered">Powered by <a href="http://4nought4.com/"><img src="images/logo-404.png" alt="" border="0" /></a></div>
<div class="clear"></div>
</div>
<!-- wrapper ends -->


<script type="text/javascript">
$(document).ready(function(){

    $('.menu').on('click', function(){
        $('.dropdown').slideToggle('100');
        return false;
    });
    $('html, body').on('click',function(){
        $('.dropdown').slideUp('100');
    });
    $(".dropdown").click(function(e){
        e.stopPropagation();
    });
    $(".menuarea").mouseleave(function(){
        $('.dropdown').slideUp('100');
    });

    $("#datepicker").datepicker({
        dateFormat:'yy-mm-dd',
        minDate: -60, 
        maxDate: 0
    });
    $("#datepicker2").datepicker({
        dateFormat:'yy-mm-dd',
        minDate: -60, 
        maxDate: 0
    });

    $(document).ajaxStart(function(){
        $("#loader").show();
    });
    
    $(document).ajaxStop(function(){
        $("#loader").hide();
    });

    hashtag_map = <?php echo json_encode($hashtag_map); ?>;
    hashtag_names = <?php echo json_encode(array_keys($hashtag_map)); ?>;

    hashtag_name = <?php echo json_encode($hashtag_name); ?>;
    if(hashtag_name != ""){
        hashtag_id = hashtag_map[hashtag_name];
    }

    $("#hashtagpicker")
        // don't navigate away from the field on tab when selecting an item
        .on( "keydown", function( event ) {
          if ( event.keyCode === $.ui.keyCode.TAB &&
              $( this ).autocomplete( "instance" ).menu.active ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            // delegate back to autocomplete, but extract the last term
            response( $.ui.autocomplete.filter(
              hashtag_names, extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            this.value = ui.item.value;
            hashtag_name = this.value;
            hashtag_id = hashtag_map[hashtag_name];
            Generate();
            $("#hashtagerror").hide();
            return false;
          }
        });
    
    searchterm = <?php echo json_encode($searchterm); ?>;

    $("#searchterm").keyup(function(){
        searchterm = $(this).val();
        if(hashtag_name != ""){
            Generate();
        }
        else{
            $("#hashtagposts").html("<b class='hashtaglink'>Select Hashtag</b>");
        }
    });

    posttypes = "0,1";
    $("#posttype").selectmenu({
      select: function( event, ui ) {
        posttype = this.value;
        if(posttype == "Active"){
            posttypes = "1";
        }
        else if(posttype == "Inactive"){
            posttypes = "0";
        }
        else{
            posttypes = "0,1";
        }
        Generate();
      }
    });
});

function saveChecks(){
    if(hashtag_name != ""){
        tweetchecks = [];
        $('.tweetchecks').each(function(){
            checkstatus = $(this).prop("checked");
            tweetid = $(this).attr("id");
            tweetstatus = {};
            tweetstatus[tweetid] = checkstatus;
            tweetchecks.push(tweetstatus);
        });
        console.log(tweetchecks);
        $.ajax({
            type:"POST",
            url:"checkposts.php",
            data:{tweetchecks:JSON.stringify(tweetchecks)},
            success:function(data){ 
                $("#hashtagerror").html(data);
                $("#hashtagerror").show();
            },
            /*async:false*/
        });
    }
    else{
        $("#hashtagerror").html("Choose Hashtag");
        $("#hashtagerror").show();
    }
}

function Generate(){
    $("#hashtagerror").hide();
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(hashtag_name != ""){
        hashtagdata = {
                    since:sincedate, 
                    until:untildate,
                    hashtagname:hashtag_name,
                    hashtagid:hashtag_id,
                    searchterm:searchterm,
                    posttypes:posttypes
                };
        $.ajax({
                type:"POST",
                url:"gethashtagposts.php",
                data:{hashtagdata:hashtagdata},
                success:function(data){ 
                    $("#hashtagposts").html(data);
                },
                /*async:false*/
            });
    }
    else{
        $("#hashtagerror").html("Choose Hashtag");
        $("#hashtagerror").show();
    }
}

function gotoPage(pagelink){
    window.location.href = pagelink;
}

function CSV(){

}
split = function(val) {
  return val.split( /,\s*/ );
};
extractLast = function(term) {
  return split(term).pop();
};

</script>



</body>
</html>
