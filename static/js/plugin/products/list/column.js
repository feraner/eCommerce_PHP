/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
(function(a){
	a.pageless = function(c) {
		a.isFunction(c) ? c.call() : a.pageless.init(c)
	};
	a.pageless.settings = {
		currentPage: 1,
		pagination: ".pagination",
		url: location.href,
		params: {},
		distance: 100,
		loaderImage: "",
		marker: null,
		scrape: function(c) {
			return c
		}
	};
	a.pageless.loaderHtml = function() {
		return a.pageless.settings.loaderHtml || '<div id="pageless_loader" style="display:none; text-align:center; width:100%;"></div>'
	};
	a.pageless.init = function(c) {
		if (!a.pageless.settings.inited) {
			a.pageless.settings.inited = true;
			c && a.extend(a.pageless.settings, c);
			a.pageless.settings.pagination && a(a.pageless.settings.pagination).remove();
			a.pageless.startListener()
		}
	};
	a.pageless.isLoading = false;
	a.fn.pageless = function(c) {
		a.pageless.init(c);
		a.pageless.el = a(this);
		if (c.loader && a(this).find(c.loader).length) a.pageless.loader = a(this).find(c.loader);
		else {
			a.pageless.loader = a(a.pageless.loaderHtml());
			a(this).append(a.pageless.loader);
			c.loaderHtml || a("#pageless_loader .msg").html(c.loaderMsg)
		}
	};
	a.pageless.loading = function(c) {
		if (c === true) {
			a.pageless.isLoading = true;
			a.pageless.loader && a.pageless.loader.fadeIn("normal")
		} else {
			a.pageless.isLoading = false;
			a.pageless.loader && a.pageless.loader.fadeOut("normal")
		}
	};
	a.pageless.stopListener = function() {
		a(window).unbind(".pageless");
		a("#" + a.pageless.settings.loader).hide()
	};
	a.pageless.startListener = function() {
		a(window).bind("scroll.pageless", a.pageless.scroll);
		a("#" + a.pageless.settings.loader).show()
	};
	a.pageless.scroll = function() {
		if (a.pageless.settings.totalPages <= a.pageless.settings.currentPage) {
			a.pageless.stopListener();
			a.pageless.settings.afterStopListener && a.pageless.settings.afterStopListener.call()
		} else {
			var c = a(document).height() - a(window).scrollTop() - a(window).height();
			if (!a.pageless.isLoading && c < a.pageless.settings.distance) {
				a.pageless.loading(true);
				a.pageless.settings.currentPage++;
				a.extend(a.pageless.settings.params, {
					page: a.pageless.settings.currentPage
				});
				a.pageless.settings.marker && a.extend(a.pageless.settings.params, {
					marker: a.pageless.settings.marker
				});
				c = a.pageless.settings.url;
				c = c.split("#")[0];
				a.ajax({
					url: c,
					type: "GET",
					dataType: "html",
					data: a.pageless.settings.params,
					success: function(d) {
						d = a.pageless.settings.scrape(d);
						d = '<div class="BoardLayout" style="visibility:visible;">'+$(d).find('#ColumnContainer').html()+'</div>';
						a.pageless.loader ? a.pageless.loader.before(d) : a.pageless.el.append(d);
						a.pageless.loading(false);
						a.pageless.settings.complete && a.pageless.settings.complete.call();
						
						$('.currency_data').text(ueeshop_config.currency_symbols);
						$('html').priceShow();
						
						products_list_obj.effects_bind();
					}
				})
			}
		}
	}
})(jQuery);

