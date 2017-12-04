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
    <!-- <b>Home</b> -->
    <!-- &nbsp; <input type="button" value="Compare Brands" name="comparebrands" onclick="gotoPage('comparebrands.php')"/>
    &nbsp; <input type="button" value="Compare Posts" name="compareposts" onclick="gotoPage('compareposts.php')"/>
    &nbsp; <input type="button" value="Social Wall" name="socialwall" onclick="gotoPage('socialwall')"/>
    &nbsp; <input type="button" value="Sign Out" name="signout" onclick="gotoPage('home.php?logout=1')"/> -->
    <b>Charts</b>
    <div class="menuwrap">
        <span class="menuarea">
            <a class="menu" href="#">Menu</a>
            <div class="dropdown">
                <ul>
                	<li><a href="../home.php">Home</a></li>
                    <li><a href="../comparebrands.php">Compare Brands</a></li>
                    <li><a href="../compareposts.php">Compare Posts</a></li>
                    <li><a href="../hashtags.php">Hashtags</a></li>
                    <li><a href="../socialwall">Social Wall</a></li>
                    <li><a href="../moderateposts.php">Moderate Posts</a></li>
                    <!-- <li><a ui-sref="home2">Home2</a></li> -->
                </ul>
            </div>
            <a class="logoutlink" href="../home.php?logout=1">Logout</a>  
        </span>
    </div>
</div>
<div class="date">
    Choose Brands: 
        <input type="text" id="brand" placeholder=" Choose Brands" ng-model="selbrands" size="60">
        <!-- <select id="selectedbrand" class="select" style='width:150px;'>
            <option value=""> </option>
            <?php
                /*foreach($brand_list as $brandid=>$brandinfo){
                    echo "<option value='$brandid'>".$brandinfo['name']." </option>";
                }*/
            ?>   
        </select> -->
    &nbsp;
    Platform: 
        <input type="text" id="platform" placeholder=" Choose Platform" ng-model="selplatform">
        &nbsp; <b id="branderror">{{ inputerror }}</b>
     <br/> <br/>       
    <label>Date from:</label> <input type='text' name='since' id='datepicker' ng-model="since" style='width:100;' />  &nbsp; &nbsp; &nbsp;
    <label>Date to:</label> <input type='text' name='until' id='datepicker2' ng-model="until" /> &nbsp; &nbsp; &nbsp;
    Metric: 
        <input type="text" id="metric" placeholder=" Choose Metric" ng-model="selmetric"> &nbsp; &nbsp; &nbsp;
    <input type="button" value="Generate" name="generate" ng-click="generate()" id="generate"/> &nbsp; &nbsp;
    &nbsp;<img id="loader" style="display:none;" src="../images/loader.gif" width="15" height="15">
</div>
<br/>
<br/>


<div id="brandnumbers">

</div>

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Total</a></li>
    <li><a href="#tabs-2">Daywise</a></li>
  </ul>
  <div id="tabs-1">
    <hc-chart options="alldata['column']">Placeholder for generic chart</hc-chart>
  </div>
  <div id="tabs-2">
    <hc-chart options="alldata['line']">Placeholder for generic chart</hc-chart>
  </div>
</div>


<br>

<br />
<div class="powered">Powered by <a href="http://4nought4.com/"><img src="../images/logo-404.png" alt="" border="0" /></a></div>
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
        /*minDate: -60,*/ 
        maxDate: 0
    });
    $("#datepicker2").datepicker({
        dateFormat:'yy-mm-dd',
        /*minDate: -60,*/ 
        maxDate: 0
    });

    $(document).ajaxStart(function(){
        $("#loader").show();
    });
    
    $(document).ajaxStop(function(){
        $("#loader").hide();
    });

    $("#tabs").tabs();

    /*brand_list = <?php echo json_encode($brand_list); ?>;
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

    platform_map = <?php echo json_encode($platform_map); ?>;
    platform_names = <?php echo json_encode(array_keys($platform_map)); ?>;
    platform_name = <?php if(isset($_SESSION['platformname'])){ echo json_encode($_SESSION['platformname']); }else{ echo json_encode(""); }?>;

    if(platform_name == ""){
        platform_id = 1;
    }
    else{
        platform_id = platform_map[platform_name];
    }
*/
    
});

/*function Generate(){
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
                url:"../getnumbers.php",
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
    
}*/

function gotoPage(pagelink){
    window.location.href = pagelink;
}

split = function(val) {
  return val.split( /,\s*/ );
};

extractLast = function(term) {
  return split(term).pop();
};

</script>
