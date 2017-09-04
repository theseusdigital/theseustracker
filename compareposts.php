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

    if(!isset($_SESSION['platformname']))
    {
        $_SESSION['platformname'] = "facebook";
    }

    if(isset($_SESSION['brandsforposts']))
    {
        $brandsforposts = $_SESSION['brandsforposts'];
    }
    else{
        $brandsforposts = array();
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

    $searchterms = array(1=>"",2=>"");
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

.handlelink{
    font-size: 15px;
    cursor:pointer;
    color:#009999;
}

.handlelink:hover{
    font-size: 16px;
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
    <b>Compare Posts</b>
    <div class="menuwrap">
        <span class="menuarea">
            <a class="menu" href="#">Menu</a>
            <div class="dropdown">
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="comparebrands.php">Compare Brands</a></li>
                    <li><a href="socialwall">Social Wall</a></li>
                    <li><a href="moderateposts.php">Moderate Posts</a></li>
                </ul>
            </div>
            <a class="logoutlink" href="home.php?logout=1">Logout</a>  
        </span>
    </div>
</div>
<div class="date">
    Platform: 
        <input type="text" id="platform" placeholder=" Choose Platform" value="<?php if(isset($_SESSION['platformname'])){ echo $_SESSION['platformname']; }?>">
        <!-- <select id="selectedbrand" class="select" style='width:150px;'>
            <option value=""> </option>
            <?php
                /*foreach($brand_list as $brandid=>$brandinfo){
                    echo "<option value='$brandid'>".$brandinfo['name']." </option>";
                }*/
            ?>   
        </select> -->
        &nbsp; <b id="branderror" style="display:none;">Choose Atleast 1 Brand</b>
     <br/> <br/>       
    <label>Date from:</label> <input type='text' name='since' id='datepicker' value="<?php if(isset($_SESSION['since'])){ echo $_SESSION['since']; }?>"  style='width:100;' />  &nbsp; &nbsp; &nbsp;
    <label>Date to:</label> <input type='text' name='until' id='datepicker2' value="<?php if(isset($_SESSION['until'])){ echo $_SESSION['until']; }?>"  /> &nbsp; &nbsp; &nbsp;
    <input type="button" value="Generate" name="generate" onclick="GenerateAll()" id="generate"/> &nbsp; &nbsp; 
    <!-- <input type="button" value="Download .CSV" name="csv" onclick="CSV()" />&nbsp; &nbsp; -->
    &nbsp;<img id="loader" style="display:none;" src="images/loader.gif" width="15" height="15">
</div>
<br/>
<br/>

<table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
    <tr>
        <td valign="top" width="50%">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker1" dataid="1" class="brandpicker" placeholder=" Choose Brand">
                &nbsp;
                Search: 
                <input type="text" id="search1" dataid="1" class="searchterm" placeholder=" Search">
                <br/><br/>
            </div>
            <div id="brandposts1" class="brandposts">
            
            </div>
        </td>
        <td valign="top" width="50%">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker2" dataid="2" class="brandpicker" placeholder=" Choose Brand">
                &nbsp;
                Search: 
                <input type="text" id="search2" dataid="2" class="searchterm" placeholder=" Search">
                <br/><br/>
            </div>
            <div id="brandposts2" class="brandposts">
                
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

    brandhandlemap = <?php echo json_encode($brandhandlemap); ?>;
    brand_map = <?php echo json_encode($brand_map); ?>;
    brand_names = <?php echo json_encode(array_keys($brand_map)); ?>;
    brandsforposts = <?php echo json_encode($brandsforposts); ?>;

    for(dataid in brandsforposts){
        picker = "#picker"+dataid;
        $(picker).val(brandsforposts[dataid]['brandname']);
    }

    platform_map = <?php echo json_encode($platform_map); ?>;
    platform_names = <?php echo json_encode(array_keys($platform_map)); ?>;
    platform_name = <?php if(isset($_SESSION['platformname'])){ echo json_encode($_SESSION['platformname']); }else{ echo json_encode(""); }?>;

    if(platform_name == ""){
        platform_id = 1;
    }
    else{
        platform_id = platform_map[platform_name];
    }

    $("#platform")
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
              platform_names, extractLast( request.term ) ) );
          },
          focus: function() {
            // prevent value inserted on focus
            return false;
          },
          change: function( event, ui ) {
            platform = this.value;
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            this.value = ui.item.value;
            platform_name = this.value;
            platform_id = platform_map[platform_name];
            return false;
          }
        });

    $(".brandpicker")
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
            brandinput = this.value;
            if(brandinput == ""){
                data_id = $(this).attr("dataid");
                datasection = "#brandposts"+data_id;
                $(datasection).html("");
            }
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            data_id = $(this).attr("dataid");
            datasection = "#brandposts"+data_id;
            this.value = ui.item.value;
            brand_name = this.value;
            brand_id = brand_map[brand_name];
            handlesdata = getHandles(brand_id, brand_name, data_id);
            handles_list = handlesdata[0];
            handleid = handlesdata[1];
            if(handleid){
                Generate(handleid, data_id, brand_name);
            }
            else{
                $(datasection).html(handles_list);
            }
            $("#branderror").hide();
            return false;
          }
        });
    
    searchterms = <?php echo json_encode($searchterms); ?>;
    handledatamap = {};

    $(".searchterm").keyup(function(){
        searchvalue = $(this).val();
        data_id = $(this).attr("dataid");
        datasection = "#brandposts"+data_id;
        searchterms[data_id] = searchvalue;
        if((data_id in brandsforposts)){
            if(('brandid' in brandsforposts[data_id])){
                brand_id = brandsforposts[data_id]['brandid'];
                brand_name = brandsforposts[data_id]['brandname'];

                handlesdata = getHandles(brand_id, brand_name, data_id);
                handles_list = handlesdata[0];
                handleid = handlesdata[1];
                $("#branderror").hide();
                if(handleid){
                    Generate(handleid, data_id, brand_name);
                }
                else{
                    if((data_id in handledatamap)){
                        handleid = handledatamap[data_id];
                        Generate(handleid, data_id, brand_name);
                    }
                    else{
                        $(datasection).html(handles_list);
                    }
                }
            }
            else{
                $(datasection).html("<b class='handlelink'>Select Brand</b>");
            }
        }
        else{
            $(datasection).html("<b class='handlelink'>Select Brand</b>");
        }
    });

});

