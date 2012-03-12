/*
*	requires the latest versoin of jquery
*/

(function($){
	var TextElement = function(element){
		var elem = $(element);
		var text = "";
		var tagclass = "normalText";

		var doMouseEnter = function(){};
		var doMouseExit = function(){};
		var doMouseClick = function(){};

		this.setText = function(str){
			text = str;
		};

		this.getText = function(){
			return text;
		};

		this.onMouseEnter = function(callback){
			doMouseEnter = callback;
		};
		
		this.onMouseExit = function(callback){
			doMouseExit = callback;
		};

		this.onMouseClick = function(callback){
			doMouseClick = callback;
		};

		this.prepare = function(){
			elem.attr("class", tagclass);
			elem.html(text);
			return elem;
		};

	};

	$.fn.textelement = function(){
		return this.each(function(){
			var element = $(this);
			if(element.data("textelement"))
				return;
			element.data("textelement", new TextElement(this));
		});
	};
	
	
	
})(jQuery);
