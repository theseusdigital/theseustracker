<?php
session_start();
    require_once '../../includes/functions.php';
    if(!isset($_SESSION['user']))
    {
        redirect_to("../../index.php");
    }
    require '../../includes/connection.php';

    $user = $_SESSION['user'];
    $username = $user['username'];

    $todaydate = date("Y-m-d");
    $daydiff = 60*60*24;
    $since = date("Y-m-d",strtotime($todaydate) - $daydiff*2);

    $hashtagquery = "select id,name from tracker_hashtag where active = 1";
    $result = mysql_query($hashtagquery);
    $hashtag_map = array();
    $hashtags = array();
    while($row = mysql_fetch_array($result)) {
        $hashtag_map[$row['name']] = $row['id'];
        array_push($hashtags, $row['name']);
    }

    if(isset($_SESSION['hashtagtweets'])){  
        $hashtagtweets = $_SESSION['hashtagtweets']; 
    }
    else{
        $hashtagtweets = array();
    }
    if(isset($_SESSION['hashtagname'])){
        $hashtagname = $_SESSION['hashtagname'];
    }
    else{
        $hashtagname = '';
    }
    $tweetcolors = array('blue','red','orange','violet');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="author" content="Romil" />
    <meta name="description" content="Social Wall">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Social Wall</title>
    
    <!--------------------- Fav Icon ---------------------->
    <link rel="shortcut icon" href="../../images/favicon.ico">
    
    <!------------- Stylesheets ----------->
    <link href="../css/stylesheet.css" rel="stylesheet"> <!-- Main Stylesheet -->
    <!-- <link href="../css/reset.css" rel="stylesheet"> <!-- 3d Cube Effect -->
    <link href="../css/refineslide.css" rel="stylesheet"> <!-- 3d Cube Effect -->  
    
    
    <!------------- Javascripts ----------->
    <script src="../js/jquery-2.1.0.min.js" type="text/javascript"></script>
    <script src="../js/jquery.js" type="text/javascript"></script>  <!-- 3d Cube Effect -->
    <script src="../js/jquery.refineslide.js" type="text/javascript"></script> <!-- 3d Cube Effect -->
    
    
    <!-- 3d Cube Effect -->
    <script> 
        $(document).ready(function(){
            since = <?php echo json_encode($since); ?>;
            hashtag_map = <?php echo json_encode($hashtag_map); ?>;
            hashtags = <?php echo json_encode($hashtags); ?>;
            hashtagtweets = <?php echo json_encode($hashtagtweets); ?>;
            selected_hashtag = <?php echo json_encode($hashtagname); ?>;

            if(selected_hashtag != ''){
                hashtag_id = hashtag_map[selected_hashtag];
            }

            tweetslider();
            if(hashtagtweets.length > 0){
                changeTweet(0);
            }
            else{
                emptyTweet(0);
              }
            $(document).keydown(function(e) {
                if (e.keyCode == 27) { 
                    gotoPage("..");
                }
            });
            var refreshTweets;
            refreshtimer();

            $(".rs-slider").click(function(){
                if(tweet){
                    window.open(tweet['tweeturl'], '_blank');
                }
            });
        });

        refreshtimer = function(){
            refreshTweets = setInterval(getTweets, 60000);
        }

        tweetslider = function(){
            var $upper = $('#upper');

            $('#walltweets').refineSlide({
                delay:5000,
                transition : 'cubeH',
                onInit : function () {
                    var slider = this.slider,
                       $triggers = $('.translist').find('> li > a');

                    $triggers.parent().find('a[href="#_'+ this.slider.settings['transition'] +'"]').addClass('active');

                    $triggers.on('click', function (e) {
                       e.preventDefault();

                        if (!$(this).find('.unsupported').length) {
                            $triggers.removeClass('active');
                            $(this).addClass('active');
                            slider.settings['transition'] = $(this).attr('href').replace('#_', '');
                        }
                    });

                    function support(result, bobble) {
                        var phrase = '';

                        if (!result) {
                            phrase = ' not';
                            $upper.find('div.bobble-'+ bobble).addClass('unsupported');
                            $upper.find('div.bobble-js.bobble-css.unsupported').removeClass('bobble-css unsupported').text('JS');
                        }
                    }

                    support(this.slider.cssTransforms3d, '3d');
                    support(this.slider.cssTransitions, 'css');
                },
                onChange : function () {
                  tweetpos = this.slider['currentPlace'];
                  if(hashtagtweets.length > 0){
                    changeTweet(tweetpos);
                  }
                  else{
                    emptyTweet(tweetpos);
                  }
                }
            });
        };

        changeTweet = function(tweetpos){
          //console.log($scope.cindex);
          //index = Math.floor(Math.random() * $scope.alltweets.length);
          tweet = hashtagtweets[tweetpos];
          tweetid = tweetpos + 1;
          $('.tweettitle'+tweetid).html(tweet['name']);
          $('.tweettext'+tweetid).html(tweet['message']);
          $('.tweetname'+tweetid).html('@'+tweet['screenname']);
          $('.tweetage'+tweetid).html(tweet['age']);
          if(tweet['name'] == "No Tweets"){
            tweet['image'] = "../"+tweet['image'];
          }
          $('.tweetimage'+tweetid).attr('src',tweet['image']);
        };

        emptyTweet = function(tweetpos){
            tweet = false;
            tweetid = tweetpos + 1;
            $('.tweettitle'+tweetid).html("No Tweets");
            $('.tweettext'+tweetid).html("No Tweets");
            $('.tweetname'+tweetid).html("@notweets");
            $('.tweetage'+tweetid).html("no tweets");
            $('.tweetimage'+tweetid).attr('src',"../images/default.jpg");
        };

        getTweets = function(){
            if(selected_hashtag != ''){
                hashtag_data = {
                                hashtagid:hashtag_id,
                                name:selected_hashtag,
                                sincedate:since
                            };
                console.log(hashtag_data);
                $.ajax({
                    type:"GET",
                    url:"../hashtagtweets.php",
                    data:{hashtagdata:hashtag_data},
                    success:function(tweetdata){ 
                        hashtagtweets = JSON.parse(tweetdata);
                        console.log(hashtagtweets);
                    }
                });
            }
        };

        function gotoPage(pagelink){
            window.location.href = pagelink;
        }
    </script>
    
    

    <style>
        .tablegridfull {            
            width: 100%; 
            height: 100%;
            color: #404040;
            text-align: left;
            border-collapse: collapse;
            opacity: 0.5;
        }
        
        .users {
            /*background-color: #666666;*/
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            border-style: solid;    
            border: 2px solid #555;
        }

        #walltweets{
            cursor:pointer;
        }
    </style>    
