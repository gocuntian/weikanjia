
/*

*** Tan 

***

*/
var addresSelect = $('#addresSelect')[0],
	zhegaiK = $('#zhegaik')[0];

function closeAddress () {
	$(zhegaik).css('opacity', 0);
	$(addresSelect).removeClass('TopShowGy');
	window.setTimeout(function () {
		$(addresSelect).css('display', 'none');
		$(zhegaik).css('display', 'none');
	}, 500);
}

$('#addressK').on('click', function () {
	$(addresSelect).css('display', 'block');
	$(zhegaik).css('display', 'block');
	window.setTimeout(function () {
		$(zhegaik).css('opacity', 0.8);
		$(addresSelect).addClass('TopShowGy');
	}, 0);
});

$('#zhegaik').on('click', function () {
	closeAddress();
});

$('#addresSelect').on('click', function (e) {
	var dangQian = e.target, labelname = dangQian.tagName.toLowerCase();
	if ($(dangQian).parents('li').get(0).tagName.toLowerCase() == 'li') {
		closeAddress();
		var dizhiHtml = $(dangQian).parents('li').html();
		$('#addressMain').html(dizhiHtml);
	}
});



function doAddress(){
	$.ajax({ 
		url: $('#paydata').attr('data-addressAjax'),
		type: 'POST',
		async:false,
		dataType:'jsonp',
		jsonp:'callback',
		success: function(msg){
			
			if(msg.code == 1){
				var num = msg['data'].length;
				for (var i=0; i<num; i++) {
					console.log(num);
					$('#addresSelect .ul1').append(pageTurning.template($('#addressList').html(), msg['data'][i]));
				}
			}

		}
	});
}

setTimeout(function(){
doAddress();
}, 1000)

	function pageTurning() {	

	}
	pageTurning.prototype.template = function (templateStr, mapData) {
		var re, key;
	    for (key in mapData) {
	        re = new RegExp('\\{' + key + '\\}','ig');
	        templateStr = templateStr.replace(re, mapData[key]); //replace key with value
	    }
	    return templateStr;
	}
	pageTurning.prototype.pageTurningExecute = function (URLS, addContainer) {
	
				
	}
	var urlEr = '',
	    rongqi = $('.ul1 .gyDizhiK');
	var pageTurning = new pageTurning();



    $('#addresSelect ul').on('click', function(){
      var oid = $('#paydata').attr('data-oid');
      var realname =  $(this).find('li').attr('data-realname');      
      var cellphone =  $(this).find('li').attr('data-cellphone');      
      var address =  $(this).find('li').attr('data-address');      
   		$.ajax({ 
            url: 'http://test.wkj.xinyuemin.com/?controller=pay&action=updateAddress&hdid=173',
			type: 'POST',
			async:false,
            data:{oid:oid, realnamd: realname, cellphone: cellphone, address: address},
			dataType:'jsonp',
			jsonp:'callback',
			success: function(msg){
                alert(msg.msg);

			}
		});
		   
    });
