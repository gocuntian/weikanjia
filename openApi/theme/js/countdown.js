function ComputingTime(year,month,day,hour,divname,interval) { 
	var now = new Date(); 
	var endDate = new Date(year, month-1, day , hour); 
	var duration=endDate.getTime()-now.getTime(); 
	var duration_Ms = parseInt(duration/1000);
	var days=Math.floor(duration_Ms/(60*60*24)); 
	var hours=Math.floor((duration_Ms-days*24*60*60)/3600); 
	var minute=Math.floor((duration_Ms-days*24*60*60-hours*3600)/60); 
	var second=Math.floor(duration_Ms-days*24*60*60-hours*3600-minute*60); 
	var Position = document.getElementById(divname); 
	if( duration_Ms > 0 ){
		Position.innerHTML ='<span>'+days+"</span>天"+'<span>'+hours+"</span>小时"+'<span>'+minute+"</span>分"+'<span>'+second+"</span>秒"; 
	}else{
		Position.innerHTML ='<span>'+0+"</span>天"+'<span>'+0+"</span>小时"+'<span>'+0+"</span>分"+'<span>'+0+"</span>秒"; 	
	}	
} 
/***********************************
	year     代表活动结束时间的年份；
	month    代表活动结束时间的月份；
	day      代表活动结束时间的天；
	hour     代表活动结束时间的小时；
	divname  代表倒计时用在哪个id的位置；
	interval 代表倒计时每隔多久执行一次；
************************************/
function ShowCountDown(year,month,day,hour,divname,interval){
	window.setTimeout(function(){ComputingTime(year,month,day,hour,divname);},0);
	window.setInterval(function(){ComputingTime(year,month,day,hour,divname);},interval);
}