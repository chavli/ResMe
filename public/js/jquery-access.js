/******************************************************************************
*	jquery-access.js
*
*	handles the UI functionality and communication with backend php files for
*	access.php
*
******************************************************************************/

$(function(){
  $("button").button();
  $("#loading").hide();
  $("#publicDef").hide();
  $("#resmeDef").hide();
  
	//fetch intial privacy data
  $.ajax({
    url: "/backend/access-backend.php?act=show",
    dataType: "json",
    success: function(data){
      var color = (data.code == 1) ? "green" : "red";
      $("#loading").hide();

      //display privacy settings
      $("#publicperms").attr("title", data.public.page_access);
      $("#resmeperms").attr("title", data.resme.page_access); 
      
			displayPermText($("#public"), data.public.page_access)
			$("#val1").val(data.public.page_access);
			displayPermText($("#resme"), data.resme.page_access)
			$("#val2").val(data.resme.page_access);               

			//toggle all the options as needed	
      if(data.searchable)
        $("#resmeyes").attr("checked", "checked");
      else
        $("#resmeno").attr("checked", "checked");

      if(data.indexed)
        $("#publicyes").attr("checked", "checked");
      else
        $("#publicno").attr("checked", "checked");
     	
			//toggle options for public users
			if(!data.public.showtags)
				$("#publichidden").attr("checked", "checked");
			if(data.public.downloadable)
				$("#publicdownload").attr("checked", "checked");
			if(data.public.stackable)
				$("#publicstack").attr("checked", "checked");
			if(!data.public.writable)
				$("#publiclocked").attr("checked", "checked");

			//toggle options for public users
			if(!data.resme.showtags)
				$("#resmehidden").attr("checked", "checked");
			if(data.resme.downloadable)
				$("#resmedownload").attr("checked", "checked");
			if(data.resme.stackable)
				$("#resmestack").attr("checked", "checked");
			if(!data.resme.writable)
				$("#resmelocked").attr("checked", "checked");
     
			//draw the sliders
			initSliders();
			resize(true, 0);
    },
    error: function(a, b, data){
      alert("error: " + data);
    }
  }); 
  
  //submit the form
  $("#submit").click(function(){
    $("#loading").show();
    resize(true, 0);        
    var data = $("#privacyForm").serialize();
    $.ajax({
      url: "/backend/access-backend.php?act=update",
      data: data,
      dataType: "json",
      type: "post",
      success: function(data){
        var color = (data.code == 1) ? "green" : "red";
        $("#output").css({
            "position": "relative",
            "margin-bottom": "7px",
            "padding": "5px",
            "border": "solid 1px " + color
          });
        $("#output").html("<span class='normalText' style='color:"+ color +";'>"+data.status+"</span>");
        $("#loading").hide();
        resize(true, 0);        
      },
      error: function(a, b, data){
        alert("error: " + data);
      }
    });
  });

	//event listeners
	$("#publicperms").hover(function(){ $("#publicDef").fadeIn(); }, function(){ $("#publicDef").fadeOut(); });
	$("#resmeperms").hover(function(){ $("#resmeDef").fadeIn(); }, function(){ $("#resmeDef").fadeOut(); });
});

function initSliders(){
	//initialize the slider for public access permisions
	$("#publicperms").each(function(){
		$(this).slider({
			range: "min",
			value: this.title,
			min: 0,
			max: 30,
			step: 10,
			animate: true,
			slide: function(event, ui){
				var offset =  displayPermText($("#public"), ui.value);
				resize(true, offset);
				$("#val1").val(ui.value);
			},
			change: function(event, ui){
				var offset = displayPermText($("#public"), ui.value);
				resize(true, offset);
				$("#val1").val(ui.value);
			}
		});

		//initialize the output
		var value = parseInt($("#publicperms").slider("option", "value"));
		$("#val1").val(value);
	});

	//initialize the slider for resme access permissions
	$("#resmeperms").each(function(){
		$(this).slider({
			range: "min",
			value: this.title,
			min: 0,
			max: 30,
			step: 10,
			animate: true,
			slide: function(event, ui){
				var offset = displayPermText($("#resme"), ui.value);
				resize(true, offset);
				$("#val2").val(ui.value);
			},
			change: function(event, ui){
				var offset = displayPermText($("#resme"), ui.value);
				resize(true, offset);
				$("#val2").val(ui.value);
			}
		});

		//initialize the output
		var value = parseInt($("#resmeperms").slider("option", "value"));
		$("#val2").val(value);
	});
}

/*
*Converts the numeric value of a slider to its corresponding permissions
*/
function displayPermText(elem, val){
	var diff = 0;
	var id = elem.attr("id");

	switch(val){
		case 0:
			diff = elem.height() - 40;
			elem.html("No Access<br>Users will be unable to see your profile page and your feedback page.");
			elem.animate({height: "40px"});
			break;
		case 10:
			diff = elem.height() - 130;
			elem.html(""+
				"Public Profile Only<br>Users will be able to view your profile page and the tags within your resume.<br>Additional Settings:<br>"+
				"<input id='" + id + "download' name='"+ id +"download' type='checkbox'>Downloadable</input><br>"+
				"<input id='" + id + "stack' name='"+ id +"stack' type='checkbox'>Stackable</input><br>"+
				"<input id='" + id + "hidden' name='"+ id +"hidden' type='checkbox'>Hide Tags</input><br>"+
				//"<label for='pages'>Visible Pages:</label><input type='text' id='pages' disabled/>"+
			"");
			elem.animate({height: "130px"});
			break;
		case 20:
			diff = elem.height() - 170;
			elem.html(""+
				"Public Profile and Feedback<br>Users will be able to view your profile page and the tags within your resume. Users will also be able"+
				" to access your feedback page.<br>Additional Settings:<br>"+
				"<input id='" + id + "download' name='"+ id +"download' type='checkbox'>Downloadable</input><br>"+
				"<input id='" + id + "stack' name='"+ id +"stack' type='checkbox'>Stackable</input><br>"+
				"<input id='" + id + "hidden' name='"+ id +"hidden' type='checkbox'>Hide Tags</input><br>"+
				//"<label for='pages'>Visible Pages:</label><input type='text' id='pages' disabled/><br>"+
				"<input id='" + id + "locked' name='"+ id +"locked' type='checkbox'>Lock Comments</input><br>"+
			"");
			elem.animate({height: "170px"});
			break;
		case 30:
			diff = elem.height() - 80;
			elem.html(""+
				"All Access<br>Users will be able to view your profile page, your entire resume, the tags within your resume, and download your resume."+
				" Users will also be able to access your feedback page and leave comments."+
			"");
			elem.animate({height: "80px"});
			break;
	}
	return diff;
}

function resize(animate, offset){
  var height = $("#privacyForm").height();
  if(animate)
    $("#accessBody").animate({
      "height": (height + 20 - offset)
    });
  else
    $("#accessBody").css({
      "height": (height + 20 - offset)
    });
 
	$("#pageFooter").css("top", $(document).height());
}
