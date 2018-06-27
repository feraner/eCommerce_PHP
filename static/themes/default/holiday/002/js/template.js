$.fn.genTimer=function(e){
	function u(e){
		var t=Math.floor(e/n),
			r=Math.floor((e-t*n)/36e5),
			i=Math.floor((e-t*n-r*1e3*60*60)/6e4),
			s=Math.floor((e-t*n-r*1e3*60*60-i*1e3*60)/1e3);
		return {hours:("0"+r).slice(-2), minutes:("0"+i).slice(-2), seconds:("0"+s).slice(-2), dates:t}
	}
	
	var t={
			beginTime:new Date,
			day_label:"day",
			days_label:"days",
			unitWord:{hours:"", minutes:"", seconds:""},
			type:"diffNoDay",
			callbackOnlyDatas:!1
		},
		n=864e5,
		r=$.extend({}, t, e),
		i=this;
		
	r.targetTime=r.targetTime.replace(/\-/g, "/");
	var s=new Date(r.targetTime)-new Date(r.beginTime),
	o=function(){
		if(s<0){
			r.callback.call(i, 'End Time: <span class="hours">00</span>Hrs<span class="miniutes">00</span>Mins<span class="senconds">00</span>Secs');
			clearInterval(i.interval);
		}else{
			var e=u(s);
			if(r.callbackOnlyDatas) r.callback.call(i, e);
			else if(r.type=="day") s>=n*2 ? r.callback.call(i, 'End Time: <span class="day_count">'+e.dates+'</span><span class="day">'+r.days_label+'</span><span class="day_seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>") : s>=n ? r.callback.call(i, '<span class="day_count">'+e.dates+'</span><span class="day">'+r.day_label+'</span><span class="day_seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>") : r.callback.call(i, '<span class="seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>");
			else if(r.type=="diffNoDay"){
				var t=e.hours;
				s>=n && (t=Number(e.dates*24)+Number(e.hours));
				r.callback.call(i, 'End Time: <span class="hours">'+t+'</span>Hrs<span class="miniutes">'+r.unitWord.hours+e.minutes+'</span>Mins<span class="senconds">'+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>Secs");
			}else{
				var t=e.hours;
				s>=n && (t=Number(e.dates*24)+Number(e.hours));
				r.callback.call(i, 'End Time: <span class="seconds">'+t+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>");
			}
		}
		s-=1e3
	};
	i.interval=setInterval(o, 1e3);
	o();
	return this
}

$(".pro_list_0").find(".time").each(function(){
	var time=new Date();
	$(this).genTimer({
		beginTime: ueeshop_config.date,
		targetTime: $(this).attr("endTime"),
		callback: function(e){
			this.html(e)
		}
	});
});