</head>
    
    
    
    
<body style="background-color: #fff;">
    
<div class="fullscreen-pics-container">    
<table class="tablegridfull">

<!-- ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/766000776732811264/323w47z6_normal.jpg);" title="Yogesh Desai">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/633210816485965824/0cQi0eZS_normal.jpg);" title="CNBCTV18Live">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/509231623036891136/GhO5lERO_normal.jpeg);" title=" Shakeel">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_1_normal.png);" title="proj_tweet">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/2598625332/3i0d26dkyst17qrdehp3_normal.jpeg);" title="Kumar20Kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/768883894947049472/4zMx0dYU_normal.jpg);" title="EnSaluja">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/681428255862177792/cAGu23GX_normal.jpg);" title="Jerald_2305">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/766295500974911488/Gb9bgs-i_normal.jpg);" title="CYogesh09">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/756449610651041792/DYlXFICy_normal.jpg);" title="ramkadam">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/697313908655321088/e6f1VDF4_normal.jpg);" title="vallabh86">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/704444606981648384/prLfY7vf_normal.jpg);" title="nigelcnbctv18">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/765958610266558464/2d3t2DBw_normal.jpg);" title="Sky Enterprises">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774860073671602176/8J0ruJa3_normal.jpg);" title="AnguluruNikitha">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/567285805639405570/NPcqjtYS_normal.png);" title="Future_Generali">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774603744814125056/3wjeXatY_normal.jpg);" title="nandkishore">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772502458236964864/J1TwFPfX_normal.jpg);" title="iAjKP">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/760727186840096768/60PPKxGi_normal.jpg);" title="trade expert">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/501112734344151043/CGWnpJj__normal.jpeg);" title="Genius Chess Academy">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/745806872213499904/qJFkpwOU_normal.jpg);" title="Atul Sharma">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/704184547500371969/YXC6f5zk_normal.jpg);" title="shyam__bala">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/546880223665324032/MuyB_xQ7_normal.png);" title="Annamalai_Cap">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774895538546278400/Y25hUOkX_normal.jpg);" title="investfirst1">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/708898818763304960/se2WomTl_normal.jpg);" title="sirajuddinada">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773002398801551360/yY-PDy3a_normal.jpg);" title="Vishal Satra">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773066245176459264/qO9SmGWx_normal.jpg);" title="Manish Chaturvedi">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/642749804448120836/1ZPOp2N6_normal.jpg);" title="Monica Wu">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/301530820/tukey4ja_normal.png);" title="bankbank">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/730817878522355712/4ZfBjBrx_normal.jpg);" title="Sameer Joshi">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/724132028824883200/C0ce--tG_normal.jpg);" title="blitzkreigm">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/711923465779945472/hgnKzb38_normal.jpg);" title="PhaneendraChiruvella">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772281110453559296/kAzm2MWv_normal.jpg);" title="preethamgupta">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_5_normal.png);" title="Prabjit Gill">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/516473276088852480/A5jb6xvh_normal.jpeg);" title="ChweeneyTodd">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/704133497925971969/YGk4n87a_normal.jpg);" title="jain12_anisha">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/1095277188/rklakshman_normal.jpg);" title="vishy64theking">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_2_normal.png);" title="LatestNewzIndia">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/765811505728139265/fVH9F1zO_normal.jpg);" title="CNBCTV18News">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/588583177679020033/yOPw64_n_normal.jpg);" title="prashantwalk">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774834504422813696/hiV1Cc5z_normal.jpg);" title="Prashant Desai">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/694455487593603072/hU5_cis8_normal.jpg);" title="Wealocity">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/750987638186713089/5XJfWC8M_normal.jpg);" title="s_navroop">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/700520507385380864/Og6sTfDo_normal.jpg);" title="sami87293">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/770791295921037313/iDkGRx-S_normal.jpg);" title="R K Chopra">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_4_normal.png);" title="Ravi detroja">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/678151229415948288/tAI643Y6_normal.jpg);" title="neerav_sanghvi">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_5_normal.png);" title="Sonal Nagar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774302405022408704/TBDneLO2_normal.jpg);" title="SCAM KA AACHAR">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774647083122905088/LoTuo2Vx_normal.jpg);" title="Sumit kumar Patel">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/378800000074860945/034767d9f818dd916f9938b7bfd110f0_normal.jpeg);" title="bijay choudhury">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/319414230/DSC00105_normal.JPG);" title="jugs_02">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/3700284421/afd2be0546d4a9ce6062cee989458912_normal.jpeg);" title="rawatnaren">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/729246894271168512/pgrHX2jd_normal.jpg);" title="PZdziechowski">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/702901720246939650/ZBn4aySo_normal.jpg);" title="sanjay99000">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/763520940097564672/1AJ70Drd_normal.jpg);" title="ajay kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/547105028868042752/WAsMkvJO_normal.jpeg);" title="sudhanshu_agr">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/771653842458902528/xT2ZfGY6_normal.jpg);" title="SreejithJoshy">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/754704814882172929/3pS0Vkj5_normal.jpg);" title="Puneet Thakur">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/753642493514813440/9GR8gt8d_normal.jpg);" title="RonBanter">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/725012570634096640/8gNtkOqO_normal.jpg);" title="Snigdh Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774271527651008512/jPNX6nWB_normal.jpg);" title="??Bunny tyagi-+?">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_1_normal.png);" title="GIRIDHARAN">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/718700301130137601/ytOKrx1E_normal.jpg);" title="Manish Verma">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/706473522416259072/F_kgHlCw_normal.jpg);" title="AMIT KUMAR BISWAL">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774966014471733249/jE2FlLCH_normal.jpg);" title="Ajay Sharma">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/619158380700045312/I82hcB_T_normal.jpg);" title="JayThakkar22">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774946274432856064/wmElBBOx_normal.jpg);" title="Vidarbha Hub">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/648484554748002304/2DzVrt7R_normal.jpg);" title="prayag">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/745963552968695808/gRs7xbAl_normal.jpg);" title="chiragpatel">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/578563544490188801/q3Xcc5bf_normal.jpeg);" title="UnagK">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_3_normal.png);" title="Rastriya Janvarta">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/478939690071244800/wIG8eSn8_normal.jpeg);" title="KhubPetuk1">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/771923219712442368/ZlW45rSP_normal.jpg);" title="Deo Darshan Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/735056099011792896/36BMOPtR_normal.jpg);" title="Shivendra Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/761907342569013248/fZlJPbNd_normal.jpg);" title="sarachaudhary41">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/762718254158778369/f76bEHBH_normal.jpg);" title="Piyush Bansal">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774548364365103105/PA-0pijw_normal.jpg);" title="???? ????? ???">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/762934955286626304/P4ByqWVo_normal.jpg);" title="YESBANK">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772313772086075393/nyTpSHMW_normal.jpg);" title="hiren mashru">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_4_normal.png);" title="Rajendra Todalbagi">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/512297125753794560/BA1bQ_tZ_normal.jpeg);" title="krishworlds">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/733513923190657024/wJ07xJyq_normal.jpg);" title="lionelaranha">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/711127850749825024/peSDtkJo_normal.jpg);" title="moneycontrolcom">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/378800000739838906/3e4ef572a82e547341dd5a2d9a40e599_normal.jpeg);" title="al_ishbaa">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774155411331002368/zBWSEncg_normal.jpg);" title="Bhikam Chand">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/603053371872837632/xJMSWTr3_normal.jpg);" title="sagomedia">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/484113748638986241/5NBGWLK6_normal.jpeg);" title="Sayan Depp">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/653287007146242048/tDiUzLfg_normal.jpg);" title="Vijaay Kadechkar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/713971050053312512/VZmKIw1t_normal.jpg);" title="Hiteshkumar Dholiya">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/659708369607921664/zq98LSpY_normal.jpg);" title="TeaboxInt">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/428756624551858177/3JkrB0x3_normal.jpeg);" title="jg2009">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/1251157827/Jaspreet_normal.JPG);" title="Jaspreet">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/768357630918090752/Tra119So_normal.jpg);" title="Manali">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_5_normal.png);" title="Sandeep Kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/427407912336904193/1pFN6T_G_normal.jpeg);" title="Rahul Gupta">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/755986588555804675/ozg9y1iX_normal.jpg);" title="Vandana Goyal">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/763669858114478080/YXgRAy6U_normal.jpg);" title="indrajeetQuotes">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/721260143544033280/fmCKsGol_normal.jpg);" title="kapilt4">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773497328720351232/HwBpdZSQ_normal.jpg);" title="odmag">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/745009706133913600/Umanprz7_normal.jpg);" title="shumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/688708532154179584/YUrM0gQE_normal.jpg);" title="JATINDER SINGH">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/624828328961142784/jNRBTZ_C_normal.jpg);" title="gsrandhir">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/756117820371591169/a5kQTcQx_normal.jpg);" title="chitrapandit">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/519685867401510912/ocKzrQq__normal.jpeg);" title="Imrankausar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772414439454830594/cPYkvIpj_normal.jpg);" title="Akanksha_India">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/680766082756390912/0D2P634v_normal.jpg);" title="amihsanali">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_2_normal.png);" title="krishna">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774498755353665536/bMRKIPu0_normal.jpg);" title="NIRAnjan kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/696719680564817920/E3wWTfoW_normal.png);" title="trendinaliaIN">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/670255819456315392/6JafWdr8_normal.jpg);" title="sanjeetkumarit">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/762992763654795264/lGOIXaam_normal.jpg);" title="PoddarNisha">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/457314167678971904/Bkq_iszI_normal.jpeg);" title="golechhaashish">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/420085316217630723/4bIWGac9_normal.jpeg);" title="Sita Garg">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/768814188080824320/kRCukXxf_normal.jpg);" title="Rajinder Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/378800000738397873/66736c8a2f3c89511234b5442afd4810_normal.jpeg);" title="@maddypillai">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/622908582086987779/PlpMXHEK_normal.jpg);" title="indianbjp1">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/670831673412423680/-if5Fwhl_normal.jpg);" title="prash_pujari">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/2174217101/cross_bigger_normal.jpg);" title="PrayerPeace">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/731911509371179008/svkOvpgN_normal.jpg);" title="Anupam Srivastava">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_6_normal.png);" title="NSEL_duped">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772161799940546560/Etaq7fCK_normal.jpg);" title="RamaNathan94">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/753918148391219200/G1PjRoHH_normal.jpg);" title="sunil_sjain">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/762235063262031873/qZ2JKUFo_normal.jpg);" title="Farhan_Work">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_4_normal.png);" title="Naveen Jain">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_6_normal.png);" title="Sushil Kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/751862146712166404/Xv9Z0imp_normal.jpg);" title="bala_rabhouse">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_2_normal.png);" title="Ravi Kochak">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/724206524843876352/SJjgJWiU_normal.jpg);" title="Arun Chopra">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/707244037594423300/qtMYtp5h_normal.jpg);" title="Santosh Tiwari">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/724661440381091842/4ZOWU2HY_normal.jpg);" title="Rohit Mehta">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/715420658247794688/ov3nQhFR_normal.jpg);" title="Rocky Gurbani">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/769833201476866048/3jEiIcxe_normal.jpg);" title="prasannaj8">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/625989503690248192/GHysoypn_normal.jpg);" title="Jaggumaratha1">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773739220749934592/nvuY9Cta_normal.jpg);" title="kusum1986">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773935166318342144/ZdrT-0ZJ_normal.jpg);" title="Aditya prakash">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774246589191876609/5-6XnbZX_normal.jpg);" title="Rashmi">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/767293288370016256/kqp8PvMX_normal.jpg);" title="bertrandOD">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/751791658803560448/dzAjLKtL_normal.jpg);" title="Rohit_Paradkar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/682088458932719616/Q_vguV3o_normal.jpg);" title="hanmireddy">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/1360512861/211915_1560781420_1605069_n_normal.jpg);" title="aparanjape">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/2813795160/ca4fcea7b18c038639db0a38ee5ed7ab_normal.jpeg);" title="neeran">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/502070523900096513/T9v4a48H_normal.png);" title="Shweta_sahaye">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/721749340285509632/fpCetQdl_normal.jpg);" title="VRAGGN">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/734424713351561216/MxJ6cTOt_normal.jpg);" title="diwakarSatish">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/489263144254967808/yoz0tjlc_normal.jpeg);" title="aman_taneja1984">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/485691290869182464/Pm8VJw1i_normal.jpeg);" title="iasexamportal">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/1008150745/2_normal.JPG);" title="VijayGowdaInc">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/525023231/abhijit_1_normal.JPG);" title="abhijitmalge">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/741309481120829440/NKUXlhZz_normal.jpg);" title="Mahesh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/538722937029656576/TWzDWINf_normal.jpeg);" title="dtruthisouthere">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/626866124592934912/3kdL2pnd_normal.jpg);" title="shanmugam_18">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/750643560064638976/mCQwT60q_normal.jpg);" title="Abhay Kumar bairwa">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773202720387952640/mIxsoaqM_normal.jpg);" title="Vinay Gupta">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773429725444317185/NF89qItM_normal.jpg);" title="DEV NARAYAN">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774482684294733824/0a74UsUk_normal.jpg);" title="BSECrackTheCode">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/627867708743286784/uCJa-QRp_normal.jpg);" title="rks196">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/766970956321619968/Qj7UpGLV_normal.jpg);" title="Rahul Patil">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/716103608278720513/WAukpOjT_normal.jpg);" title="vinod kumar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/773318794412294146/PDg1JAV-_normal.jpg);" title="Utpal kant">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/768439461713682433/M_laW4T1_normal.jpg);" title="HCAwards2014">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/774135828826034177/_NV6Tgw5_normal.jpg);" title="shakil ahmad">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_5_normal.png);" title="raj sonu">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_4_normal.png);" title="ketanchahwala">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid -->
</tr><!-- end ngRepeat: grid in usergrid --><tr ng-repeat="grid in usergrid" class="ng-scope">
<!-- ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/705470661313982465/FOTbY2D9_normal.jpg);" title="NavinsinghIn">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/603464050056376321/s4SXXQPE_normal.jpg);" title="Khoslarajat10">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_3_normal.png);" title="Shailendra Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/229309754/arun_giri180_normal.jpg);" title="arungiri">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/559791429682089985/r9RX--tf_normal.jpeg);" title="Shailendra Singh">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/768131736274341888/MJCxvd6c_normal.jpg);" title="SWAPNIL MAHAJAN">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/581062464487301121/USXJrUco_normal.jpg);" title="rajarien">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/761895585108615168/tZ_i8-xg_normal.jpg);" title="Sohini_Dutt">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/759652471891333120/rn-8jJgt_normal.jpg);" title="Saswat Mishra">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/660820780071055361/n-69red-_normal.png);" title="AnyBodyCanFly">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/754773085430001664/pmDt6SxI_normal.jpg);" title="DrHRBHADU">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/604209584840953856/W35pU_Jz_normal.jpg);" title="ambedkarperiyar">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/730328334924550144/2iYPCbXo_normal.jpg);" title="dhruvbhim">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/747105318937702400/jPSKTz_m_normal.jpg);" title="dhavalgandhi007">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/772632237883863040/JfwvnE7g_normal.jpg);" title="ROHITKUMBHOJKAR">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/753966440517136384/pfjk8MuN_normal.jpg);" title="anuradhasays">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope" style="width:5.555555555555555%;height:10%;background-image:url(http://abs.twimg.com/sticky/default_profile_images/default_profile_2_normal.png);" title="ganesh das">
<!-- {{ user.name }} -->
</td><!-- end ngRepeat: user in grid --><td ng-repeat="user in grid" class="users ng-scope ticker" style="width:5.555555555555555%;height:10%;background-image:url(http://pbs.twimg.com/profile_images/529865142477942784/UG2tFWA9_normal.jpeg);" title="SMuraly">
<!-- {{ user.name }} --><div>ro</div>
</td><!-- end ngRepeat: user in grid -->
</tr>
</table>
    
