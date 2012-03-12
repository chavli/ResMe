/******************************************************************************
*	jquery-home-resumes.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for the "Explore Resumes" tab in home.php
*
******************************************************************************/

$(function(){

});

function displayResumes(){
  resetTabContents();
	//create the containers for each category of resume
	html = "<span class='headerText'>Popular Resumes</span><div id='popular'></div>" + 
					"<span class='headerText'>New Resumes</span><div id='new'></div>";
					//"<span class='headerText'>Related Resumes</span><div id='related'></div>";
	$("#tabContents").css("padding", "10px");
	$("#tabContents").html(html);
	
	//center contents of divs
	$("#popular").css("text-align", "center");
	$("#new").css("text-align", "center");
	$("#related").css("text-align", "center");

	//ajax request to get popular resumes
	$.ajax({
		url: "/backend/home-backend.php?act=explore",
		dataType: "json",
		success: function(json){
			html = "";
			if(json.code == 1){

				//display popular resumes
				for(i = 0; i < json.popular.length; i++){
					resume = json.popular[i];
					url = "/" + resume.username;
					src = "/users/" + resume.username + "/albums/" + resume.album_id + "/0.jpg";
					
					var thumbnail = $(document.createElement("div")).thumbcontainer(src);
					thumbnail = thumbnail.data("thumbcontainer");
					thumbnail.setImgLink(url);
					thumbnail.setUrlElem(resume.firstname + " " + resume.lastname, url);
					thumbnail.setTextElems("Title: " + resume.title, "Approvals: " + resume.upvotes);
					$("#popular").append(thumbnail.prepare());
				}
				
				//display newest resumes
				html = "";
				for(i = 0; i < json.newest.length; i++){
					resume = json.newest[i];
					url = "/" + resume.username;
					src = "/users/" + resume.username + "/albums/" + resume.album_id + "/0.jpg";
					
					var thumbnail = $(document.createElement("div")).thumbcontainer(src);
					thumbnail = thumbnail.data("thumbcontainer");
					thumbnail.setImgLink(url);
					thumbnail.setUrlElem(resume.firstname + " " + resume.lastname, url);
					thumbnail.setTextElems("Title: " + resume.title, "");
					$("#new").append(thumbnail.prepare());
				}
				setTimeout("resize(true)", 10);
			}
		},
		error: function(xhr, error_status, error_text){
			console.log("jquery-home-resumes:displayResumes " + error_text);
		}
	});

}
