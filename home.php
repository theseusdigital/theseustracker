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

    $todaydate = date("Y-m-d");
    $weekdiff = 60*60*24*6;
    $daydiff = 60*60*24;
    $until = date("Y-m-d",strtotime($todaydate) - $daydiff);
    $since = date("Y-m-d",strtotime($until) - $weekdiff);

    if(!isset($_SESSION['since']))
    {
        $_SESSION['since'] = $since;
        $_SESSION['until'] = $until;
    }

    $brandquery = "select br.id,br.name,bh.handle_id from tracker_brand br inner join tracker_brand_handle bh on br.id=bh.brand_id";
    $result = mysql_query($brandquery);
    $brand_list = array();
    $brand_map = array();
    while($row = mysql_fetch_array($result)) {
      if(!isset($brand_list[$row['id']])){
        $brand_list[$row['id']] = array("name"=>$row['name'],"handles"=>array());
      }
      array_push($brand_list[$row['id']]['handles'], $row['handle_id']);
      $brand_map[$row['name']] = $row['id'];
    }
    ksort($brand_list);
    asort($brand_map);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Social Tracker</title>
<link rel="icon" type="image/png" href="images/favicon.ico" />
<link href="css/style.css" type="text/css" rel="stylesheet" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.12.4.js"></script>
<script src="http://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
    <!-- <b>Home</b> -->
    <!-- &nbsp; <input type="button" value="Compare Brands" name="comparebrands" onclick="gotoPage('comparebrands.php')"/>
    &nbsp; <input type="button" value="Compare Posts" name="compareposts" onclick="gotoPage('compareposts.php')"/>
    &nbsp; <input type="button" value="Social Wall" name="socialwall" onclick="gotoPage('socialwall')"/>
    &nbsp; <input type="button" value="Sign Out" name="signout" onclick="gotoPage('home.php?logout=1')"/> -->
    <b>Home</b>
    <div class="menuwrap">
        <span class="menuarea">
            <a class="menu" href="#">Menu</a>
            <div class="dropdown">
                <ul>
                    <li><a href="comparebrands.php">Compare Brands</a></li>
                    <li><a href="compareposts.php">Compare Posts</a></li>
                    <li><a href="socialwall">Social Wall</a></li>
                    <li><a href="moderateposts.php">Moderate Posts</a></li>
                </ul>
            </div>
            <a class="logoutlink" href="home.php?logout=1">Logout</a>  
        </span>
    </div>
</div>
<div class="date">
    Choose your Brand: 
        <input type="text" id="brand" placeholder=" Choose Brand" value="<?php if(isset($_SESSION['brandname'])){ echo $_SESSION['brandname']; }?>">
        <!-- <select id="selectedbrand" class="select" style='width:150px;'>
            <option value=""> </option>
            <?php
                /*foreach($brand_list as $brandid=>$brandinfo){
                    echo "<option value='$brandid'>".$brandinfo['name']." </option>";
                }*/
            ?>   
        </select> -->
        &nbsp; <b id="branderror" style="display:none;">Choose Brand</b>
     <br/> <br/>       
    <label>Date from:</label> <input type='text' name='since' id='datepicker' value="<?php if(isset($_SESSION['since'])){ echo $_SESSION['since']; }?>"  style='width:100;' />  &nbsp; &nbsp; &nbsp;
    <label>Date to:</label> <input type='text' name='until' id='datepicker2' value="<?php if(isset($_SESSION['until'])){ echo $_SESSION['until']; }?>"  /> &nbsp; &nbsp; &nbsp;
    <input type="button" value="Generate" name="generate" onclick="Generate()" id="generate"/> &nbsp; &nbsp;
    <input type="button" value="Download CSV" name="csv" onclick="CSV()" id="csv"/> &nbsp; &nbsp;
    &nbsp;<img id="loader" style="display:none;" src="images/loader.gif" width="15" height="15">
</div>
<br/>
<br/>


<div id="brandnumbers">

</div>

 

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

    /*$('#selectedbrand').change(function() {
        brand_id = $('#selectedbrand').val();
        if(brand_id != ""){
            brand_handles = brand_list[brand_id]['handles'];
            console.log(brand_handles);
            $("#branderror").hide();
        }
    });*/
    brand_list = <?php echo json_encode($brand_list); ?>;
    brand_map = <?php echo json_encode($brand_map); ?>;
    brand_names = <?php echo json_encode(array_keys($brand_map)); ?>;

    brand_name = <?php if(isset($_SESSION['brandname'])){ echo json_encode($_SESSION['brandname']); }else{ echo json_encode(""); }?>;
    if(brand_name == ""){
        brand_id = "";
    }
    else{
        brand_id = brand_map[brand_name];
        brand_handles = brand_list[brand_id]['handles'];
    }

    $("#brand")
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
              brand_names, extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            brand = this.value;
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            /*var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(",");*/
            this.value = ui.item.value;
            brand_name = this.value;
            brand_id = brand_map[brand_name];
            brand_handles = brand_list[brand_id]['handles'];
            console.log(brand_handles);
            $("#branderror").hide();
            return false;
          }
        });
});

function Generate(){
    $("#brandnumbers").hide();
    $("#generate").val("Generating..");
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(brand_id != ""){
        branddata = {
                    since:sincedate, 
                    until:untildate,
                    brandname:brand_name,
                    brandid:brand_id,
                    brandhandles:brand_handles
                };
        $.ajax({
                type:"POST",
                url:"getnumbers.php",
                data:{branddata:branddata},
                success:function(data){ 
                    $("#brandnumbers").html(data);
                    $("#brandnumbers").fadeIn("slow");
                    $("#generate").val("Generate");
                }
            });
    }
    else{
        $("#branderror").show();
        $("#generate").val("Generate");
    }
    
}

function gotoPage(pagelink){
    window.location.href = pagelink;
}

function CSV(){
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(brand_id != ""){
        branddata = {
                    since:sincedate, 
                    until:untildate,
                    brandname:brand_name,
                    brandid:brand_id,
                    brandhandles:brand_handles
                };
        $.ajax({
                type:"POST",
                url:"homecsv.php",
                data:{branddata:branddata},
                success:function(data){ 
                    gotoPage('homecsv.php');
                }
            });
    }
    else{
        $("#branderror").show();
    }
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
