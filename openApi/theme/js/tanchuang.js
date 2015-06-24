function tanchukuang(bg,kuan,gao,line,hudu,biankuang,imgTitle){
	this.bg=bg;
	this.k=kuan;
	this.g=gao;
	this.line_Height=line+'px';
	this.radiu=hudu;
	this.bian=biankuang;
	this.touxiang=imgTitle;
}

function xianshi(title,describe,word,btnValue){
	this.h=title;
	this.d=describe;
	this.w=word;
	this.b=btnValue;
}

function huidiao(fn){
	this.name=fn;
}

huidiao.prototype.dosomething=function(callback){
	callback.call(this);
}

function aa(){
	
}
tanchukuang.prototype.neirong=function(){
	$('.box').css({'background':this.bg , 'width':this.k , 'height':this.g , 'border-radius':this.radiu , 'border':this.bian});
}

tanchukuang.prototype.anniu=function(){
	if($('.box2 .img_titile').get(0))
	{
		$('.box2 img').attr('src',this.touxiang);
	}
	if($('.anniu').children('.btn-zu1'))
	{
		$('.anniu .btn-zu1').css({'background':this.bg , 'width':this.k , 'height':this.g , 'line-height':this.line_Height , 'border-radius':this.radiu , 'border':this.bian});
	}
}

xianshi.prototype.quHtml=function(){
	if($('.box img').parent('.box'))
	{
		$('.box1 .biaoti').html(this.h);
		$('.box1 .miaoshu').html(this.d);
		$('.box2 .describe_ontent').html(this.w);
		$('.box2 .btn-zu1').html(this.b);
	}
}
$(document).ready(function(){
	$(document).on('click',function(e){
		var dangQian=e.target,labelname=dangQian.tagName.toLowerCase();
		if($(dangQian).attr("delete-obj") == "closable")
		{	
			$('.box_zhezhao').fadeIn(100,function(){
				//var kuang1=new tanchukuang('#F4F4F4','80%',260,'',10,'','');
				//var html1=new xianshi('活动规则','1、活动时间：每个用户只能玩一次<br>2、参与方式：每次只能玩一次抢红包<br>3、规则每个用户只能玩一次<br>4、本活动最终解释权归深圳市IDEA新媒体','','');
				//kuang1.neirong();
				//html1.quHtml();
				$('.box').css('display','none');
				$('.box1').css('display','block');
			})
		}
		
		if($(dangQian).hasClass('exit'))
		{
			$('.box_zhezhao').fadeOut(100,function(){
				$('.box1').css('display','none');
			})
		}

		if($(dangQian).attr("delete-obj") == "Reconfirm")
		{
			$('.box_zhezhao').fadeIn(100,function(){
				/*var anniu2=new tanchukuang('#E43A3D',120,50,50,15,'','images/touxiang.png')
				var kuang2=new tanchukuang('#F4F4F4','80%','','',10,'','');
				var html2=new xianshi('','','你的刀法一流，已经帮忙砍掉了30元','确定');
				kuang2.neirong();
				anniu2.anniu();
				html2.quHtml();*/
				//$('.box').css('display','none');
				//$('.box2').css('display','block');
			})
		}

		if($(dangQian).hasClass('btn-zu1'))
		{
			var t=new huidiao();
			t.dosomething(aa);
			$('.box_zhezhao').fadeOut(100,function(){
				$('.box2').css('display','none');
			})
		}
	})
})