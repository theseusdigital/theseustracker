<?php
session_start();
    require_once '../includes/functions.php';
    if(!isset($_SESSION['user']))
    {
        redirect_to("../index.php");
    }
    require '../includes/connection.php';

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
    <link rel="shortcut icon" href="../images/favicon.ico">

    <link href="css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/cupertino/jquery-ui.css" />

    <!------------- Stylesheets ----------->
    <link href="css/stylesheet.css" rel="stylesheet"> <!-- Main Stylesheet -->
    <link href="css/reset.css" rel="stylesheet"> <!-- 3d Cube Effect -->
    <link href="css/refineslide.css" rel="stylesheet"> <!-- 3d Cube Effect -->  
    
    
    <!------------- Javascripts ----------->
    <script src="js/jquery-2.1.0.min.js" type="text/javascript"></script>
    <script src="js/jquery.js" type="text/javascript"></script>  <!-- 3d Cube Effect -->
    <script src="js/jquery.refineslide.js" type="text/javascript"></script> <!-- 3d Cube Effect -->

    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
    <!-- Dropdown Menu -->
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

        // Hashtag Modal Button code
        $("#hashes").click(function(){
            $('#hashtagbox').modal('show');
            $('.dropdown').slideUp('100');
        });

        $("#hashtags")
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
                hashtags, extractLast( request.term ) ) );
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
            /*var terms = split(this.value);
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push(ui.item.value);
            // add placeholder to get the comma-and-space at the end
            terms.push("");
            this.value = terms.join(",");*/
            this.value = ui.item.value;
            selected_hashtag = this.value;
            hashtag_id = hashtag_map[selected_hashtag];
            return false;
          }
        });
        tweetslider();
        if(hashtagtweets.length > 0){
            changeTweet(0);
        }
        else{
            emptyTweet(0);
          }
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

    split = function(val) {
      return val.split( /,\s*/ );
    };

    extractLast = function(term) {
      return split(term).pop();
    };

    tweetslider = function(){
        // 3d Cube Effect
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
      $('.tweetimage'+tweetid).attr('src',tweet['image']);
    };

    emptyTweet = function(tweetpos){
        tweet = false;
        tweetid = tweetpos + 1;
        $('.tweettitle'+tweetid).html("No Tweets");
        $('.tweettext'+tweetid).html("No Tweets");
        $('.tweetname'+tweetid).html("@notweets");
        $('.tweetage'+tweetid).html("no tweets");
        $('.tweetimage'+tweetid).attr('src',"images/default.jpg");
    };

    updateWall = function(tweetpos){
        clearInterval(refreshTweets);
        getTweets();
        refreshtimer();
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
                url:"hashtagtweets.php",
                data:{hashtagdata:hashtag_data},
                success:function(tweetdata){ 
                    hashtagtweets = JSON.parse(tweetdata);
                    console.log(hashtagtweets);
                }
            });
        }
    };

    resetHashtags = function(){
        $("#hashtags").val("");
    };

    function gotoPage(pagelink){
        window.location.href = pagelink;
    }
    </script>

<style type="text/css">
  .ui-autocomplete {
    z-index: 1100;
    max-height: 100px;
    overflow-y: auto;
    overflow-x: hidden;
  }
  .dropdownlinks{
    cursor:pointer;
  }
  #walltweets{
    cursor:pointer;
  }
</style>
</head>
    
<body>
    
<!--====================================================================================================
    MAIN WRAPPER START
=====================================================================================================-->
    
<div class="main-container">
    
<!-- ============== Header Start ============== -->
<div class="header-conatiner">
    
    <!-- <div class="brand-logo-container"><img src="images/cnbc-logo.PNG" /></div> -->
    <div class="header-title">Social <font color="#009999">Wall</font></div>   
    <div class="goonj-logo" style="padding:8px;"><a href="http://www.4nought4.com" target="_blank" alt="Powered By Theseus" title="Powered By Theseus"><img src="../images/logo-404-big.png" /></a></div>
    
    
    <div class="nav-container">
        <div class="menuwrap">
            <span class="menuarea">
                <a class="menu" href="#">Menu</a>
                <div class="dropdown">
                    <ul>
                        <li><a href="../home.php">Home</a></li>
                        <li><a id="hashes" class="dropdownlinks">Select Hashtags</a></li>
                        <li><a href="../moderateposts.php">Moderate Posts</a></li>
                    </ul>
                </div>
                <a class="link" href="../home.php?logout=1">Logout</a>  
            </span>          
        </div>
    </div>
    
</div>    
<!-- ============== Header Start ============== -->
    
