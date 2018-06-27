/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$(document).ready(function(){
	$('#help').css('border-bottom-color', $('.FontColor').css('color'));
	var NavBgColor=$('.NavBgColor').css('background-color');
	$('.nav_categories').css('border', '2px solid '+NavBgColor);
	$('.search .category .list>li').hover(function(){
		$(this).css({'background-color':NavBgColor, 'color':'#fff'});
	}, function(){
		$(this).css({'background-color':'','color':''});
	});
	
	/*导航分类下拉(start)*/
	$('#nav').delegate('.nav_menu', 'mouseover', function(){$(this).find('.nav_categories').fadeIn(400);});
	$('#nav').delegate('.nav_menu', 'mouseleave', function(){$(this).find('.nav_categories').fadeOut(400);});
	$('#nav, #category').delegate('.nav_categories>ul>li', 'mouseover', function(){
		$(this).find('h2>a').addClass('FontColor').next('em').addClass('NavArrowHoverColor');
		var json=$.evalJSON($(this).attr('data'));
		if(json.length){
			var index=$(this).addClass('hover').index();
			if(!$(this).find('.nav_subcate').length){
				var html='<div class="nav_subcate">';
				for(i=0; i<json.length; i++){
					html=html+'<dl'+(i>=3?' class="tline"':'')+'><dt><a href="'+json[i].url+'" title="'+json[i].text+'">'+json[i].text+'</a></dt>';
					if(json[i].children){
						var jsonchild=json[i].children;
						html=html+'<dd>';
						for(j=0; j<jsonchild.length; j++){html=html+'<a href="'+jsonchild[j].url+'" title="'+jsonchild[j].text+'">'+jsonchild[j].text+'</a>';}
						html=html+'</dd>';
					}
					html=html+'</dl>';
					if((i+1)%3==0){html=html+'<div class="blank12"></div>';}
				}
				html=html+"</div>";
				$(this).append(html);
			}
			if(index<=11){//11
				$(this).find('.nav_subcate').css('top',(-index*40)+'px');//-8
			}else{
				$(this).find('.nav_subcate').css('bottom',-40+'px');
			}
		}
		$(this).find('em').css('border-color', 'transparent transparent transparent '+$('.CategoryBgColor').css('background-color'));
	});
	$('#nav, #category').delegate('.nav_categories>ul>li', 'mouseleave', function(){$(this).removeClass('hover').find('h2>a').removeClass('FontColor').next('em').css('border-color', 'transparent transparent transparent #ccc').parent().parent().find('.nav_subcate').remove();});
	$('#nav .nav_item li a').addClass('NavHoverBgColor');
	/*导航分类下拉(end)*/
	
	/*分类左侧栏(start)*/
	var sideCategory=function(){
		if($('body').hasClass('index')){
			if($('body').hasClass('w_1200')){
				$('.side_category li:gt(7)').show();
			}else{
				$('.side_category li:gt(7)').hide();
			}
		}
	}
	sideCategory();
	$(window).resize(sideCategory);
	$('.side_category').on('mouseover', '.cate_menu>ul>li', function(){
		$(this).find('h2>a').addClass('FontColor').next('em').addClass('NavArrowHoverColor');
		var json=$.evalJSON($(this).attr('data'));
		if(json.length){
			var index=$(this).addClass('hover').index();
			if(!$(this).find('.cate_subcate').length){
				var html='<div class="cate_subcate">';
				for(i=0; i<json.length; i++){
					html=html+'<dl'+(i>=3?' class="tline"':'')+'><dt><a href="'+json[i].url+'" title="'+json[i].text+'">'+json[i].text+'</a></dt>';
					if(json[i].children){
						var jsonchild=json[i].children;
						html=html+'<dd>';
						for(j=0; j<jsonchild.length; j++){
							html=html+'<a href="'+jsonchild[j].url+'" title="'+jsonchild[j].text+'">'+jsonchild[j].text+'</a>';
						}
						html=html+'</dd>';
					}
					html=html+'</dl>';
					if((i+1)%3==0){html=html+'<div class="blank12"></div>';}
				}
				html=html+"</div>";
				$(this).append(html);
			}
			if(index<=11){
				$(this).find('.cate_subcate').css('top',(-index*30-8)+'px');
			}else{
				$(this).find('.cate_subcate').css('bottom',-40+'px');
			}
		}
	}).on('mouseleave', '.cate_menu>ul>li', function(){
		$(this).removeClass('hover').find('h2>a').removeClass('FontColor').next('em').removeClass('NavArrowHoverColor').parent().parent().find('.cate_subcate').remove();
	});
	/*分类左侧栏(end)*/
	
	/*搜索框分类下拉*/
	$('.search .category').on('mouseleave', function(){
		$list=$(this).find('.list');
		$(this).find('.head').removeClass('selected');
		$list.hide();
	}).on('mouseenter', function(){
		$list=$(this).find('.list');
		$(this).find('.head').addClass('selected');
		$list.show();
	}).on('click', '.list>li', function(){
		$this=$(this);
		$form=$this.parents('form').find('input[name=CateId]');
		$list=$this.parents('form').find('.list');
		$value=$this.attr('cateid');
		$title=$this.text();
		
		$form.val($value);
		$this.parents('form').find('.head').text($title);
		$list.hide();
	});
	
	$('.index_prod_list .move>a').off().on('click', function(){
		var $class=$(this).attr('class'),
			$obj=$(this).parents('.index_prod_list'),
			$len=$obj.find('.index_prod_row').size(),
			$num=parseInt($obj.find('.index_prod_row:visible').attr('num'));
		
		if($class=='move_left'){
			$num-=1;
		}else{
			$num+=1;
		}
		if($num>=$len) $num=0;
		if($num<0) $num=$len-1;
		
		$obj.find('.index_prod_row[num='+$num+']').show().siblings().hide();
	});
	
	//会员注册页头部背景
	$('#customer .header').addClass('FontBgColor');
	
	/****** 导航显示 Start ******/
	function navShow(){
		var $obj=$('.nav').eq(1),
			navItemWidth=0,
			navWidth=$obj.width();
		$obj.css('overflow', 'visible').find('.item').each(function(){
			navItemWidth+=$(this).outerWidth();
			if(navItemWidth>navWidth){
				$(this).hide();
			}else{
				$(this).show();
			}
		});
	}
	navShow();
	$(window).resize(function(){ navShow(); });
	/****** 导航显示 End ******/
});


