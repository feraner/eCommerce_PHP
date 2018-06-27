/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
var products_list_obj={
	init:function(){
		products_list_obj.price_limit($('#minprice'), $('#maxprice'), $('#submit_btn'));
	},
	
	price_limit:function(min_obj, max_obj, btn_obj){
		btn_obj.click(function(){
			var url=$(this).next().val(),
				p0=min_obj.val()?parseFloat(min_obj.val()):0,
				p1=max_obj.val()?parseFloat(max_obj.val()):0;
			
			if(p0>=0 && p1>=0){
				if(p0>0 && p1>0 && p0>p1){
					min_obj.val(p1);
					max_obj.val(p0);
					p0=parseFloat(min_obj.val()),
					p1=parseFloat(max_obj.val());
				}
				if(p0==0 && p1==0){
					window.location=url;
				}else{
					window.location=url+'&Price='+p0+'-'+p1;
				}
			}
		});
	}
}