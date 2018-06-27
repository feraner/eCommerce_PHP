/*
Powered by ly200.com		http://www.ly200.com
广州联雅网络科技有限公司		020-83226791
*/

$.fn.bigGlass = function(type){
	/*
	 *	type 1: 身份证    2：电话号码
	 *	号码放大镜随着字数延伸
	 *	身份证分割： 3 3 4 4 4   手机号码分割： 3 4 4
	*/
	var glassT = $(this).offset().top, glassL = $(this).offset().left;//定义预展示输入框的坐标
	var gId = $(this).attr("id");
	var glassStr = '<div id="bigGlass"><nobr><span></span><span></span><span></span><span></span></nobr></div>';
	$(this).after($(glassStr));
	$(this).keyup(function(){
		showBigGlass();
	})
	//生成放大镜
	function showBigGlass(){
		var inputVal = $("#"+gId).val(), l = inputVal.length;
		$("#bigGlass").css({"top":(glassT-37)+"px","left":glassL+"px"});
		 style="top:'+(glassT-50)+'px;left:'+glassL+'px;"
		if(!inputVal){
			$("#bigGlass").hide();
			return false;
		}
		//身份证号码与电话号码展示逻辑不同，做区分
		$("#bigGlass").html('<nobr><span></span><span></span><span></span><span></span></nobr>');
		if(type == 1){
			if(l <= 4){
				$("#bigGlass").find("span").eq(0).text(inputVal);
			}else if(l <= 8){
				$("#bigGlass").find("span").eq(0).text(inputVal.substring(0,4));
				$("#bigGlass").find("span").eq(1).text(inputVal.substring(4,l));
			}else if(l <= 12){
				$("#bigGlass").find("span").eq(0).text(inputVal.substring(0,4));
				$("#bigGlass").find("span").eq(1).text(inputVal.substring(4,8));
				$("#bigGlass").find("span").eq(2).text(inputVal.substring(8,l));
			}else{
				$("#bigGlass").find("span").eq(0).text(inputVal.substring(0,4));
				$("#bigGlass").find("span").eq(1).text(inputVal.substring(4,8));
				$("#bigGlass").find("span").eq(2).text(inputVal.substring(8,12));
				$("#bigGlass").find("span").eq(3).text(inputVal.substring(12,l));
			}
		}else{
				$("#bigGlass").find("span").eq(0).text(inputVal.substring(0,4));
				$("#bigGlass").find("span").eq(1).text(inputVal.substring(4,8));
				$("#bigGlass").find("span").eq(2).text(inputVal.substring(8,12));
				$("#bigGlass").find("span").eq(1).text(inputVal.substring(12,16));
				$("#bigGlass").find("span").eq(3).text(inputVal.substring(16,l));
		}
		$("#bigGlass").show();
	}
	//控制数字放大镜的显示与销毁
	$(document).click(function(event){
		var obj = event.srcElement || event.target;
		if($(obj).attr("id") != gId){
			$("#bigGlass").html("").hide();
		}else{
			showBigGlass();
		}
	});
}
$(function(){
	$("#CardNo").bigGlass(1);
	/*$("#CardNo1").bigGlass(2);*/
})