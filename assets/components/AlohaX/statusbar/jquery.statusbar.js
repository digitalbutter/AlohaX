/*
	THIS PLUGIN IS BASED ON jbar: http://tympanus.net/codrops/2009/10/29/jbar-a-jquery-notification-plugin/
*/
(function($) {
	
	$.fn.alohaStatus = function(options) {
		var opts = $.extend({}, $.fn.alohaStatus.defaults, options);
		return this.each(function() {
			$this = $(this);
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
			if(!$('.alohaStatus-container').length){
				$('body').prepend('<div class="alohaStatus-container"></div>');
			}
			var container = $('.alohaStatus-container');
			$(container).empty();
			$(container).show();
			if(!$('.alohaStatus').length){
				if(!o.waitUntilClose){
					timeout = setTimeout(function() { $.fn.alohaStatus.removebar(); },o.time);
				} else {
					//force remove button
					o.removebutton = true;
				}
				
				var _message_span = $(document.createElement('span')).addClass('alohaStatus-content').html(o.message);
				var _wrap_bar;
				(o.position == 'bottom') ? 
				_wrap_bar	  = $(document.createElement('div')).addClass('alohaStatus alohaStatus-bottom'):
				_wrap_bar	  = $(document.createElement('div')).addClass('alohaStatus alohaStatus-top') ;
				
				_wrap_bar.css({"background-color" 	: o.background_color});
				if(o.removebutton){
					var _remove_cross = $(document.createElement('a')).addClass('alohaStatus-cross');
					_remove_cross.click(function(e){$.fn.alohaStatus.removebar();})
				}
				else{				
					_wrap_bar.css({"cursor"	: "pointer"});
					_wrap_bar.click(function(e){$.fn.alohaStatus.removebar();})
				}
				
				_wrap_bar.addClass(o.type);
				if(o.type == 'warning'){
					if(o.success){
						_message_span.addClass('alohaStatus-success');
					} else {
						_message_span.addClass('alohaStatus-error');
					}
				} else {
					_message_span.css({"color" : o.color});
				}
					
				_wrap_bar.append(_message_span).append(_remove_cross).hide().appendTo(container).fadeIn('fast');
			}
		});
	};
	var timeout;
	$.fn.alohaStatus.removebar 	= function(txt) {
		if($('.alohaStatus').length){
			clearTimeout(timeout);
			$('.alohaStatus').fadeOut('fast',function(){
				$(this).remove();
				$(".alohaStatus-container").hide();
			});
		}	

	};
	$.fn.alohaStatus.defaults = {
		background_color 	: '#FFFFFF',
		color 				: '#000',
		position		 	: 'top',
		removebutton     	: true,
		time			 	: 5000	,
		type                : 'notice'
	};
	
})(jQuery);