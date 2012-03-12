/******************************************************************************
*	jquery-sitemenu.js
*
*	defines the behavior of the horizontal menu bar at the top of each page
*
******************************************************************************/
$(function(){
	var handle = 0;
	$("#menuitems").hide();
	
	//handle mouse events for the menu button
	$('#menu').hover(
		function(){ //onmouseover
			$('selector').css( 'cursor', 'pointer' );
			$(this).animate({backgroundColor: '#fff58f'}, 100);
			stopCloseMenu();
			$("#menuitems").show("fade", {}, 500);
		},
		function(){	//onmouseout
			$(this).animate({backgroundColor: '#fff'}, 100);
			startCloseMenu();
		}
	);

	//handle mouse events for the menu itself
	$('#menuitems').hover(
		function(){ //onmouseover
			stopCloseMenu();
		},
		function(){ //onmouseout
			startCloseMenu();
		}
	);

	//handle mouse events for each menu item
	//highlight selected menu item
	$('#menuitems a').hover(
		function(){ //onmouseover
			$(this).animate({backgroundColor: '#fff58f'}, 100);
		},
		function(){ //onmouseout
			$(this).animate({backgroundColor: '#fff'}, 100);
		}
	);

	//fade out the menu
	function closeMenu(){
		$("#menuitems").hide("fade", {}, 500);
	}
	
	//start the timer to close the menu in 100ms
	function startCloseMenu(){		
		handle = setTimeout(closeMenu, 100);
	}

	//cancel the timer that closes the menu
	function stopCloseMenu(){
		if(handle){
			clearTimeout(handle);
			handle = null;
		}
	}
});