var BoardLayout=function() {
	return {
		setup: function(a) {
			if (!this.setupComplete) {
				$(document).ready(function() {
					BoardLayout.allPins()
				});
				this.center = !!a;
				this.setupComplete = true
			}
		},
		pinsContainer: ".BoardLayout",
		pinArray: [],
		orderedPins: [],
		mappedPins: {},
		columnCount: 4,
		columns: 0,
		columnWidthInner: $('.prod_list .prod_box').outerWidth(),
		columnMargin: 20,
		columnPadding: 0,
		columnContainerWidth: 0,
		allPins: function() {
			var a = $(this.pinsContainer + " .pin"),
				c = $('#prod_list').width();//document.documentElement.clientWidth;
			
			this.columnWidthOuter = this.columnWidthInner + this.columnMargin + this.columnPadding;
			this.columns = Math.max(this.columnCount, parseInt(c / this.columnWidthOuter));
			if (a.length < this.columns) this.columns = Math.max(this.columnCount, a.length);
			c = this.columnWidthOuter * this.columns - this.columnMargin;
			var d = document.getElementById("prod_list");
			if (d) {
				d.style.width = c + "px";
			}
			$(".LiquidContainer").css("width", c + "px");
			for (c = 0; c < this.columns; c++) this.pinArray[c] = 0;
			document.getElementById("SortableButtons") ? this.showPins() : this.flowPins(a, true);
			if ($("#ColumnContainer .pin").length === 0 && window.location.pathname === "/") {
				$("#ColumnContainer").addClass("empty");
				setTimeout(function() {
					window.location.reload()
				},
				5E3)
			}
		},
		newPins: function(){
			this.flowPins($(this.pinsContainer + ":last .pin"));
		},
		flowPins: function(a, c){
			if(c){
				this.mappedPins={};
				this.orderedPins=[];
			}
			if(this.pinArray.length > this.columns) this.pinArray = this.pinArray.slice(0, this.columns);
			
			for(i=0; i<a.length; i++){
				c = a[i];
				var d=$(c).attr("data-id");
				if(d && this.mappedPins[d]){
					$(c).remove();
				}else{
					var e=jQuery.inArray(Math.min.apply(Math, this.pinArray), this.pinArray),
						f=this.pinArray[e];
					
					c.style.top=f+"px";
					c.style.left=e*this.columnWidthOuter+"px";
					this.pinArray[e]=f+c.offsetHeight+this.columnMargin;
					this.mappedPins[d]=this.orderedPins.length;
					this.orderedPins.push(d);
				}
			}
			var columnContainer = document.getElementById("ColumnContainer");
			if(columnContainer) columnContainer.style.height = Math.max.apply(Math, this.pinArray) + "px";
			
			this.showPins();
		},
		showPins: function() {
			$.browser.msie && parseInt($.browser.version);
			var a = $(this.pinsContainer);
			setTimeout(function() {
				a.css({
					visibility: "visible"
				})
			},
			200)
		}
	}
}();



var products_list_obj={
	init:function(){
		products_list_obj.price_limit($('#minprice'), $('#maxprice'), $('#submit_btn'));
		BoardLayout.setup();
		products_list_obj.effects_bind();
		
		$(window).resize(function(){
			BoardLayout.columnWidthInner=$('.prod_list .prod_box').width();
			BoardLayout.allPins();
			//BoardLayout.newPins();
		});
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
	},
	
	effects_bind:function(){
		var $effectsVal=$("#prod_list").attr("effects");
		if($effectsVal==1){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_1');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_1');
			});
		}else if($effectsVal==2){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_2');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_2');
			});
		}else if($effectsVal==3){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_3');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_3');
			});
		}else if($effectsVal==4){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_4');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_4');
			});
		}else if($effectsVal==5){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).children(".prod_box_pic").addClass("pic_enlarge");
			}).on("mouseleave", function(){
				$(this).children(".prod_box_pic").removeClass("pic_enlarge");
			});
		}else if($effectsVal==6){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				if($(this).find(".thumb_hover").length){
					$(this).find(".thumb").stop(true, true).animate({opacity:0}, 300);
					$(this).find(".thumb_hover").stop(true, true).animate({opacity:1}, 300);
				}
			}).on("mouseleave", function(){
				if($(this).find(".thumb_hover").length){
					$(this).find(".thumb").stop(true, true).animate({opacity:1}, 300);
					$(this).find(".thumb_hover").stop(true, true).animate({opacity:0}, 300);
				}
			});
		}
	}
}

products_list_obj.init();