<!-- ============== Content Start ============== -->
<div class="content-container">
    
    <div class="wall-container">        
        <div class="wall">            
            <a href="fullscreen"><div class="fullscreen-button">Go Fullscreen</div></a>
            
            <!-- Background Images Start -->
            <div class="pics">
                <img src="images/wall-pics.jpg"/>
            </div>
            <!-- Background Images End -->
            
            
            <!-- Tweets Start -->
            <div class="twitter-container">
                <ul id="walltweets" class="rs-slider">
                    <li>
                    <div class="carousel-container">
                        <div class="profile-pic-left-container"><img class="tweetimage1" src=""/></div>
                        <div class="tweet-container-blue">
                            <div class="blue-title tweettitle1"></div>
                            <div class="tweet tweettext1">
                                 
                            </div>
                            <div class="tweet-info-blue">
                                <span class="name tweetname1"></span>
                                <span class="mins tweetage1"></span>
                            </div>
                        </div>
                    </div>
                    </li>

                    <li>
                    <div class="carousel-container">                    
                        <div class="tweet-container-red">
                            <div class="red-title tweettitle2"></div>
                            <div class="tweet tweettext2">
                              
                            </div>
                            <div class="tweet-info-red">
                                <span class="name tweetname2"></span>
                                <span class="mins tweetage2"></span>
                            </div>
                        </div>
                        <div class="profile-pic-right-container"><img class="tweetimage2" src=""/></div>
                    </div>
                    </li>
                        
                    <li>
                    <div class="carousel-container">
                        <div class="profile-pic-left-container"><img class="tweetimage3" src=""/></div>
                        <div class="tweet-container-orange">
                            <div class="orange-title tweettitle3"></div>
                            <div class="tweet tweettext3">
                                
                            </div>
                            <div class="tweet-info-orange">
                                <span class="name tweetname3"></span>
                                <span class="mins tweetage3"></span>
                            </div>
                        </div>
                    </div>
                    </li>
                        
                    <li>
                    <div class="carousel-container">                    
                        <div class="tweet-container-violet">
                            <div class="violet-title tweettitle4"></div>
                            <div class="tweet tweettext4">
                              
                            </div>
                            <div class="tweet-info-violet">
                                <span class="name tweetname4"></span>
                                <span class="mins tweetage4"></span>
                            </div>
                        </div>
                        <div class="profile-pic-right-container"><img class="tweetimage4" src=""/></div>
                    </div>
                    </li>

                    <li>
                    <div class="carousel-container">
                        <div class="profile-pic-left-container"><img class="tweetimage5" src=""/></div>
                        <div class="tweet-container-blue">
                            <div class="blue-title tweettitle5"></div>
                            <div class="tweet tweettext5">
                                
                            </div>
                            <div class="tweet-info-blue">
                                <span class="name tweetname5"></span>
                                <span class="mins tweetage5"></span>
                            </div>
                        </div>
                    </div>
                    </li>

                    <li>
                    <div class="carousel-container">                    
                        <div class="tweet-container-red">
                            <div class="red-title tweettitle6"></div>
                            <div class="tweet tweettext6">
                              
                            </div>
                            <div class="tweet-info-red">
                                <span class="name tweetname6"></span>
                                <span class="mins tweetage6"></span>
                            </div>
                        </div>
                        <div class="profile-pic-right-container"><img class="tweetimage6" src=""/></div>
                    </div>
                    </li>
                        
                    <li>
                    <div class="carousel-container">
                        <div class="profile-pic-left-container"><img class="tweetimage7" src=""/></div>
                        <div class="tweet-container-orange">
                            <div class="orange-title tweettitle7"></div>
                            <div class="tweet tweettext7">
                                
                            </div>
                            <div class="tweet-info-orange">
                                <span class="name tweetname7"></span>
                                <span class="mins tweetage7"></span>
                            </div>
                        </div>
                    </div>
                    </li>
                        
                    <li>
                    <div class="carousel-container">                    
                        <div class="tweet-container-violet">
                            <div class="violet-title tweettitle8"></div>
                            <div class="tweet tweettext8">
                              
                            </div>
                            <div class="tweet-info-violet">
                                <span class="name tweetname8"></span>
                                <span class="mins tweetage8"></span>
                            </div>
                        </div>
                        <div class="profile-pic-right-container"><img class="tweetimage8" src=""/></div>
                    </div>
                    </li>

                    <li>
                    <div class="carousel-container">
                        <div class="profile-pic-left-container"><img class="tweetimage9" src=""/></div>
                        <div class="tweet-container-blue">
                            <div class="blue-title tweettitle9"></div>
                            <div class="tweet tweettext9">
                                 
                            </div>
                            <div class="tweet-info-blue">
                                <span class="name tweetname9"></span>
                                <span class="mins tweetage9"></span>
                            </div>
                        </div>
                    </div>
                    </li>

                    <li>
                    <div class="carousel-container">                    
                        <div class="tweet-container-red">
                            <div class="red-title tweettitle10"></div>
                            <div class="tweet tweettext10">
                              
                            </div>
                            <div class="tweet-info-red">
                                <span class="name tweetname10"></span>
                                <span class="mins tweetage10"></span>
                            </div>
                        </div>
                        <div class="profile-pic-right-container"><img class="tweetimage10" src=""/></div>
                    </div>
                    </li>
                
                </ul>              
            </div>
            <!-- Tweets End -->
        </div>
    </div>
    
    
</div>    
<!-- ============== Content End ============== -->

    
</div>
    
<!--====================================================================================================
    MAIN WRAPPER END
=====================================================================================================-->

    
    
    
    
<!--====================================================================================================
    FOOTER START
=====================================================================================================-->
    
<div class="footer-container">
    <!-- <div class="disclaimer">*Best viewed in Firefox</div> -->
    <div class="email">
        <a href="mailto:we@theseus.digital">we@theseus.digital<div class="tooltip">Contact us for info or feedback</div></a>        
    </div> 
</div>    
    
<!--====================================================================================================
    FOOTER END
=====================================================================================================-->    

<!-- Bootstrap ModalBox HTML Start -->
<div id="hashtagbox" class="modal fade" role="dialog" style="color:black;">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Select Hashtags</h4>
      </div>
      <div class="modal-body">
        <div class="input-group ui-widget">
            <span class="input-group-addon"><b>#</b></span>
            <input type="text" class="form-control" id="hashtags" placeholder="Select Hashtag" name="hashtag" value="<?php echo $hashtagname; ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="updateWall()" data-dismiss="modal">Start</button>
        <button type="button" class="btn btn-danger" onclick="resetHashtags()">Undo</button>
      </div>
    </div>

  </div>
</div>
<!-- Bootstrap ModalBox HTML End -->

</body>
</html>