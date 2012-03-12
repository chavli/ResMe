/******************************************************************************
*	jquery-profile.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of profile.php
*
******************************************************************************/
//TODO: need a resize(animate) function
$(function(){
  $( "button" ).button();

	//used to toggle the stack button
	stack_toggle = 0;

	//resize event listener that keeps the tagger/viewer modal windows centered when
	//browser window is resized.
	$(window).resize(function(){
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

		$("#tagger").css({
      "left": _left,
      "top": _top 
    }); 

		//style the error modalwindow values defined in profile.css
    _left = (width - 450) / 2;
		_top = (height - 25) / 2;
		
		$("#error").css({
			"left": _left,
			"top": _top
		});
	});
  
  //ajax request for data to display
  var uname = $("body").attr("id");
  $.ajax({
    url: "/backend/profile-backend.php?uname="+uname+"&act=show",
    dataType: "json",
    success: function(data){
			console.log(data);
      //display returned profile data  
      if(data.code > 0){
        //display profile owner's name
        $("#profileHeader").html("<span class='pageTitle shadowed' style='position:absolute; left: 10px; top: 10px;'>"+data.name+"</span>");
 
        //display first page of resume
        $("#rightContainer").html("<img id='resumePicture' src='"+data.pages[0].src+"' style='width:970px;'/>");
        
        //display contact info
        html = "";
        html +=  "<label class='headerText' for='email'>E-Mail:</label><br><span class='normalText' id='email' style='width:130px;'>"+data.email+"</span><br>";
        if(data.mainphone.length > 0)
          html += "<label class='headerText' for='mphone'>Primary Number:</label><br><span class='normalText' id='mphone' style='width:130px;'>"+data.mainphone+"</span><br>";
        if(data.cellphone.length > 0)
          html += "<label class='headerText' for='cphone'>Mobile Number:</label><br><span class='normalText' id='cphone' style='width:130px;'>"+data.cellphone+"</span><br>";
        if(data.officephone.length > 0)
          html += "<label class='headerText' for='ophone'>Office Number:</label><br><span class='normalText' id='ophone' style='width:130px;'>"+data.officephone+"</span><br>";
        
        $("#contactInfo").html(html);
        
        //display resume pages
        html = "";
        for(var i = 0; i <data.pages.length; i++){
					var thumbnail = $(document.createElement("div")).thumbcontainer(data.pages[i].src);
					thumbnail = thumbnail.data("thumbcontainer");
					thumbnail.setImgId(data.pages[i].id);
					thumbnail.setTextElems("Page " + (i + 1), "");
        	$("#resPages").append(thumbnail.prepare());
        }

        if(data.pdfpath){
					var pdflink = $(document.createElement("div")).urlelement();
					pdflink = pdflink.data("urlelement");
					pdflink.setUrl("/"+data.pdfpath);
					pdflink.setText("Download Resume PDF");
        	$("#resPages").append(pdflink.prepare());
				}
        
        //set up page menu and tagger/viewer
        html = "<div id='resume_opts' style='position:absolute;right:0.5em;top:0.5em;'>";
        if(data.isOwner == 1){
          html += "<a href='/"+data.owner+"/comments/' style='text-decoration:none;'><button type='button' class='normalText' style='position:relative;'>+ New Comment</button></a>";
          //$("#taggerWrapper").html(taggerHTML()); TODO this doesnt work yet
          initPhotoTagger(data.resumepage, data.owner);
        }
        else if(data.comments == 1){
          html += "<a href='/"+data.owner+"/comments/' style='text-decoration:none;'><button type='button' class='normalText' style='position:relative;'>+ New Comment</button></a>";
          $("#taggerWrapper").html(viewerHTML());
          initPhotoViewer(data.resumepage, data.owner);
        }
        else{
          $("#taggerWrapper").html(viewerHTML());
          initPhotoViewer(""+data.resumepage, ""+data.owner);
        }

				//create the approve button
				if(data.approved != null && data.isOwner != 1){
					label = (data.approved == 1) ? "Unapprove Resume" : "Approve Resume";	
          html += "<button type='button' class='normalText' id='judge' value='"+data.resume_id+"'style='position:relative;'>"+label+"</button>";
				}

				//create the stack button
				if(data.stackable != null && data.isOwner != 1){
					stack_toggle = data.stackable;
					label = (data.stackable == 1) ? "Add to Stack" : "Remove from Stack";
          html += "<button type='button' class='normalText' id='stack' value='"+data.id+"'style='position:relative;'>"+label+"</button>";
				}
        html += "</div>";

				html = $("#profileHeader").html() + html;
        $("#profileHeader").html(html);
        $("button").button();
				
				//listener for the stack button
				$("#stack").click(function(){
					data = new Object;
					data.id = $(this).attr("value");
					data.owner = $("body").attr("id");
					act = (stack_toggle == 1) ? "addstack" : "delstack";
					$.ajax({
						url:"/backend/profile-backend.php?act="+act,
						dataType: "json",
						data: data,
						success: function(json){
							stack_toggle = (stack_toggle == 1) ? 0 : 1
							new_label = (stack_toggle == 1) ? "Add to Stack" : "Remove from Stack";
							$("#stack").button("option", "label", new_label);
						},
						error: function(errorCode, errorText, data){
							console.log(data);
						}
					});
				});

				//listener for the approve button
				$("#judge").click(function(){
					data = new Object;
					data.id = $(this).attr("value");
					data.owner = $("body").attr("id");
					$.ajax({
						url:"/backend/profile-backend.php?act=judge",
						dataType: "json",
						data: data,
						success: function(json){
							new_label = (json.approved == 1) ? "Unapprove Resume" : "Approve Resume";
							$("#judge").button("option", "label", new_label);
						},
						error: function(errorCode, errorText, data){
							console.log(data);
						}
					});
				});
				
      }
      else{ //private profile
        $("#rightContainer").html("<span class='headerText'>"+data.status+"</span>");
      }
      $("#profilePicture").attr("src", data.picture);
    },
    error: function(a, b, data){
      alert("error: " + data);
    }
  });  
  //end ajax request

});
/**** END MAIN ****/

