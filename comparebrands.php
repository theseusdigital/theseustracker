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

    if(isset($_SESSION['since']))
    {
        $since = $_SESSION['since'];
        $until = $_SESSION['until'];
    }
    if(isset($_SESSION['brandsfornumbers']))
    {
        $brandsfornumbers = $_SESSION['brandsfornumbers'];
    }
    else{
        $brandsfornumbers = array();
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
  .ui-autocomplete {
    z-index: 1100;
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
  }
</style>
</head>

<body>
<!-- wrapper starts -->
<div id="wrapper">
    <div id="topbar">
        <div class="toplinks" align="right"></div> <br /><br />
        <div class="report-heading">
            <h1>Theseus <font color="#009999">Tracker</font></h1>
            <!-- <h1> <img src="images/logo-404-big.png" alt="" style="vertical-align:bottom" /> Social Tracker</h1> -->
        </div>
        <!--<div class="client-logo" align="center"><img src="images/logo-kxip.png" alt=""  /></div>-->
        <div class="clear"></div>
    </div><!-- <br /><br /> -->
    
<div class="pagebuttons">
    <b>Compare Brands</b>
    <div class="menuwrap">
        <span class="menuarea">
            <a class="menu" href="#">Menu</a>
            <div class="dropdown">
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="compareposts.php">Compare Posts</a></li>
                    <li><a href="socialwall">Social Wall</a></li>
                    <li><a href="moderateposts.php">Moderate Posts</a></li>
                    <li><a href="charts">View Charts</a></li>
                </ul>
            </div>
            <a class="logoutlink" href="home.php?logout=1">Logout</a>  
        </span>
    </div>
</div>
<div class="date">    
    <label>Date from:</label> <input type='text' name='since' id='datepicker' value="<?php if(isset($_SESSION['since'])){ echo $since; }?>"  style='width:100;' />  &nbsp; &nbsp; &nbsp;
    <label>Date to:</label> <input type='text' name='until' id='datepicker2' value="<?php if(isset($_SESSION['until'])){ echo $until; }?>"  /> &nbsp; &nbsp; &nbsp;
    <input type="button" value="Generate" name="generate" onclick="GenerateAll()" id="generate"/> &nbsp; &nbsp;
    <input type="button" value="Download CSV" name="csv" onclick="CSV()" id="csv"/> &nbsp; &nbsp;
    <!-- <input type="button" value="Download .CSV" name="csv" onclick="CSV()" />&nbsp; &nbsp; -->
    &nbsp; <b id="branderror" style="display:none;">Choose Atleast 1 Brand</b>
    &nbsp;<img id="loader" style="display:none;" src="images/loader.gif" width="15" height="15">
</div>
<br/>
<br/>
<table width='100%' border='0' cellpadding='0' cellspacing='1' class='data-table'>
    <tr>
        <td valign="top">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker1" dataid="1" class="brandpicker" placeholder=" Choose Brand">
                <br/><br/>
            </div>
            <div id="brandnumbers1" class="brandnumbers">
            
            </div>
        </td>
        <td valign="top">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker2" dataid="2" class="brandpicker" placeholder=" Choose Brand">
                <br/><br/>
            </div>
            <div id="brandnumbers2" class="brandnumbers">
                
            </div>
        </td>
        <td valign="top">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker3" dataid="3" class="brandpicker" placeholder=" Choose Brand">
                <br/><br/>
            </div>
            <div id="brandnumbers3" class="brandnumbers">
                
            </div>
        </td>
        <td valign="top">
            <div>
                <br/>
                Brand: 
                <input type="text" id="picker4" dataid="4" class="brandpicker" placeholder=" Choose Brand">
                <br/><br/>
            </div>
            <div id="brandnumbers4" class="brandnumbers">
                
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

    brand_list = <?php echo json_encode($brand_list); ?>;
    brand_map = <?php echo json_encode($brand_map); ?>;
    brand_names = <?php echo json_encode(array_keys($brand_map)); ?>;

    brandsfornumbers = <?php echo json_encode($brandsfornumbers); ?>;
    for(dataid in brandsfornumbers){
        picker = "#picker"+dataid;
        $(picker).val(brandsfornumbers[dataid]['brandname']);
    }

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
                datasection = "#brandnumbers"+data_id;
                $(datasection).html("");
            }
            // prevent value inserted on change
            return false;
          },
          select: function( event, ui ) {
            data_id = $(this).attr("dataid");
            datasection = "#brandnumbers"+data_id;
            this.value = ui.item.value;
            brand_name = this.value;
            brand_id = brand_map[brand_name];
            brand_handles = brand_list[brand_id]['handles'];
            /*console.log(brand_handles);*/
            $("#branderror").hide();
            Generate();
            return false;
          }
        });
});

function Generate(){
    $(datasection).hide();
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(brand_id != ""){
        branddata = {
                    since:sincedate, 
                    until:untildate,
                    brandname:brand_name,
                    brandid:brand_id,
                    brandhandles:brand_handles,
                    dataid:data_id
                };
        brandsfornumbers[data_id] = {"brandid":brand_id, "brandname":brand_name};
        $.ajax({
                type:"POST",
                url:"comparenumbers.php",
                data:{branddata:branddata},
                success:function(data){ 
                    $(datasection).html(data);
                    $(datasection).fadeIn("slow");
                }
            });
    }
    else{
        $("#branderror").show();
    }
}

function GenerateAll(){
    $(".brandnumbers").hide();
    $("#generate").val("Generating..");
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();
    if(brandsfornumbers.length == 0){
        $("#branderror").show();
        $("#generate").val("Generate");
        return;
    }
    for(dataid in brandsfornumbers){
        data_id = dataid;
        brand_id = brandsfornumbers[dataid]['brandid'];
        brand_name = brandsfornumbers[dataid]['brandname'];
        brand_handles = brand_list[brand_id]['handles'];
        datasection = "#brandnumbers"+data_id;
        branddata = {
                    since:sincedate, 
                    until:untildate,
                    brandname:brand_name,
                    brandid:brand_id,
                    brandhandles:brand_handles,
                    dataid:data_id
                };
        /*console.log(branddata);*/
        picker = "#picker"+data_id;
        brandinput = $(picker).val();
        if(brandinput == ""){
            $(datasection).html("");
        }
        else{
            $.ajax({
                type:"POST",
                url:"comparenumbers.php",
                data:{branddata:branddata},
                success:function(data){ 
                    $(datasection).html(data);
                },
                async:false
            });
        }
        
    }
    $(".brandnumbers").fadeIn("slow");
    $("#generate").val("Generate");
}

function gotoPage(pagelink){
    window.location.href = pagelink;
}

function CSV(){
    sincedate = $('#datepicker').val();
    untildate = $('#datepicker2').val();

    if(brandsfornumbers.length == 0){
        $("#branderror").show();
    }
    else{
        branddata = {
                    since:sincedate, 
                    until:untildate
                };
        $.ajax({
                type:"POST",
                url:"comparebrandscsv.php",
                data:{branddata:branddata},
                success:function(data){
                    gotoPage('comparebrandscsv.php');
                }
            });
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
