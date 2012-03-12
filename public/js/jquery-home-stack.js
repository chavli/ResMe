/******************************************************************************
*	jquery-home-stack.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for the "Your Stack" tab in home.php
*
******************************************************************************/

$(function(){
});

function displayStack(){
  resetTabContents();
  $.ajax({
    url: "/backend/home-backend.php?act=stack",
    dataType: "json",
    success: function(json){
      if(json.code == 1){
        $("#tabContents").css("text-align", "center");
        html = "";
        for(i = 0; i < json.stack.length; i++){
          item = json.stack[i];	
          url = "/" + item.username; 
          src = "/users/" + item.username + "/albums/" + item.resume_album + "/0.jpg";

					var thumbnail = $(document.createElement("div")).thumbcontainer(src);
					thumbnail = thumbnail.data("thumbcontainer");
					thumbnail.setImgLink(url);
					thumbnail.setUrlElem(item.firstname + " " + item.lastname, url);
					$("#tabContents").append(thumbnail.prepare());
        }
        setTimeout("resize(true)", 10);
      }
    },
    error: function(xhr, error_status, error_text){
      console.log("jquery-home:254 " + error_text);
    },
  });
}
