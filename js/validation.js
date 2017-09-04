function test(obj)
{
if(obj.us.value.match(" "))
{
document.getElementById("un").innerHTML="No Spaces Allowed";
return false;
}
if(obj.pw.value.match(" "))
{
document.getElementById("pwd").innerHTML="No Spaces Allowed";
return false;
}
if(obj.us.value.length<6)
{
document.getElementById("un").innerHTML="Username Too Short";
return false;
}
if(obj.pw.value.length<6)
{
document.getElementById("pwd").innerHTML="Password Too Short";
return false;
}
}
function validate(obj)
{
var val=obj.value;
if(val.match(" ") && obj.name=="us")
{
obj.style.color="red";
obj.style.borderColor="red";
var sp=document.getElementById("un").innerHTML="No Spaces Allowed";
sp.style.color="red";
return false;
}
else if(val.match(" ") && obj.name=="pw")
{
obj.style.color="red";
obj.style.borderColor="red";
var sp=document.getElementById("pwd").innerHTML="No Spaces Allowed";
sp.style.color="red";
return false;
}
else if(val.length<6 && obj.name=="us")
{
obj.style.color="red";
obj.style.borderColor="red";
var u=document.getElementById("un").innerHTML="Username Too Short";
u.style.color="red";
return false;
}
else if(val.length<6 && obj.name=="pw")
{
obj.style.color="red";
obj.style.borderColor="red";
var p=document.getElementById("pwd").innerHTML="Password Too Short";
p.style.color="red";
return false;
}
else
{
obj.style.color="black";
obj.style.borderColor="gray";
document.getElementById("un").innerHTML="";
document.getElementById("pwd").innerHTML="";
return false;
}
}