/*
*	requires latest version of jquery
*/

function Widget(){
	var id = "widget";
	var height = 200;
	var width = 200;
	var color = "black";

	//callbacks for events
	var onHover = null;
	var onCreate = null;
	var onStart = null;
	var onDestroy = null;

	this.setHeight = function(new_height){
		height = new_height;
	}
	
	this.getHeight = function(){
		return height;
	}

	this.toElement = function(){
		ele = jQuery('</div>', {
	    id: id,
		  height: height,
		  width: width,
		  color: color
		});
		return ele;
	}

}