var oldurl = "";
function initPhotoTagger(resumePage, username){
  $(function(){
   	$("#vidOption1").show();
		$("#vidOption2").hide();

		$("#urlInput").keyup(function(){
      var url = document.getElementById("urlInput").value;
      if((id = isValidYoutube(url)) && url != oldurl){
        oldurl = url;
        document.getElementById("vidPreview").innerHTML = 
          "<object width='378' height='243'>" +
            "<param name='movie' value='http://www.youtube.com/v/"+ id +"'/>" +
            "<param name='wmode' value='transparent' />" +
            "<embed src='http://www.youtube.com/v/"+ id +"' type='application/x-shockwave-flash'" +
              " width='378' height='243' style='padding:10px;'/>" +
          "</object>";
        //http://www.youtube.com/v/
      }
    });    

    $( "div.resumediv" ).photoTagger({
	
      // The API urls.
      loadURL: "/backend/tagger-backend.php?act=load",
      saveURL: "/backend/tagger-backend.php?act=save",
      deleteURL: "/backend/tagger-backend.php?act=delete",
      resumepage: resumePage,
      username: username,
      isTagDeletionEnabled: true
    });
    $(".tabContents").hide(); //Hide all content
   
    //On Click Event
    $("ul.tabs li").click(function() {
      resetTagger();
      $("ul.tabs li").removeClass("active"); //Remove any "active" class
      $(this).addClass("active"); //Add "active" class to selected tab
      $(".tabContents").hide(); //Hide all tab content
      var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
      $(activeTab).fadeIn(); //Fade in the active ID content
      return false;
    });

    $("#closeTagger").click( function(){
       oldurl = "";
       unbindTagger();
       closeTagger();
       resetTagger();
    });

    //cancel tag event listeners if user changes resume pages
	  $(".resPages img").click(function(){
	  	unbindTagger();
			resetTagger();
		});

		$("input:radio").click(function(){
			var opt = parseInt($(this).attr("value"));
			switch(opt){
				case 0:	//video upload radio
					$("#vidOption1").show("blind");
					$("#vidOption2").hide("blind");
				break;
				case 1:	//video url radio
					$("#vidOption1").hide("blind");
					$("#vidOption2").show("blind");
				break;
			}
		});

		//onClick event for resume pages
		$(".resPages img").click(function() {
      $("#resumePicture").attr("src", $(this).attr("src"));
      $("#resumePicture").hide().show("fade");
      $('html, body').animate({scrollTop:0}, 'fast');
			resetTagger();
			//unbind the listeners for previous page's tags
			$( "div.resumediv" ).unbind();

			//set up a new phototagger for new page
	 		$( "div.resumediv" ).photoTagger({
	    	// The API urls.
        loadURL: "/backend/tagger-backend.php?act=load",
        saveURL: "/backend/tagger-backend.php?act=save",
        deleteURL: "/backend/tagger-backend.php?act=delete",
   	 		resumepage: parseInt($(this).attr("id")) ,
        username: username
    	});
		});
 		$("#closeViewer").click( function(){
    	$("#viewer").fadeOut("fast");
     	$("#background").fadeOut();
     	$("#deleteTag").unbind();
   	});
  });
}