<!-- Tweets Start -->    
<div class="fullscreen-twitter-container">
    <ul id="walltweets" class="rs-slider">                   
        <li>
        <div class="fullscreen-carousel-container">
            <div class="profile-pic-left">
                <div class="fullscreen-profile-pic-left-container"><img class="tweetimage1" src=""/></div>
            </div>
            <div class="fullscreen-tweet-container-blue">
                <div class="fullscreen-blue-title tweettitle1"></div>
                <div class="fullscreen-tweet tweettext1">
                     
                </div>
                <div class="fullscreen-tweet-info-blue">
                    <span class="fullscreen-name tweetname1"></span>
                    <span class="fullscreen-mins tweetage1"></span>
                </div>
            </div>
        </div>
        </li>

        <li>
        <div class="fullscreen-carousel-container">                    
            <div class="fullscreen-tweet-container-red">
                <div class="fullscreen-red-title tweettitle2"></div>
                <div class="fullscreen-tweet tweettext2">
                  
                </div>
                <div class="fullscreen-tweet-info-red">
                    <span class="fullscreen-name tweetname2"></span>
                    <span class="fullscreen-mins tweetage2"></span>
                </div>
            </div>
            <div class="profile-pic-right">
                <div class="fullscreen-profile-pic-right-container"><img class="tweetimage2" src=""/></div>
            </div>
        </div>
        </li>
            
        <li>
        <div class="fullscreen-carousel-container">
            <div class="profile-pic-left">
                <div class="fullscreen-profile-pic-left-container"><img class="tweetimage3" src=""/></div>
            </div>
            <div class="fullscreen-tweet-container-orange">
                <div class="fullscreen-orange-title tweettitle3"></div>
                <div class="fullscreen-tweet tweettext3">
                    
                </div>
                <div class="fullscreen-tweet-info-orange">
                    <span class="fullscreen-name tweetname3"></span>
                    <span class="fullscreen-mins tweetage3"></span>
                </div>
            </div>
        </div>
        </li>
            
        <li>
        <div class="fullscreen-carousel-container">                    
            <div class="fullscreen-tweet-container-violet">
                <div class="fullscreen-violet-title tweettitle4"></div>
                <div class="fullscreen-tweet tweettext4">
                  
                </div>
                <div class="fullscreen-tweet-info-violet">
                    <span class="fullscreen-name tweetname4"></span>
                    <span class="fullscreen-mins tweetage4"></span>
                </div>
            </div>
            <div class="profile-pic-right">
                <div class="fullscreen-profile-pic-right-container"><img class="tweetimage4" src=""/></div>
            </div>
        </div>
        </li>

        <li>
        <div class="fullscreen-carousel-container">
            <div class="profile-pic-left">
                <div class="fullscreen-profile-pic-left-container"><img class="tweetimage5" src=""/></div>
            </div>
            <div class="fullscreen-tweet-container-blue">
                <div class="fullscreen-blue-title tweettitle5"></div>
                <div class="fullscreen-tweet tweettext5">
                    
                </div>
                <div class="fullscreen-tweet-info-blue">
                    <span class="fullscreen-name tweetname5"></span>
                    <span class="fullscreen-mins tweetage5"></span>
                </div>
            </div>
        </div>
        </li>

        <li>
        <div class="fullscreen-carousel-container">                    
            <div class="fullscreen-tweet-container-red">
                <div class="fullscreen-red-title tweettitle6"></div>
                <div class="fullscreen-tweet tweettext6">
                  
                </div>
                <div class="fullscreen-tweet-info-red">
                    <span class="fullscreen-name tweetname6"></span>
                    <span class="fullscreen-mins tweetage6"></span>
                </div>
            </div>
            <div class="profile-pic-right">
                <div class="fullscreen-profile-pic-right-container"><img class="tweetimage6" src=""/></div>
            </div>
        </div>
        </li>
            
        <li>
        <div class="fullscreen-carousel-container">
            <div class="profile-pic-left">
                <div class="fullscreen-profile-pic-left-container"><img class="tweetimage7" src=""/></div>
            </div>
            <div class="fullscreen-tweet-container-orange">
                <div class="fullscreen-orange-title tweettitle7"></div>
                <div class="fullscreen-tweet tweettext7">
                    
                </div>
                <div class="fullscreen-tweet-info-orange">
                    <span class="fullscreen-name tweetname7"></span>
                    <span class="fullscreen-mins tweetage7"></span>
                </div>
            </div>
        </div>
        </li>
            
        <li>
        <div class="fullscreen-carousel-container">                    
            <div class="fullscreen-tweet-container-violet">
                <div class="fullscreen-violet-title tweettitle8"></div>
                <div class="fullscreen-tweet tweettext8">
                  
                </div>
                <div class="fullscreen-tweet-info-violet">
                    <span class="fullscreen-name tweetname8"></span>
                    <span class="fullscreen-mins tweetage8"></span>
                </div>
            </div>
            <div class="profile-pic-right">
                <div class="fullscreen-profile-pic-right-container"><img class="tweetimage8" src=""/></div>
            </div>
        </div>
        </li>

        <li>
        <div class="fullscreen-carousel-container">
            <div class="profile-pic-left">
                <div class="fullscreen-profile-pic-left-container"><img class="tweetimage9" src=""/></div>
            </div>
            <div class="fullscreen-tweet-container-blue">
                <div class="fullscreen-blue-title tweettitle9"></div>
                <div class="fullscreen-tweet tweettext9">
                     
                </div>
                <div class="fullscreen-tweet-info-blue">
                    <span class="fullscreen-name tweetname9"></span>
                    <span class="fullscreen-mins tweetage9"></span>
                </div>
            </div>
        </div>
        </li>

        <li>
        <div class="fullscreen-carousel-container">                    
            <div class="fullscreen-tweet-container-red">
                <div class="fullscreen-red-title tweettitle10"></div>
                <div class="fullscreen-tweet tweettext10">
                  
                </div>
                <div class="fullscreen-tweet-info-red">
                    <span class="fullscreen-name tweetname10"></span>
                    <span class="fullscreen-mins tweetage10"></span>
                </div>
            </div>
            <div class="profile-pic-right">
                <div class="fullscreen-profile-pic-right-container"><img class="tweetimage10" src=""/></div>
            </div>
        </div>
        </li>
    </ul> 
</div>
<!-- Tweets End -->
    
</div>
    
    


<!-- Bottom Branding Starts -->    
<div class="fullscreen-branding-container">           
    <div class="brand-logo-fullscreen"><span class="align-helper"></span><img src="../images/historytv18-logo.png" /></div>
    <div class="hastags-container">
        <div class="popular">Tweets With</div>
        <div class="hashtag">     
            <span class="hashtag-<?php echo $tweetcolors[0]; ?>"><?php echo $hashtagname; ?></span>
        </div>
    </div>
    <div class="goonj-logo-fullscreen"><span class="align-helper"></span><img src="../images/logo-404-big.png" /></div>        
</div>
<!-- Bottom Branding End -->

</body>
</html>