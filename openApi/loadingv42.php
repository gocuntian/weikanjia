<!DOCTYPE html>
<html>
<head>
<title>正在进入支付...</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no" />
<link rel="prefetch" href="wxPayOauth_callback.php?<?php echo $_SERVER['QUERY_STRING'];?>" /> 

<script language="JavaScript">
<!--
var url = 'wxpayapi.php?<?php echo $_SERVER['QUERY_STRING'];?>';
//-->
</script>

</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0">
<table border=0 cellpadding=0 cellspacing=0 width="98%" height="100%">
<tr>
<form name=loading>
<td align=center>
<p><font color=gray>正在进入安全支付环境，请稍候....</font></p>
<p>
<input type=text name=chart size=46 style="font-family:Arial;
font-weight:bolder; color:gray;
background-color:white; padding:0px; border-style:none;">
<br>
<input type=text name=percent size=46 style="font-family:Arial;
color:gray; text-align:center;
border-width:medium; border-style:none;">
<script>var bar = 0
var line = "||"
var amount ="||"
count()
function count(){
bar= bar+2
amount =amount + line
document.loading.chart.value=amount
document.loading.percent.value=bar+"%"
if (bar<99)
{setTimeout("count()",1);}
else
{window.location = url;}
}
</script>
</p>
</td>
</form>
</tr>
</table>
</body>
</html>