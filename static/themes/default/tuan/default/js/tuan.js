/*
 * Powered by ueeshop.com		http://www.ueeshop.com
 * 广州联雅网络科技有限公司		020-83226791
 */

var tuan_obj={
	tuan_init:function(){
		$("#tuan_title").delegate("a", "click", function(){
			var $type=$(this).attr("data-type");
			$(this).addClass("current").siblings().removeClass("current");
			ajax_tuan_list({"page":0, "CateId":$(".tuan_menu .category a.current").attr("data"), "Sort":$(".tuan_sort a.current").attr("data-sort"), "typ":$type}, 1);
			return false;
		});
		
		$(".tuan_menu .category").delegate("a", "click", function(){
			$(this).addClass("current").siblings().removeClass("current");
			ajax_tuan_list({"page":0, "CateId":$(this).attr("data"), "Sort":$(".tuan_sort a.current").attr("data-sort"), "typ":$("#tuan_title .current").attr("data-type")}, 1);
			return false;
		});
		
		$(".tuan_sort").delegate("a", "click", function(){
			var $children=$(this).children("i"),
				$num=parseInt($(this).index())+1,
				$sort=$(this).attr("data-sort");
			$(this).addClass("current").siblings().removeClass("current").children("i").attr("class", "icon_sort");
			if($num==1){
				if($children.hasClass("icon_sort_up")){
					$children.attr("class", "icon_sort_down");
					$sort="1d";
				}else if($children.hasClass("icon_sort_down")){
					$children.attr("class", "icon_sort_up");
					$sort="1a";
				}else{
					$children.attr("class", "icon_sort_up");
					$sort="1a";
				}
			}else{
				if($children.hasClass("icon_sort")){
					$children.attr("class", "icon_sort_down");
					$sort=$num+"d";
				}else{
					$children.attr("class", "icon_sort");
					$sort=$num;
				}
			}
			$(this).attr("data-sort", $sort);
			ajax_tuan_list({"page":0, "CateId":$(this).attr("data"), "Sort":$sort, "typ":$("#tuan_title .current").attr("data-type")}, 1);
			return false;
		});
		
		ajax_tuan_list({"page":0, "CateId":$(".tuan_menu .category a.current").attr("data"), "Sort":$(".tuan_sort a.current").attr("data-sort"), "typ":$("#tuan_title .current").attr("data-type")}, 1);
		
		function ajax_tuan_list(data, clear){
			clear && $("#prolist").html("");
			$("#prolist").loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":523, "background-position":"center"});
			setTimeout(function(){
				$.ajax({
					url:"/ajax/tuan_default.html",
					async:false,
					type:"post",
					data:data,
					dataType:"html",
					success:function(result){
						if(result){
							$("#prolist").append(result).unloading();
							$("#prolist").attr("Num", "0");
							
							$("#turn_page").delegate("a", "click", function(){
								var page=$(this).attr("href").replace('Page_', '');
								ajax_tuan_list({"page":page, "CateId":$(".tuan_menu .category a.current").attr("data"), "typ":$("#tuan_title .current").attr("data-type")}, 1);
								return false;
							});
						}else{
							var html='<div class="blank25"></div><div id="loading_tips">"+lang_obj.seckill.no_products+"</div>';
							$("#prolist").append(html).unloading();
							$("#prolist").attr("Num", "1");
						}
					}
				});
			}, 500);
		}
	}
};