function initPhotoViewer(resumePage, username){

	$(function(){
 		$("div.resumediv").photoTagger({
	 		// The API urls.
	 		loadURL: "/backend/tagger-backend.php?act=load",
	 		isTagCreationEnabled: false,
	 		resumepage: resumePage,
      username: username
	 	});

		$(".resPages img").click(function() {
      $("#resumePicture").attr("src", $(this).attr("src"));
      $("#resumePicture").hide().show("fade");
      $('html, body').animate({scrollTop:0}, 'fast');
			//unbind the listeners for previous page's tags
			$( "div.resumediv" ).unbind();
			//set up a new phototagger for new page
 			$( "div.resumediv" ).photoTagger({
    		// The API urls.
        loadURL: "/backend/tagger-backend.php?act=load",
				isTagCreationEnabled: false,
  	 		resumepage: parseInt($(this).attr("id")),
        username: username
   		});
		});

 		$("#closeViewer").click( function(){
    	$("#viewer").fadeOut("fast");
     	$("#background").fadeOut();
   	});
	});
}

//unbind all listeners from this tag
function unbindTagger(){
	$("#textSubmit").unbind();
	$("#picForm").unbind();
	$("#urlSubmit").unbind();
	$("#vidForm").unbind();
}

//reset all fields in the tagger
function resetTagger(){
	$("ul.tabs li").removeClass("active");
	$('input:file').MultiFile('reset');
	$('input:file').attr("id", "userfile");
	$("#picForm").reset();
	$("#vidForm").reset();
	document.getElementById("textInput").value = "";
	document.getElementById("urlInput").value = "http://";
	document.getElementById("vidPreview").innerHTML = "";
	$('#upload_frame').hide();
	$('#upload_frame').attr('src','');
	$("#vidOption1").show();
	$("#vidOption2").hide();
}

//close the tagger 
function closeTagger(){
	$("#tagger").fadeOut("fast");
  $("#viewer").fadeOut("fast");
	$("#background").fadeOut("fast");

}

function isValidYoutube(url){
  var base = "http://www.youtube.com/watch";
  var retval = false;
  if(url){
    //check if url is correct
    if(url.indexOf(base) == 0){
      //parse out the value 'v' is set to
      url = url.replace(base, "");																					//?v=LuJmEfO2yNI&feature=featured
      var end = (url.indexOf("&") != -1) ? url.indexOf("&") : url.length;
      url = url.slice(1, end);																						//v=LuJmEfO2yNI
      url = url.replace("v=", "");																				//LuJmEfO2yNI
      retval = url;
    }
    else
      retval = false;
  }
  return retval;
}