function getHandles(brand_id, brand_name, data_id){
    handleid = false;
    if(!(platform_id in brandhandlemap[brand_id])){
        handles_list = "<div class='title'><h5>No "+platform_name+" Handles</h5></div>";
    }
    else{
        brand_handles = brandhandlemap[brand_id][platform_id];
        if(brand_handles.length == 1){
            handles_list = "One Handle";
            handleid = brand_handles[0]['id'];
        }
        else{
            handles_list = "";
            for(hid in brand_handles){
                handle = brand_handles[hid];
                handles_list += "<b class='handlelink' onclick=\"Generate("+handle['id']+",'"+data_id+"','"+brand_name+"')\">"+handle['name']+"</b> &nbsp;";
            }
        }
    }
    return [handles_list, handleid];
}

function Generate(handle_id, data_id, brand_name){
    brand_id = brand_map[brand_name];
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    datasection = "#brandposts"+data_id;
    /*$(datasection).hide();*/
    if(brand_id != ""){
        branddata = {
                    since:sincedate, 
                    until:untildate,
                    brandname:brand_name,
                    brandid:brand_id,
                    dataid:data_id,
                    platformid:platform_id,
                    platformname:platform_name,
                    handleid:handle_id,
                    searchterms:searchterms
                };
        brandsforposts[data_id] = {"brandid":brand_id, "brandname":brand_name};
        handledatamap[data_id] = handle_id;
        $.ajax({
                type:"POST",
                url:"getposts.php",
                data:{branddata:branddata},
                success:function(data){ 
                    $(datasection).html(data);
                    /*$(datasection).fadeIn("slow");*/
                },
                async:false
            });
    }
    else{
        $("#branderror").show();
    }
}

function GenerateAll(){
    $("#generate").val("Generating..");
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(brandsforposts.length == 0){
        $("#branderror").show();
        $("#generate").val("Generate");
        return;
    }
    for(dataid in brandsforposts){
        data_id = dataid;
        brand_id = brandsforposts[dataid]['brandid'];
        brand_name = brandsforposts[dataid]['brandname'];
        datasection = "#brandposts"+data_id;
        handlesdata = getHandles(brand_id, brand_name, data_id);
        handles_list = handlesdata[0];
        handleid = handlesdata[1];
        $("#branderror").hide();
        if(handleid){
            Generate(handleid, data_id, brand_name);
        }
        else{
            $(datasection).html(handles_list);
        }
    }
    $("#generate").val("Generate");
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