/*
var Num=0;

(function($){
	$('.t_head .menu').on('click', 'a', function(){
		$(this).addClass('current').siblings().removeClass('current');
		$(".tuan_list .list_body").attr('list', $(this).attr('list'));
		ajax_tuan_list({"page":0, "List":$(this).attr('list'),'CateId':$(".tuan_list .list_body").attr("cateid")}, 1);
		return false;
	});
	
	if($(".tuan_list").length){
		ajax_tuan_list({"page":0,'CateId':$(".tuan_list .list_body").attr("cateid")}, 1);
		
		$(document).scroll(function(){
			var $winTop = $(window).scrollTop(),
				$winH = $(window).height(),
				$listTop = $(".tuan_list .list_body").offset().top,
				$listHeight = $(".tuan_list .list_body").outerHeight(),
				$loadNum = $listTop+$listHeight-$winH-30;
			
			if($winTop>=$loadNum && Num==0){
				Num=1;
				var page=parseInt($(".tuan_list .list_body .item:last").attr("page"));
				if(!isNaN(page)) ajax_tuan_list({"page":page+1, "List":$(".tuan_list .list_body").attr("list"),'CateId':$(".tuan_list .list_body").attr("cateid")}, 0);
			}
		});
		
		function ajax_tuan_list(data, clear){
			clear && $(".tuan_list .list_body").html("");
			$(".tuan_list .list_body").loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":50, "background-position":"center"});
			setTimeout(function(){
				$.ajax({
					url:"/ajax/tuan_default.html",//"/?m=ajax&a=tuan_default",
					async:false,
					type:'get',
					data:data,
					dataType:'html',
					success:function(result){
						if(result){
							clear && $(".tuan_list .list_body").html("");
							$(".tuan_list .list_body").append(result).unloading();;
							Num=0;
						}else Num=1;
						if($('#loading_tips').length) Num=1;
					}
				});
			}, 1000);
		}
	}
	
	
	//检查当前属性库存的情况
	function check_stock(){
		var obj=$('ul.attributes'),
			attr_len=obj.find('li').length,
			ext_attr,
			$attrStock=parseInt($("#attrStock").val()),
			$defaultStock=parseInt($(".buy_now").attr('stock'));//剩余团购数量
		if(attr_len && $("#ext_attr").length) ext_attr=$.evalJSON($("#ext_attr").val());//扩展属性
		if(attr_len && $defaultStock==0){//产品总库存为0，就是缺货状态，所有属性不能勾选
			obj.find('option').addClass('hide hide_fixed').get(0).disabled=true;
		}
		if(attr_len && $attrStock && $defaultStock>0){ //开启了0是库存为空的设定
			var ext_ary=new Object, ary=new Object, cur, stock_ary=new Object;
			for(k in ext_attr){
				ary=k.split('_');
				for(k2 in ary){
					if(!stock_ary[ary[k2]]) stock_ary[ary[k2]]=0;
				}
				if(ext_attr[k][1]>0){
					for(k2 in ary){
						if(ary.length!=attr_len) continue;
						stock_ary[ary[k2]]+=1;
					}
				}
			}
			for(k in stock_ary){
				if(stock_ary[k]<1){
					if(obj.find('option[value='+k+']').length) obj.find('option[value='+k+']').addClass('hide hide_fixed').get(0).disabled=true;
				}else{
					if(obj.find('option[value='+k+']').length) obj.find('option[value='+k+']').removeClass('hide hide_fixed').get(0).disabled=false;
				}
			}
		}
	}
	
	//检查当前所有属性库存的情况
	if($('ul.attributes').attr('data-combination')==1){//规格组合
		check_stock();
	}
	
	var VId, attr_id, attr_ary=new Object,
		attr_hide=$("#attr_hide"),
		attr_len=$("ul.attributes li").length,
		ext_attr=$.evalJSON($("#ext_attr").val()),//扩展属性
		$attrStock=parseInt($("#attrStock").val()),
		attrSelected=parseInt($("ul.attributes").attr('default_selected')),//默认选择
		$IsCombination=$('ul.attributes').attr('data-combination');//是否开启规格组合
	
	$(".attributes").on("change", "select", function(){
		VId=$(this).val();
		attr_id=$(this).attr("attr");
		if(attr_hide.val() && attr_hide.val()!='[]'){
			attr_ary=$.evalJSON(attr_hide.val());
		}
		if(VId){
			attr_ary[attr_id]=VId;
		}else{//选择默认选项，清除对应ID
			delete attr_ary[attr_id];
		}
		attr_hide.val($.toJSON(attr_ary));
		
		if($attrStock && $IsCombination==1){
			if(attr_hide.val()=='[]' || attr_hide.val()=='{}'){//组合属性都属于默认选项
				$('ul.attributes').check_stock(); //检查当前所有属性库存的情况
				$('#inventory_number').text($defaultStock);//还原库存
				stock=$defaultStock;
			}else if(ext_attr && ext_attr!='[]'){//判断组合属性库存状态
				var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, cur, no_stock_ary=new Object;
				for(k in attr_ary){
					select_ary[++i]=attr_ary[k];
				}
				if(select_ary.length == attr_len-1){ //勾选数 比 属性总数 少一个
					var no_attrid=0, attrid=0, _select_ary, key;
					$('ul.attributes li').each(function(){
						attrid=$(this).children('select').attr('attr');
						if(!attr_ary[attrid]){
							no_attrid=attrid; //没有勾选的属性ID
						}
					});
					$('ul.attributes #attr_'+no_attrid).find('option:gt(0)').each(function(){
						value=$(this).attr('value');
						_select_ary=new Array;
						for(k in select_ary){
							_select_ary[k]=select_ary[k];
						}
						_select_ary[select_ary.length]=value;
						_select_ary.sort(function(a, b){ return a - b });
						key=_select_ary.join('_');
						if(ext_attr[key][1]==0){
							if($('ul.attributes option[value='+k+']').length) $('ul.attributes option[value='+value+']').addClass('hide').get(0).disabled=true;
						}else{
							if($('ul.attributes option[value='+k+']').length) $('ul.attributes option[value='+value+']').removeClass('hide').get(0).disabled=false;
						}
						if(VId==''){ //取消操作
							$('ul.attributes li').each(function(){
								if($(this).children('select').attr('attr')!=attr_id){
									$(this).find('option.hide').not('.hide_fixed').removeClass('hide').get(0).disabled=false;
								}
							});
						}
					});
				}else if(select_ary.length == attr_len && attr_len!=1){ //勾选数 跟 属性总数 一致
					for(k in ext_attr){
						ary=k.split('_');
						for(k2 in ary){
							if(!no_stock_ary[ary[k2]]) no_stock_ary[ary[k2]]=0;
						}
						cur=0;
						for(k2 in select_ary){
							if(global_obj.in_array(select_ary[k2], ary) && ary.length==attr_len){ //找出包含自身的关联项数据，不一致的属性数量数据也排除掉
								++cur;
							}
						}
						if(cur && cur>=(select_ary.length-1) && select_ary.length==attr_len){ //“数值里已有的选项数量”跟“已勾选的选项数量”一致
							if(ext_attr[k][1]==0){
								for(k2 in ary){
									if(global_obj.in_array(ary[k2], select_ary)) continue;
									if(!no_stock_ary[ary[k2]]){
										no_stock_ary[ary[k2]]=1;
									}else{
										no_stock_ary[ary[k2]]+=1;
									}
								}
							}
						}
					}
					for(k in no_stock_ary){
						if(!global_obj.in_array(k, select_ary) && no_stock_ary[k]>0){
							if($('ul.attributes option[value='+k+']').length) $('ul.attributes option[value='+k+']').addClass('hide').get(0).disabled=true;
						}else{
							if($('ul.attributes option[value='+k+']').length) $('ul.attributes option[value='+k+']').removeClass('hide').get(0).disabled=false;
						}
					}
				}else{ //勾选数 大于 1
					$('ul.attributes li').each(function(){
						$(this).find('option.hide').not('.hide_fixed').removeClass('hide').attr('disabled', false);
					});
				}
			}
		}
	});
	
	//购物车属性以选择按钮显示，默认执行第一个选项
	if(attrSelected && $(".attributes li select").length){
		$(".attributes li select").each(function(){
			$(this).find("option[value!='']").not(".hide").eq(0).attr("selected", "selected").change();
			$(this).find("option:eq(0)").remove();
		});
	}
	
	//购物车属性提示框，关闭事件
	$('.attr_sure_close').click(function(){
		if($('.attributes_tips').length){
			$('.attributes').removeClass('attributes_tips');
		}
	});
	
	//Buy Now
	$('#tuan_form').submit(function(){return false;});
	$('#tuan_form input:submit').on('click', function(){
		var subObj=$("#tuan_form"),
			$attr_name='',
			result=0;
		subObj.find("select").each(function(){
			if(!$(this).val()){
				result=1;
			}
		});
		
		$('.attributes').removeClass('attributes_tips');
		if(result){
			$('.attributes').addClass('attributes_tips');
			return false;
		}
		
		$.post('/', $('#tuan_form').serialize()+'&do_action=cart.additem&back=1', function(data){
			data=$.evalJSON(data);
			if(data.ret==2){
				$(this).attr('disabled', false);
				alert(data.msg);
			}else if(data.ret==1){
				window.location.href='/cart/';
			}
		});
		return false;
	});
})(jQuery);
*/
