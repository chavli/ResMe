/******************************************************************************
* jquery-home.js
*
* javascript that handles the tab functionality, element resets, and other 
* general things of home.php
*
* the js files that handle the functionality of each tab can be found in the 
* named jquery-home-*.js
*
******************************************************************************/
$(function(){
	//stylize buttons
  $("button").button();
  
  //hide url submission form
  $("#submissionForm").hide();
  
  //by default, the ResMe notification tab is chosen
  selected = "resnews";
  expandTab(selected);
  displayResnews();

	/*
  *highlight tabs on mouseover
  */
	$("div.verticalTabs .tab").hover(
    function(){ //mouseover
      $(this).animate({backgroundColor: '#fff58f'});
    },
    function(){ //mouseout
      //dont fade out current selected tab
      if(selected != $(this).attr('id'))
        $(this).animate({backgroundColor: '#fff'});      
    }
  );
  
	/*
  *handle when any tab is clicked
  */
	$("div.verticalTabs .tab").click(function(){
      //shrink previous tab
      if(selected && selected != $(this).attr('id'))
        shrinkTab(selected);
      
      //expand newly selected tab
      if(selected != $(this).attr('id')){
        selected = $(this).attr('id');
        expandTab(selected);
      }
  });
  

  /*
  * onclick listeners for the tabs
  */
  $('#usernews').click(function(){
    displayUserArticles(true);
  });
  
	$('#resnews').click(function(){
    displayResnews();
  });
  
  $('#notifications').click(function(){
    displayNotifications();
  });
  
  $('#resumes').click(function(){
  	displayResumes();
	});
  
  $('#stack').click(function(){
		displayStack();
  });
});

/*
* resize tab based on whether or not it's selected
*/
function expandTab(id){
  $("#"+id).animate({backgroundColor: '#fff58f', left: '-=20', width: '+=20'});
}
function shrinkTab(id){
  $("#"+id).animate({backgroundColor: '#fff', left: '+=20', width: '-=20'});
}

/*
* resize background depending on contents of tab
*/
function resize(animate){
	var height = $("#tabContents").height() + $("#output").height();
	
	if(selected == "usernews")
		height +=  $("#submissionForm").height();

	height =  height < $("#tabs").height() ? $("#tabs").height() : height;
  if(animate)
	  $("#tabContainer").animate({
  	  "height": (height + 20)
		}, 500);
  else
    $("#tabContainer").css({
      "height":height + 20
    });
    
  setTimeout("placeCopyright()", 500);
}

/*
* reset tab settings to default
*/
function resetTabContents(){
	resetSubmissionForm();
	$("#submissionForm").hide();
	$("#tabContents").html("");
	$("#tabContents").css("text-align", "left");
	$("#tabContents").css("padding", "0px");
}

/*
* reset the form used to submit user articles
*/
function resetSubmissionForm(){
	$("#url").attr("value", "http://");
	$("#title").attr("value", "");
	$("#description").attr("value", "");
	
	charCount("title", "tcharcount");
	charCount("description", "dcharcount");
}

/*
* places the copyright at the bottom of the page
*/
function placeCopyright(){
  var ref_elem;
	var offset = 0;
	$("#copyright").hide();
	if($(window).height() < $(document).height())
    ref_elem = $(document);
  else
    ref_elem = $(window);

  $("#copyright").css({
    "top": ref_elem.height() - $("#copyright").height()
  });
  $("#copyright").show();
}