//i seperated this so it would be easier to work with
function taggerHTML(upload_id){
  return ''+
    '<div id="tagger" class="modalwindow">'+
      '<ul class="tabs">'+
        '<li><a href="#tab1">Text</a></li>'+
        '<li><a href="#tab2">Pictures</a></li>'+
        '<li><a href="#tab3">Video</a></li>'+
        '<li><a href="#tab4">Audio</a></li>'+
        '<li><a href="#tab5">Documents</a></li>'+
      '</ul>'+
      '<div class="tabContainers">'+
        '<div id="tab1" class="tabContents">'+
          '<span class="headerText">Enter Text:</span>'+
          '<textarea class="normalText" id="textInput" style="height:320px; width:100%;resize:none;"></textarea>'+
          '<button class="normalText" id="textSubmit" style="position:relative;width:80px;left:480px;" type="button">Submit</button>'+
        '</div>'+
        '<div id="tab2" class="tabContents">'+
          '<form name="picForm" id="picForm" action="" method="POST" enctype="multipart/form-data">'+
            '<span class="normalText">Select images to upload from your computer.</span> <br>'+
            '<span class="normalText">Allowed image types: .jpg, .png, .bmp, .gif, .tiff</span> <br><br>'+
            '<input id="userfile" type="file" class="multi normalText" maxlength="100" name="userfile[]"/>'+
            '<button class="normalText" style="position:relative;width:80px;left:480px;"type="submit">Submit</button>'+
          '</form>'+
        '</div>'+
        '<div id="tab3" class="tabContents">'+
          '<form action="" method="post" enctype="multipart/form-data" name="vidForm" id="vidForm">'+
            '<input id="vidType" name="vidType" type="radio" value="0" checked/><span class="normalText">Upload Video (50MB limit)</span><br>'+
            '<div id="vidOption1" style="margin-left:20px;">'+
              '<span class="normalText">Select a video to upload from your computer.</span> <br>'+
              '<span class="normalText">Allowed video types: .mpeg, .mp4, .flv</span> <br><br>'+
              '<input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="'+upload_id+'"/>'+
              '<input class="normalText" name="vidFile" type="file" id="vidFile"/>'+
              '<br />'+
              '<iframe id="upload_frame" name="upload_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>'+
              '<br />'+
              '<button id="vidSubmit" style="position:relative;width:80px;left:460px;"type="submit" class="normalText">Submit</button>'+
            '</div>'+
            '<input id="vidType" name="vidType" type="radio" value="1"/><span class="normalText">YouTube Link</span><br>'+
            '<div id="vidOption2" style="margin-left:20px;">'+
              '<input type="text" id="urlInput" name="urlInput" value="http://" class="normalText" style="width:540px;left:0px;"/><br>'+
              '<div id="vidPreview" style="text-align:center;"></div>'+
              '<button id="urlSubmit" type="button" style="position:relative;width:80px;left:460px;" class="normalText">Submit</button>'+
            '</div>'+
          '</form>'+
        '</div>'+
        '<div id="tab4" class="tabContents">Tab 4</div>'+
        '<div id="tab5" class="tabContents">Tab 5</div>'+
      '</div>'+
      '<button class="normalText" id="closeTagger" type="button">Close</button>'+
    '</div>'+
    '<div id="viewer" class="modalwindow">'+
      '<div id="tagData"></div>'+
      '<button class="normalText" id="deleteTag" type="button">Delete Tag</button>'+
      '<button class="normalText" id="closeViewer" type="button">Close</button>'+
    '</div>'+
    '<div id="background"></div>	'+
    '<div id="error" class="modalwindow"></div>';
}
function viewerHTML(){
  return ''+
    '<div id="viewer" class="modalwindow">'+
      '<div id="tagData"></div>'+
      '<input id="closeViewer" type="button" value="Close"/>'+
    '</div>'+
    '<div id="background"></div>';
}
