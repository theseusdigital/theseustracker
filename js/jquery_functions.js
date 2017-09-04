$(document).ready(function(){
$("p.test").mouseleave(function(){
$("#sl").slideDown("slow");
});
$("p.test").mouseenter(function(){
$("#sl").slideUp("slow");
});
$("button").click(function(){
  var str=$("h1").html(function(i,ot){
  return "index:"+i+" origText:"+ot;
  });
});
var txt=$("<p></p>").text("sdgdsfgdfgdfg");
var el=document.createElement("p").innerHTML=txt;
$("p.test").append(el);
$("a").click(function(){
 alert($("a").attr("href")); 
});
$("div").mouseenter(function(){
  $("div").animate({width:'500px',height:'300px',fontSize:'200%'});
});
$("div").mouseleave(function(){
  $("div").animate({width:'300px',height:'100px',fontSize:'100%'});
});

});