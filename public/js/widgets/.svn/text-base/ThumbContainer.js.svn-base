/*
*	requires the latest version of jquery
*	UrlElement.js
*	TextElement.js
*
*/
(function($){
	var ThumbContainer = function(element, imgurl){
		var elem = $(element);
		var imgsrc = imgurl;
		var imgid = "";
		var imgurl = "";
		var imgclass = "shadowed-gray";
		
		//lines of text under the image
		var url = "";
		var urltext = "";
		var line1 = "";
		var line2 = "";

		var url_div = $(document.createElement('div')).urlelement(); 
		var line1_div = $(document.createElement('div')).textelement(); 
		var line2_div = $(document.createElement('div')).textelement(); 
		
		url_div = url_div.data("urlelement");
		line1_div = line1_div.data("textelement");
		line2_div = line2_div.data("textelement");

		this.setImgSrc = function(src){
			imgsrc = src;
		};

		this.setImgId = function(id){
			imgid = id;
		};

		this.setImgLink = function(url){
			imgurl = url;
		};

		this.setUrlElem = function(text, href){
			url = href;
			urltext = text;
		};

		this.setTextElems = function(first, second){
			line1 = first;
			line2 = second;
		};
	
		this.getImgId = function(){
			return imgid;
		};

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

		this.prepare = function(){
			//prepare the img tag
			var thumb = $(document.createElement('img'));
			thumb.attr("id", imgid);
			thumb.attr("src", imgsrc);
			thumb.attr("class", imgclass);
			thumb.css({
				display: "block",
				border: "solid 1px LightGray",
				width: "160px"
			});
			if(imgurl.length > 0){
				var a = $(document.createElement('a'));
				a.attr("href", imgurl);
				a.append(thumb);
				thumb = a;
			}
			
			if(line2.length > 0){
				line2_div.setText(line2);			
				elem.prepend(line2_div.prepare());
			}

			if(line1.length > 0){
				line1_div.setText(line1);
				elem.prepend(line1_div.prepare());
			}

			if(url.length > 0){
				url_div.setUrl(url);
				url_div.setText(urltext);
				elem.prepend(url_div.prepare());
			}

			elem.prepend(thumb);
			elem.css({
				display: "inline-block",
				textAlign: "center",
				margin: "20px"
			});

			return elem;
		};
	};

	$.fn.thumbcontainer = function(imgsrc){
		return this.each(function(){
			var element = $(this);
			if(element.data("thumbcontainer"))
				return;

			element.data("thumbcontainer", new ThumbContainer(this, imgsrc));
		});
	};

})(jQuery);



