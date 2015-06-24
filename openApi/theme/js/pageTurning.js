/* 
*动态加载元素
*
* Tan
****调用方式******
* 滚动到底部用ajax动态加载新内容函数pageTurning
* 该函接受3个参数1、ajax请求地址。2、要被添加的容器。3、ajax接收数据的格式。
*/

function pageTurning() {
	this.page = 2;
}

pageTurning.prototype.template = function (templateStr, mapData) {
	var re, key;
    for (key in mapData) {
        re = new RegExp('\\{' + key + '\\}','ig');
        templateStr = templateStr.replace(re, mapData[key]); //replace key with value
    }
    return templateStr;
}
pageTurning.prototype.pageTurningExecute = function (URLS, addContainer, ajaxType) {

	if (!ajaxType) { //判断用户是不是用json格式接收数据
		ajaxType = "html";
	}
	$(document.body).click(function (e) {
		var dangQian = e.target;
		if($(dangQian).get(0).className == 'More'){
			$.ajax({
			  type: "post",
			  url: URLS,
			  data : {'yema':this.page},
			  dataType : ajaxType
			}).done(function( msg ) {
				if (msg && msg.length) {
					for (var i=0; i<3; i++) {
						$(addContainer).append(pageTurning.template($('#htmlStructure').html(), msg[i]));
					}
					$(".listLumpk").scrollTop($(".listLumpk ul").height());
				} //else {
					//$('.loading').text('已全部加载完');
				//}
				//console.log(msg);
				this.page++;
			 }).error(function () {
				 alert(2);
				//$('.loading').text('网络异常');
			 });
		}else if($(dangQian).get(0).className == 'renovate'){
			$.ajax({
			  type: "post",
			  url: URLS,
			  data : {'yema':this.page},
			  dataType : ajaxType
			}).done(function( msg ) {
				$('.listLump ul').html('');
				if (msg && msg.length) {
					for (var i=0; i<3; i++) {
						$(addContainer).append(pageTurning.template($('#htmlStructure').html(), msg[i]));
					}
				} //else {
					//$('.loading').text('已全部加载完');
				//}
				//console.log(msg);
			 }).error(function () {
				 alert(2);
				//$('.loading').text('网络异常');
			 });
		}
	
	});
}