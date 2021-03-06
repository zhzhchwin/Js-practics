;(function($){
	
	$.fn.tab=function(options){
		var defaults={//各种参数，属性
			navHoverClass:'navHover',
			eventType:'click'
		};
		//使用传入参数（也是对象字面量）覆盖defaults里的默认值
		var options = $.extend(defaults,options);

		this.each(function(){
			var that=$(this);
			that.find('li').each(function(i){
				$(this).bind(options.eventType,function(){
					$(this).siblings().removeClass(options.navHoverClass).end().addClass(options.navHoverClass);
					$(this).parent().next().children().eq(i).show().siblings().hide();
			
				});

			});
			

		});
		return this;
	};

})(jQuery);
