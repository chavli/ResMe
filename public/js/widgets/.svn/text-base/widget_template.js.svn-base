/*
*	requires the latest version of jquery
*/
(function($){
	var WidgetName = function(element){
		var elem = $(element);

		//event listeners
		var doMouseEnter = function(){};
		var doMouseExit = function(){};
		var doMouseClick = function(){};

		this.onMouseEnter = function(callback){
			doMouseEnter = callback;
		};
		
		this.onMouseExit = function(callback){
			doMouseExit = callback;
		};

		this.onMouseClick = function(callback){
			doMouseClick = callback;
		};


	};

	$.fn.widgetname = function(){
		return this.each(function(){
			var element = $(this);
			if(element.data("widgetname"))
				return;

			element.data("widgetname", new WidgetName(this));
		});
	};

})(jQuery);



