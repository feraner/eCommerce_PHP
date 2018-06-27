/**
*	Use:元素拖动组件
var drag = new Drag();
drag.init({
	id:'',//指定需要拖动的元素
	isClone :'false', //拖动时是否复制
	toDown:funciton(ops){},//鼠标按下时操作
	toMove:funciton(ev,self){},//鼠标移动时操作
	toUp:funciton(){}//鼠标抬起时操作
});
**/
function Drag(){
    this.obj=null;
    this.disX=0;
    this.disY=0;
    
    this.settings={
        isClone:false, //是否复制对象
		hitPoint:[], //碰撞的对象
        toDown:function(ops){}, 
        toMove:function(ev,self){},
        toUp:function(){}
    }
}
Drag.prototype.init=function(opt){
    var self=this;
    this.obj=$("#"+opt.id).get(0);
    $.extend(this.settings,opt);
    this.obj.onmousedown=function(ev){
        var ev=ev || window.event;
        self.fnDown(ev);
        self.settings.toDown(ev,self);// 按下接口
        document.onmousemove=function(ev){
            var ev=ev || window.event;
            self.fnMove(ev);
            self.settings.toMove(ev,self);
        }
        document.onmouseup=function(){
            self.fnUp();
            self.settings.toUp();
			self.obj.releaseCapture && self.obj.releaseCapture();
        };
		self.obj.setCapture && self.obj.setCapture();
        return false;
    }
}
Drag.prototype.fnDown=function(ev){
    this.disX=ev.clientX-this.getLeft(this.obj);
    this.disY=ev.clientY-this.getTop(this.obj);
}
Drag.prototype.fnMove=function(ev){
    if(!this.settings.isClone){
		$(this.obj).css({'left':ev.clientX - this.disX});
    	$(this.obj).css({'top':ev.clientY - this.disY});
	}
}
Drag.prototype.fnUp = function(){
	document.onmousemove = null;
	document.onmouseup = null;
};
Drag.prototype.getLeft=function(e){
	var offset=e.offsetLeft; 
	if(e.offsetParent!=null) {
		offset+=this.getLeft(e.offsetParent); 
	}
	return offset; 
}
Drag.prototype.getTop=function(e){
	var offset=e.offsetTop; 
	if(e.offsetParent!=null) {
		offset+=this.getTop(e.offsetParent); 
	}
	return offset; 
}
Drag.prototype.removeBg=function(){
	for (var i = 0; i < this.settings.hitPoint.length; i++){
		$(this.settings.hitPoint[i]).css('background','');
	} 	
}
Drag.prototype.getDistance=function(obj1, obj2){	//找出两点的距离	
	var a = ($(obj1).offset().left + $(obj1).outerWidth()  / 2)  - ($(obj2).offset().left + $(obj2).outerWidth()  / 2);
	var b = ($(obj1).offset().top  + $(obj1).outerHeight() / 2)  - ($(obj2).offset().top  + $(obj2).outerHeight() / 2);
	return Math.sqrt(a * a + b * b);
}
Drag.prototype.findNearest=function(obj,hitObj){  //找出相遇点最近元素
	var aDistance = [];
	var i = 0;
	for (i = 0; i < hitObj.length; i++){
		aDistance[i] = $(hitObj[i]).get(0) == $(obj).get(0) ? Number.MAX_VALUE : this.getDistance(obj, $(hitObj[i]).get(0));
	} 
	var minNum = Number.MAX_VALUE;
	var minIndex = -1;
	for (i = 0; i < aDistance.length; i++){
		 aDistance[i] < minNum && (minNum = aDistance[i], minIndex = i);
	}
	return this.isButt(obj, hitObj[minIndex]) ? hitObj[minIndex] : null; //返回碰撞到的对象
}
Drag.prototype.isButt=function(obj1, obj2){ //对象碰撞
	var l1 = $(obj1).position().left;
	var t1 = $(obj1).position().top;
	var r1 = $(obj1).position().left + $(obj1).outerWidth();
	var b1 = $(obj1).position().top  + $(obj1).outerHeight();
	var l2 = $(obj2).offset().left;
	var t2 = $(obj2).offset().top;
	var r2 = $(obj2).offset().left + $(obj2).outerWidth();
	var b2 = $(obj2).offset().top  + $(obj2).outerHeight();
	return !(r1 < l2 || b1 < t2 || r2 < l1 || b2 < t1)
}