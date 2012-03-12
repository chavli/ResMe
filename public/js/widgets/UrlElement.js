/*
*	requires the latest version of jquery
*/
(function($){
	var UrlElement = function(element){
		var elem = $(element);
		var text = "";
		var url = "http://www.google.com";
		var tagclass = "normalText";

		//event listeners
		var doMouseEnter = function(){};
		var doMouseExit = function(){};
		var doMouseClick = function(){};

		//setters
		this.setText = function(new_text){
			text = new_text;
		};

		this.setUrl = function(new_url){
			url = new_url;
		};
	
		//getters
		this.getText = function(){
			return text;
		};

		this.getUrl = function(){
			return url;
		};

		//implement event listeners
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
			var a = $(document.createElement('a'));
			a.attr("href", url);
			a.html(text);
			elem.attr("class", tagclass);
			elem.append(a);
			return elem;
		};

	};

	$.fn.urlelement = function(){
		return this.each(function(){
			var element = $(this);
			if(element.data("urlelement"))
				return;

			element.data("urlelement", new UrlElement(this));
		});
	};

})(jQuery);



