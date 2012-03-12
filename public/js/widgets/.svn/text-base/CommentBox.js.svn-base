/*
*	requires the latest version of jquery
*/

(function($){
	var CommentBox = function(element){
		var elem = $(element);
		var title = $(document.createElement('div')).textelement();
		var body = $(document.createElement('div')).textelement();
		title = title.data("textelement");
		body = body.data("textelement");

		var self = this;
	
		//private variables
		var position = "relative";
		var hover_color = "red";
		var height = "auto";
		var width = "100%";

		//set default callback functions for events
		var doMouseEnter = function(){};
		var doMouseExit = function(){};
		var doMouseClick = function(){};

		//public methods
		this.setHeight = function(h){
			height = h;
		};
		this.setWidth = function(w){
			width = w;
		};
	
		this.setTitle = function(text){
			title.setText(text);
		};

		this.setBody = function(text){
			body.setText(text);
		};

		this.onMouseEnter = function(callback){
			doMouseEnter = callback;	
		};

		this.onMouseExit = function(callback){
			doMouseExit = callback;
		};


		this.prepare = function(){
			elem.css({
				position: position,
				height: height,
				width: width,
				borderBottom: "solid 1px lightgray",
				marginTop: "10px",
				marginBottom: "10px",
			});
			elem.append(title.prepare());
			elem.append(body.prepare());
			
			//event handlers
			elem.hover(doMouseEnter, doMouseExit);
			
			return elem;
		};

		this.getElement = function(){
			return elem;
		};


		//private methods




	};

	$.fn.commentbox = function(){
		return this.each(function(){
			var element = $(this);
			
			if(element.data("commentbox"))
				return;

			element.data("commentbox", new CommentBox(this));
		});
	};
})(jQuery);
