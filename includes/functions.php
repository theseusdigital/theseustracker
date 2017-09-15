<?php
  function redirect_to($location=NULL)
  {
    if($location!=NULL)
    {
      header("Location:$location");
      exit;
    }
  }
  function confirm_query($result)
  {
    if(!$result)
    {
      die("Query Failed".mysql_error());
    }
  }
  
  function highlight($keyword, $text, $color="red")
  {
    if(empty($keyword))
    {
      return $text;
    }
    else
    {
      $keyword = preg_quote($keyword);
      $starttag = "<font color='$color'>";
      $endtag = "</font>";
      preg_match_all("/$keyword/i", $text, $matcharray);
      $searchcursor = 0;
      foreach ($matcharray[0] as $match) {
        $highlighted_match = $starttag.$match.$endtag;
        $matchposition = stripos($text, $match, $searchcursor);
        $matchlength = strlen($match);
        $text = substr_replace($text, $highlighted_match, $matchposition, $matchlength);
        $searchcursor = $matchposition + strlen($highlighted_match);
      }
      /*$highlighted_match = $starttag.$keyword.$endtag;
      $text = str_replace($keyword,$highlighted_match,$text);*/
      return $text;
    }
  }

  function getGrowth($nownumber, $pastnumber){
    if($pastnumber == 0){
      $growth = 0;
    }
    else{
      $growth = (($nownumber-$pastnumber)/abs($pastnumber))*100;
    }
    if(abs($growth)>0 && abs($growth)<1){
      $growth = round($growth,2);
    }
    else{
      $growth = round($growth);
    }
    return highlight_growth($growth);
  }

  function highlight_growth($growth, $color="green"){
    $starttag = "<font color='$color'>";
    $endtag = "</font>";
    if($growth<0){
      $color = "red";
      $starttag = "<font color='$color'>";
      $highlighted_growth = $starttag.abs($growth)."%".$endtag;
    }
    elseif($growth>0){
      $highlighted_growth = $starttag.$growth."%".$endtag;
    }
    else{
      $highlighted_growth = "";
    }
    return $highlighted_growth;
  }

  function getGrowthCSV($nownumber, $pastnumber){
    if($pastnumber == 0){
      $growth = 0;
    }
    else{
      $growth = (($nownumber-$pastnumber)/abs($pastnumber))*100;
    }
    if(abs($growth)>0 && abs($growth)<1){
      $growth = round($growth,2);
    }
    else{
      $growth = round($growth);
    }
    return $growth."%";
  }

  function unicode_decode($text){
    $words = explode(" ", $text);
    $decoded_words = array();
    foreach($words as $word){
      array_push($decoded_words, json_decode('"'.$word.'"'));
    }
    //print_r($decoded_words);
    $message = implode(" ", $decoded_words);
    return $message;
  }

  function highlight_tweet($keyword, $text, $color="blue", $classprefix="light"){
    if(empty($keyword))
    {
      return $text;
    }
    else
    {
      $keyword = preg_quote($keyword);
      $starttag = "<span class='$classprefix-$color'>";
      $endtag = "</span>";
      preg_match_all("/$keyword/i", $text, $matcharray);
      $searchcursor = 0;
      foreach ($matcharray[0] as $match) {
        $highlighted_match = $starttag.$match.$endtag;
        $matchposition = stripos($text, $match, $searchcursor);
        $matchlength = strlen($match);
        $text = substr_replace($text, $highlighted_match, $matchposition, $matchlength);
        $searchcursor = $matchposition + strlen($highlighted_match);
      }
      /*$highlighted_match = $starttag.$keyword.$endtag;
      $text = str_replace($keyword,$highlighted_match,$text);*/
      return $text;
    }
  }

  function postage($time){
    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.pluralize($numberOfUnits);
    }
  }

  function pluralize($number){
    return ($number>1 || $number==0)?'s':'';
  }

  function getDays($since, $until){
    $daydiff = 60*60*24;
    $sincetime = strtotime($since);
    $untiltime = strtotime($until);
    $days = array();
    while($sincetime <= $untiltime){
        $day = date("d M",$sincetime);
        array_push($days, $day);
        $sincetime += $daydiff;
    }
    return $days;
  }
?>
