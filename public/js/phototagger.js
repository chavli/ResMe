/*
* Original PhotoTagger plugin written by Ben Nadel Copyright www.bennadel.com 2010.
*/

(function( window, $ ){
	function PhotoTagger( container, settings ){
		var inst = this; 																//used to reference closure methods
		this.container = container;											//container that holds the phototagger and tags
		this.settings = settings;												//phototagger settings
		this.image = this.container.children( "img" );	//the image object being tagged
		this.tags = $( [] ); 														//all the tags associated with the current phototagger instance
		this.pendingTag = null;													//the current pending tag (being drawn by user)
		this.isTagCreationEnabled = this.settings.isTagCreationEnabled;
		this.isTagDeletionEnabled = this.settings.isTagDeletionEnabled;

		// Check to make sure the container has a position that 
		// will allow us to absolutely position the other elements
		// inside of it.
		if (
			(this.container.css( "position" ) != "relative") &&
			(this.container.css( "position" ) != "absolute") &&
			(this.container.css( "position" ) != "fixed")
			){
			
			// Make this contianer relative.
			this.container.css( "position", "relative" );
		}
				
		// Resize the container to be the dimensions of the 
		// image so that we don't have any mouse confusion.
		this.container.width( this.image.width() );
		this.container.height( this.image.height() );
		
		// Strip out the ALT and TITLE tags to prevent mouse over displays in the browser.
		this.container.removeAttr( "title" );
		this.image.removeAttr( "title" ).removeAttr( "alt" );
		
		this.isActiveContainer = false;	//whether the phototagger is active
		this.isLoadingRequired = true;	//whether or not tags need to be loaded from server
		
		// Check to see if the user wants to delay the loading
		// of existing tags.
		if (!this.settings.isDelayedLoad){
			// Flag that the tags don't need to be loaded.
			this.isLoadingRequired = false;
		
			// Load existing tags immediately.
			this.loadTags();
		}
		
		// Bind to the hover event on the container. When the user
		// hovers over the container, we want to show the tags
		// associated with this photo.
		this.container.hover(
			function( event ){
				// Activate the container.
				inst.activateContainer();
			},
			function( event ){
				// If the user is currently drawing a tag, then we
				// don't want to deactivate the container.
				if (!inst.isUserCreatingTag()){
					// There is no pending activity on the 
					// container, so deactivate it.
					inst.deactivateContainer();
				}
			}
		);	
		
		// Bind to the mouse down even on the container. If the 
		// user clicks down, they *probably* want to start drawing
		// a new tag hotspot.
		this.container.mousedown(
			function( event ){
				// Get the target of the click (if it is an 
				// existing tag, we have some more logic).
				var target = $( event.target );
								
				// Check to see if new tag creation is enabled. 
				// We only want to let the user draw new tags in
				// this state such that new tags are not randomly
				// being created. Also, we don't want the user to
				// start a tag ON an existing tag (this helps us
				// with the delete dble-click).
				if (
					inst.isTagCreationEnabled &&
					!target.is( "a." + inst.getFullClassName( "tag" ) )
					){
				
					// The user is going to start drawing. Cancel 
					// the default event to make sure the browser 
					// does not try to select the IMG object.
					event.preventDefault();
					
					// Set up the container for manual tag 
					// creation (by the user).
					inst.setupPendingTagCreation(
						event.clientX, 
						event.clientY 
					);
				}
			}
		);
		
		// Bind to the dragstart event (Internet Explorer) to
		// prevent the image from being dragged. This will allow
		// the box to be draw without interuption.
		this.container.bind(
			"dragstart selectstart",
			function( event ){
				// Prevent event.
				return( false );
			}
		);
	}
	
	PhotoTagger.prototype = {
	  /* activate the phototagger container. */
		activateContainer: function(){
			// Flag that the container is active.
			this.isActiveContainer = true;
			// Check to see if the tags need to be loaded.
			if (this.isLoadingRequired){
				
				// Since we are about to load, immediately flag 
				// that no more loading is need (so we don't 
				// fire the load more than once).
				this.isLoadingRequired = false;
		
				// Load existing tags immediately.
				this.loadTags();
			}
			// Show the tags.
			this.showTags();
		},
		
		
		/* add a new tag to the phototagger */
		addTag: function( id, x, y, width, height, message, type ){
			var inst = this;
			// Create the physical tag.
			var tag = this.createTag( x, y, width, height );
			
			// Associate the appropriate data with the tag.
			tag.data( "id", id );
			tag.data( "data", message );
			tag.data( "type", type);

			// Bind the mouse over event on this tag (will show 
			// the associated message).
			/*NOTE: this is the code that is executed when a tag is clicked */
			tag.bind(
				"mousedown.photoTagger",
				function(){
					// Check to see if the user is currently
					// creating a new tag. If so, we don't 
					// want to interfere with that experience.
					if (!inst.isUserCreatingTag()){
						//show info					
						inst.displayTag(tag);
					}
				}
			);
				
			/*NOTE: this is the code that is executed when the mouse hovers over a tag*/	
			tag.bind(
				"mouseover.photoTagger",
				function(){
					if(!inst.isUserCreatingTag()){
						//emphasize the tag
						inst.emphasizeTag( tag );
					}
				}
			);

			// Bind the mouse out event on this tag.
			tag.bind(
				"mouseout.photoTagger",
				function(){
					// Check to see if the user is currently
					// creating a new tag.  
					if (!inst.isUserCreatingTag()){
						// Deemphasize the given tag visually.
						inst.deemphasizeTag( tag );
						var tagname = tag.data( "data" ).toString();
					}
				}
			);
			
			// Add the tag to the internal collection.
			this.tags = this.tags.add( tag );
			
			// Add the tag to the container.
			this.container.append( tag );
			
			// Show the tag if there is an active phototagger container
			if (this.isActiveContainer){
				tag.show();
				
			}
			return( tag );
		},
		
		/* 
		* Add a pending tag at the given position and store it
		* as the global pending tag.
		*/
		addPendingTag: function( left, top ){
			// Create the new tag.
			var tag = this.createTag( left, top );
			
			// Set the anchor points for the tag. This is the 
			// point from which the drawing will be made 
			// (regardless of technical position).
			tag.data({
				anchorLeft: left,
				anchorTop: top
			});
			
			// Set it as the pending tag.
			this.pendingTag = tag;
			
			// Add it to the container.
			this.container.append( tag );
			
			// Since tags start out hidden, show this one after 
			// it is added to the container.
			tag.show();
		
			// Return the new, pending tag.
			return( tag );
		},
		
		/* create a new tag object */
		createTag: function( left, top, width, height ){
			// Create the new tag.
			var tag = $( "<a class='" + this.getFullClassName( "tag" ) + "'></a>" );
			
			// Set the absolute positon (within the container).
			// By default, the tag will start out hidden.
			tag.css({
				left: (left + "px"),
				top: (top + "px"),
				width: ((width || 1) + "px"),
				height: ((height || 1) + "px"),
				display: "none"
			});
		
			// Return the new tag object.
			return( tag );
		},
		
		
		/* Deactivate the container.*/
		deactivateContainer: function(){
			// Flag that the container is no longer active.
			this.isActiveContainer = false;
			this.hideTags();
		},
		
		/* de-emphasize the given tag */
		deemphasizeTag: function( tag ){
			// Make sure to deselected tag.
			tag.removeClass( 
				this.getFullClassName( "selected-tag" )
			);
			
			// Show all the tags at their normal state.
			this.tags.css( "opacity", 0.6 );
		},
		
		/* prepare to delete tag */
		deleteTag: function( tag ){
			// Delete the tag record.
			this.deleteTagRecord( tag.data( "id" ), tag.data( "type" ), tag.data( "data"));
			// Remove the elements from the collection.
			this.tags = this.tags.not( tag );
			
			// Remove the tag from the container.
			tag.remove();		
		},
		
		/*  delete tag from the server */
		deleteTagRecord: function( id, type, data, onSuccess ){
      var inst = this;
			var album_id = 0;
			if(type == inst.settings.IMG_TAG) //special case for image tags
				album_id = data.split(",")[0];
			$.ajax({
				method: this.settings.deleteMethod,
				url: this.settings.deleteURL,
				data: {
					data: data,
					albumid: album_id,
					type: type,
					id: id,
          username: this.settings.username
				},
				dataType: "json",
				cache: false,
				success: function( response ){
					// Pass off to handler (if it exists).
					if (onSuccess){
						onSuccess(response);
					}
				}
			});
		},
		
		disableTagCreation: function(){
			this.isTagCreationEnabled = false;
		},
		
		disableTagDeletion: function(){
			this.isTagDeletionEnabled = false;
		},
		
		emphasizeTag: function( tag ){
			// Dim all the tags' opacity to visually bring
			// out the currently selected tag.
			this.tags.css( "opacity", this.settings.minOpacity );
			
			// Visually pop the current tag.
			tag.css( "opacity", 1 );
		},

		/* display the contents of a selected tag */
		displayTag: function( tag ){
			var inst = this;
			// Get the current position of the tag.
			var tagPosition = tag.position();
		
			$("#background").css({
					"opacity":"0.7"
				});

			//do some math to center modal window
			var width = $(window).width();
			var height = $(window).height();
				
			//600x460 is defined in profile.css under #tagger 
			var _left = (width - 600) / 2;
			var _top = (height - 450) / 2;
				
			$("#viewer").css({
				"left": _left,
				"top": _top
			});
			//NOTE .innerHTML depends on tag type
			//create tag html based on type
			switch(parseInt(tag.data("type"))){
				case inst.settings.TXT_TAG:
					document.getElementById("tagData").innerHTML = "<span class='normalText'>" + tag.data("data") + "</span>";
					break;
				case inst.settings.IMG_TAG:
 					var dataArray = tag.data("data").split(",");
 					var albumName = dataArray[0];
 					var albumSize = parseInt(dataArray[1]);
 					var jsString = "";
 					for(var i=0;i<albumSize;i++){
 						jsString+="<img src='/users/"+this.settings.username+"/albums/"+albumName+"/"+i+".jpg'>";
 					}
 					document.getElementById("tagData").innerHTML = jsString;
 					$('#tagData').galleria({
 						height:430
 					});      
					break;
				case inst.settings.LVID_TAG:
				case inst.settings.UVID_TAG:
					document.getElementById("tagData").innerHTML = 
						"<object width='560' height='360'>" +
							"<param name='src' value='"+ tag.data("data") +"'/>" +
							"<param name='autoplay' value='false'/>" +
							"<param name='cache' value='true'/>" +
					 		"<param name='wmode' value='transparent' />" +
					  	"<embed src='"+ tag.data("data") +"' width='560' height='360' autoplay='false' cache='true' />" +
						"</object>";
					break;
				case inst.settings.SND_TAG:
					break;
				case inst.settings.DOC_TAG:
					break;
			}

			$("#viewer").fadeIn("fast");
			$("#background").fadeIn("fast");
		
			//delete the tag
			$("#deleteTag").click(function(){
				if (confirm( "Delete this tag?" )){
						inst.deleteTag( tag );
						closeTagger();
						$("#deleteTag").unbind();	
				}			
			});

		},

		enableTagCreation: function(){
			this.isTagCreationEnabled = true;
		},
		
		enableTagDeletion: function(){
			this.isTagDeletionEnabled = true;
		},
		
		// Return the full CSS class name based on covenience name 
		getFullClassName: function( className ){
			// Prepend the CSS namespace.
			return( this.settings.cssNameSpace + className );
		},
		
		// Get the container-local top / left coordiantes 
		// of the current mouse position based on the given page-
		// level X,Y coordinates.
		getLocalPosition: function( mouseX, mouseY ){
			// Get the current position of the container.
			var containerOffset = this.container.offset();
		
			// Adjust the client coordiates to acocunt for 
			// the offset of the page and the position of the 
			// container.
			var localPosition = {
				left: Math.floor( 
					mouseX - containerOffset.left + window.scrollLeft() 
				),
				top: Math.floor( 
					mouseY - containerOffset.top + window.scrollTop() 
				)
			};
			
			// Return the local position of the mouse.
			return( localPosition );
		},
		
		// Hide the tags associated with this photo.
		hideTags: function(){
			this.tags.hide();
		},
		
		// Check to see if the given tag size is valid 
		isPendingTagSizeValid: function(){
			// Get the pending tag dimensions.
			var pendingWidth = this.pendingTag.width();
			var pendingHeight = this.pendingTag.height();
			var pendingLeft = this.pendingTag.position().left;
			var pendingTop = this.pendingTag.position().top;
		
			// Loop over the existing tags to see if any of them
			// are being eclipsed by the pending tag size.
			for (var i = 0 ; i < this.tags.size() ; i++){
				// Get the current tag.
				var tag = this.tags.eq( i );	
				
				// Get the current tag position.
				var position = tag.position();
				
				// Check to see if the position is too small.
				if (
					(position.top >= pendingTop) &&
					((position.top + tag.height()) <= (pendingTop + pendingHeight)) &&
					(position.left >= pendingLeft) &&
					((position.left + tag.width()) <= (pendingLeft + pendingWidth))					
					){
					// Tag is eclipsed, return false.
					return( false );
				}
			}
			return( true );		
		},
		
		// Determine if the user is currently drawing a  pending tag.
		isUserCreatingTag: function(){
			// If there is a pending tag, return true.
			return( !!this.pendingTag );
		},
		
		// initilize settings for loading tags 
		loadTagRecords: function( onSuccess ){
			var inst = this;
			$.ajax({
				method: "get",
				url: this.settings.loadURL,
				data: {
					resumepage: this.settings.resumepage,
          username: this.settings.username
				},
				dataType: "json",
				cache: false,
				success: function( data ){
					// Pass off to handler (if it exists).
					if (onSuccess){
						onSuccess(data);
					}
				}
			});
		},
		
		// Load the tags from the server and translate them into tags in the photo container.
		loadTags: function(){
			var inst = this;
			// Load the tag records.
			this.loadTagRecords(
				function( data ){
					// Loop over the response data to create a
					// tag for each record.
					$.each(
						data.tags,
						function( index, tagData ){
							// Add the tag.
							inst.addTag(
								tagData.id,
								tagData.x,
								tagData.y,
								tagData.width,
								tagData.height,
								tagData.data,
								tagData.type
							);
						}
					);
				}
			);
		},
		
		// Resize the pending tag based on the given mouse position.
		resizePendingTag: function( mouseX, mouseY ){
			// Get the local position of the mouse.
			var localPosition = this.getLocalPosition( 
				mouseX, 
				mouseY 
			);
			
			// Get the current anchor position of the tag.
			var anchorLeft = this.pendingTag.data( "anchorLeft" );
			var anchorTop = this.pendingTag.data( "anchorTop" );
	
			// Get the height and width of the pending tag based on its current position plus the position of the 
			// mouse.
			var width = Math.abs(
				(localPosition.left - anchorLeft)	
			);
			
			var height = Math.abs(
				(localPosition.top - anchorTop)
			);
			
			//set the size of the pending tag, with a min size of 10x10
			this.pendingTag.width( Math.max( width, 10 ) );
			this.pendingTag.height( Math.max( height, 10 ) );
			
			// Check to see if the mouse position is greater than the original anchor position.
			// Check for left translation.
			if (localPosition.left < anchorLeft){
				// Move left.
				this.pendingTag.css( 
					"left", 
					(localPosition.left + "px") 
				);
			}
			
			// Check for top translation.
			if (localPosition.top < anchorTop){
				// Move up.
				this.pendingTag.css( 
					"top", 
					(localPosition.top + "px") 
				);
			}
		},
		
		// Save the given tag.
		saveTag: function(_left, _top, type, tag ){
			var inst = this;
			// Get the tag position.
			var position = tag.position();
			// Save the tag record.
			inst.saveTagRecord(
				tag.data( "id" ),
				_left,
				_top,
				tag.width(),
				tag.height(),
				tag.data( "data" ),
				inst.settings.resumepage,
				type,
				// If the AJAX response comes back successfully,
				// associate the given ID.
				function( data ){
          if(data){
					  tag.data( "id", data.lastID );
          }
				}
			);
		},
		
		//pass the data to the server to be stored
		saveTagRecord: function( id, x, y, width, height, data, resumepage, type, onSuccess ){
			var inst = this;
      $.ajax({
        method: this.settings.saveMethod,
        url: this.settings.saveURL,
        data: {
          "0": resumepage,
          "1": x,
          "2": y,
          "3": width,
          "4": height,
          "5": data,
          "6":	type,
          username: this.settings.username
        },
        dataType: "json",
        cache: false,
        success: function( data ){
          // Pass off to handler (if it exists).
          if (onSuccess){
            onSuccess(data);
          }
        },
        error: function(a, b, c){
          alert(c);
        }
      });
		},
		
		// Set up the tag creation state when the user creates a tag 
		setupPendingTagCreation: function( clickX, clickY ){
			var inst = this;
			
			//start coordinates (upper left) of new tag
			var localPosition = this.getLocalPosition( 
				clickX, 
				clickY 
			);
			
			//set position of the new tag
			this.addPendingTag(
				localPosition.left,
				localPosition.top
			);
		
			// allow resizing of tags by dragging the moust around
			this.container.bind(
				"mousemove.photoTagger",
				function( event ){
					// Resize the pending tag.
					inst.resizePendingTag( 
						event.clientX, 
						event.clientY 
					);
				}
			);
					
			// Stop drawing and finalize tag on mouseup 
			this.container.bind(
				"mouseup.photoTagger",
				function(){
					inst.teardownPendingTagCreation();
				}
			);
		},
				
		// show the tags associated with this photo.
		showTags: function(){
			this.tags.show();
		},
		
		/*
		*	Convert pending tag into actual tag
		*/
		teardownPendingTagCreation: function(){
			var inst = this;
			
			// Unbind any mouse up and mouse move events on container.
			this.container.unbind( "mouseup.photoTagger" );
			this.container.unbind( "mousemove.photoTagger" );
			
			// Check to see if the current tag size is valid. 
			if (this.isPendingTagSizeValid()){
				var inst  = this;
				var curTag = this.pendingTag;
				
				// Now that the user has drawn the tag, let's prompt them for the message to be associated.
				$("#background").css({
					"opacity":"0.7"
				});

				//do some math to center modal window
				var width = $(window).width();
				var height = $(window).height();
				
				//600x500 is defined in profile.css under #tagger 
				var _left = (width - 600) / 2;
				var _top = (height - 500) / 2;
				
				$("#tagger").css({
					"left": _left,
					"top": _top
				});

				$("#background").fadeIn("fast");
				$("#tagger").fadeIn("fast");
				$("ul.tabs li:first").addClass("active").show(); //Activate first tab
				$(".tabContents:first").show(); //Show first tab content

				//style the error modalwindow. values defined in profile.css
				 _left = (width - 450) / 2;
				 _top = (height - 25) / 2;

				$("#error").css({
					"left": _left,
					"top": _top
				});
				var stderr = document.getElementById("error");
			
				//store tag values to be used in click function
				var _left = curTag.position().left; 
				var _top = curTag.position().top;
				var _width = curTag.width();
				var _height = curTag.height();

				// Check to see if the message was returned  
				$("#textSubmit").click( function(){
					var message = document.getElementById("textInput").value;
					if(message){
						// Create a tag based on our pending tag. We
						// know everything BUT the ID at this point.
						var tag = inst.addTag(
							"", 
							_left, 
							_top, 
							_width, 
							_height, 
							message,
							inst.settings.TXT_TAG	
						);
						// Save this tag (to the server).
						inst.saveTag( _left, _top, inst.settings.TXT_TAG	, tag );
						closeTagger();
						unbindTagger();
						resetTagger();
					}
					else{
						stderr.innerHTML = "<span class='headerText'>Nothing to submit.</span>"; 
						$("#tagger").fadeTo(300, .3).delay(1750).fadeTo(300, 1.0); 
						$("#error").fadeIn("fast").delay(1750).fadeOut("slow");
					}
				});
 				
				/*
				*
				* Upload picture(s)
				*
				*/
 				var callback = function(responseText, statusText, xhr, $form){
 					if(responseText){
						var is_error = false;
			      //error check photo tag
     			  var status = responseText.split(",");
						//error values are generated by uploadPics
		        if(status[0] < 0){  //a negative value means an error occured in uploadPic.php
		          is_error = true;
							stderr.innerHTML = "<span class='headerText'>" + status[1] + "</span>"; 
							$("#tagger").fadeTo(300, .3).delay(1750).fadeTo(300, 1.0); 
							$("#error").fadeIn("fast").delay(1750).fadeOut("slow");
       			}		
						
						if(!is_error){
 							// Create a tag based on our pending tag. We know everything BUT the ID at this point.
 							var tag = inst.addTag(
 								"", 
 								_left, 
 								_top, 
 								_width, 
 								_height, 
 								responseText,
 								inst.settings.IMG_TAG	
 							);
 							// Save this tag (to the server).
 							inst.saveTag( _left, _top, inst.settings.IMG_TAG	, tag );
							closeTagger();
							unbindTagger();
							resetTagger();
						}
 					}
 				}
 				var options = {
 					//pre-submit callback
 					beforeSubmit:  function(formData, jqForm, options){
 						$.fn.MultiFile.disableEmpty();
 						return true;
 					},
 					//post-submit callback
 					success: callback,
 			 		url: "uploadPic.php",    // override for form's 'action' attribute
 					clearForm: true
 				};
 				$('#picForm').submit(function() { 
 					// inside event callbacks 'this' is the DOM element so we first 
 					// wrap it in a jQuery object and then invoke ajaxSubmit 
           $(this).ajaxSubmit(options); 
 					// always return false to prevent standard browser submit and page navigation 
 					return false; 
 				});         

				/*
				*
				* Upload video file
				*
				*/
 				var finalizeVidSubmit = function(data, statusText, xhr, $form){
					if(data){
						var is_error = false;
						/*data is json
						* data.code- error code. < 0 means an error
						* data.status -  or error message
            * data.path - filepath of video
						*/
		        if(data.code < 0){  //a negative value means an error occured in uploadPic.php
		          is_error = true;
							stderr.innerHTML = "<span class='headerText'>" + data.status + "</span>"; 
							$("#tagger").fadeTo(300, .3).delay(1750).fadeTo(300, 1.0); 
							$("#error").fadeIn("fast").delay(1750).fadeOut("slow");
          		$('#vidForm').reset();
			      	$('#upload_frame').hide();
       			}		
						
						if(!is_error){
 							// Create a tag based on our pending tag. We
 							// know everything BUT the ID at this point.
 							var tag = inst.addTag(
 								"", 
 								_left, 
 								_top, 
 								_width, 
 								_height, 
 								data.path,
 								inst.settings.UVID_TAG
 							);
 							// Save this tag (to the server).
 							inst.saveTag( _left, _top, inst.settings.UVID_TAG, tag );
							closeTagger();
							unbindTagger();
							resetTagger();
						}
 					}
 				};

 				var ajaxOptions = {
 					//post-submit callback
 					success: finalizeVidSubmit,
					error: function(a, errorType, errorText){
						alert("bad return: " + errorText);
					},
 			 		url: "/backend/profile-backend.php?act=upload",    // override for form's 'action' attribute
          dataType:"json",
 					clearForm: true
 				};

				var show_bar = 0;
		   	$('#vidFile').click(function(){
			  	show_bar = 1;
				});

			  //show iframe on form submit
			  $("#vidForm").submit(function(){
          $(this).ajaxSubmit(ajaxOptions); 
			     $('#upload_frame').show("clip");
					 var id= $("#progress_key").val();
			     function set () {
			       $('#upload_frame').attr('src','/backend/upload-frame.php?up_id='+ id);
			     }
			     setTimeout(set);
					return false;
			  });

				/*
				*
				* Linked youtube video
				*
				*/
				$("#urlSubmit").click( function(){
					var url = "http://www.youtube.com/watch";
					var data = document.getElementById("urlInput").value;
					if(data){
						//check if url is correct
						if(data.indexOf(url) == 0){																							//http://www.youtube.com/watch?v=LuJmEfO2yNI&feature=featured
							//parse out the value 'v' is set to
							data = data.replace(url, "");																					//?v=LuJmEfO2yNI&feature=featured
							var end = (data.indexOf("&") != -1) ? data.indexOf("&") : data.length;
							data = data.slice(1, end);																						//v=LuJmEfO2yNI
							data = data.replace("v=", "");																				//LuJmEfO2yNI
							var tag = inst.addTag(
								"", 
								_left, 
								_top, 
								_width, 
								_height, 
								"http://www.youtube.com/v/" + data,
								inst.settings.LVID_TAG	
							);
							// Save this tag (to the server).
							inst.saveTag( _left, _top, inst.settings.LVID_TAG, tag );
							closeTagger();
							unbindTagger();
							resetTagger();
						}
						else{
							stderr.innerHTML = "Invalid Youtube link.";
							$("#tagger").fadeTo(300, .3).delay(2000).fadeTo(300, 1.0); 
							$("#error").fadeIn("fast").delay(2000).fadeOut("slow");
						}
					}
				});
				/*** END video tag ***/
			}
			else {
				stderr.innerHTML = "This tag is too large.";
				$("#tagger").fadeTo(300, .3).delay(2000).fadeTo(300, 1.0); 
				$("#error").fadeIn("fast").delay(2000).fadeOut("slow");
			}

			//clean up processed tag
			this.pendingTag.remove();
			this.pendingTag = null;
		},
		
		toggleTagCreation: function(){
			this.isTagCreationEnabled = !this.isTagCreationEnabled;
		},
		
		toggleTagDeletion: function(){
			this.isTagDeletionEnabled = !this.isTagDeletionEnabled;
		}
	};
	/*** END teardownPendingTagCreation ***/


	/*
	*	Apply the plugin to the given collecion of elements
	*/
	var isCSSLoaded = false;
	var applyPhotoTagger = function( collection, options ){
		// Check to see if the required CSS has been loaded. Only do this if the 
		//user hasn't already supplied the CSS.
		if ($.fn.photoTagger.defaultOptions.applyCSS &&	!isCSSLoaded){
			isCSSLoaded = true;
			var styleText = [];
			
			// Loop over each CSS selector to create a rule in our style string buffer.
			$.each(
				$.fn.photoTagger.defaultOptions.css,
				function( selector, rule ){
					// Append the start of the rule.
					styleText.push( 
						selector.replace( 
							new RegExp( "\\." ),
							("." + $.fn.photoTagger.defaultOptions.cssNameSpace)
						) + 
						" { " 
					);
				
					// Loop over the rule items.
					$.each(
						rule,
						function( propertyName, value ){
							styleText.push(
								propertyName + ": " + value + " ;"
							);
						}
					);
					styleText.push( " } " );
				}
			);
			
			// append CSS rules to page 
			$( "<style type='text/css'>" + styleText.join( "\n" ) + "</style>" )
				.appendTo( "html > head" )
			;
		}
	
		// Create a collection of settings to be used with this set of photo tagging elements.
		var settings = $.extend(
			{},
			$.fn.photoTagger.defaultOptions,
			options
		);
	
		// Loop over each container element and create a phototagger service instance for it.
		collection.each(function( index, node ){
			var container = $( this );
			var photoTagger = new PhotoTagger( 
				container, 
				settings 
			);
			container.data( "photoTagger", photoTagger );			
		});
		return( collection );
	};
	
	/*
	* Execute the given method on elements with an existing PhotoTagger plugin association.
	*/
	var applyPhotoTaggerMethod = function( collection, methodName ){
		
		// Loop over each element in the collection so that we
		// can get at the PhotoTagger instance.
		collection.each(function( index, node ){
				var container = $( this );
				var photoTagger = container.data( "photoTagger" );
						
				//Make sure phototagger exists and method is valid before executing
				//method
				if (photoTagger && 	(methodName in photoTagger)){
					photoTagger[ methodName ]();
				}			
		});
		return( collection );
	};
	
	/* 
	* Define the jQuery plugin for photo tagging. This is meant
	* to be called on photo container (NOT the photo).
	* 
	* NOTE: This function can take more than one type of method
	* signature:
	*
	* - Options: Sets up plugin for the first time.
	* - MethodName: Calls method on existing plugin.
	*/
	$.fn.photoTagger = function(){
		// Check to see what kind of plugin application we are going to perform
		if (typeof( arguments[ 0 ] ) == "string"){
			// We're invoking a method on elements with an existing phototagger instance 
			return(
				applyPhotoTaggerMethod( this, arguments[ 0 ] )
			);
		} else {
			// We're applying the plugin for the first time.
			return(
				applyPhotoTagger( this, arguments[ 0 ] )
			);
		}
	};
	
	/*
	* Default values for global variables. Can be defined when initializing
	*	the phototagger.
	*/
	$.fn.photoTagger.defaultOptions = {
		isDelayedLoad: false,						//load tag data immediately or on mouseover
		isTagCreationEnabled: true,			//can tags be created
		isTagDeletionEnabled: false,		//can tags be deleted
		
		resumepage: "0",								//id of the currently displayed resume page
		username: "",										//username of the owner of current resume	
    
		loadURL: "",										//url of file used to load tags
		saveURL: "",										//url of file used to save tags
		saveMethod: "get",							//method used to pass tag info to be saved to db
		deleteURL: "",									//url of file used to deletes tags
		deleteMethod: "get",						//method used to pass tag info to be deleted from db
		minOpacity: .25,								//min-opacity used by tags
		applyCSS: true,									//auto-apply the CSS or not
		cssNameSpace: "photo-tagger-",	//prefix used when creating CSS rules
	
		//tag types
		TXT_TAG:  1,	//text tag
		IMG_TAG: 2,	//image tag
		LVID_TAG: 3,	//linked video tag
		UVID_TAG: 4,	//uploaded video tag
		SND_TAG: 5,  //sound tag
		DOC_TAG: 6,	//document tag
		
		/*
		* Phototagger CSS
		*/
		css: {
			"a.tag": {
				"border": "1px solid gray",
				"border-style": "outset",
				"display": "block",
				"height": "1px",
				"position": "absolute",
				"width": "1px",
				"z-index": "10",
				"zoom": "1"
			},

			"#tagbg":{
				"position": "absolute",
				"left": "0",
				"top": "0",
				"z-index": "11",
				"height":"100%",
				"border-radius": "7px",
				"text-align": "center"

			},

			"#tagbg:hover":{
				"opacity": "0.7"
			},

			"a.selected-tag": {
				"z-index": "11"
			}
		}
	};

})( jQuery( window ), jQuery );
