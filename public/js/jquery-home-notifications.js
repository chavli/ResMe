/******************************************************************************
*	jquery-home-notifications.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for the "Notifications" tab in home.php
*
******************************************************************************/

//notification types
var COMMENT_NOTE = 1; //comment on resume
var STACK_NOTE = 2;		  //add resume to stack
var APPRES_NOTE = 3;	  //approve of resume
var C_NOTE = 4;
var D_NOTE = 5;

$(function(){

	/*
	*	fetch notifications and display number on tab
	*/
  $.ajax({
    url: "/backend/home-backend.php?act=show",
    dataType: "json",
    success: function(json){
			if(json.code == 1){
        notification_data = json.notifications;
				$("#quantity").html(notification_data.length);
      }
			else
				$("#quantity").html(0);
    },
    error: function(a, b, data){
      console.log("error" + data);
    }
  });   
});

/*
* performs an ajax request to get the latest notification data for a user and
* wraps the json data in html
*/
function displayNotifications(){
  //display user's notifications
  resetTabContents();
  uname = $("body").attr("id");
  //fetch user's notifications and display them
  $.ajax({
    url: "/backend/home-backend.php?act=show",
    dataType: "json",
    success: function(data){
			if(data.code == 1){
				html = "<div class='headerText' style='padding:10px;'>You have " + data.notifications.length + " ";
				html += (data.notifications.length == 1) ? "notification:</div>" : "notifications:</div>";
				for(i = 0; i < data.notifications.length; i++){
					note = data.notifications[i];
					html += "<div class='normalText' style='padding:10px;border-bottom:1px solid LightGray;'>";

					//TODO: consoladate similar notifications to reduce span
					switch(note.type){
						case COMMENT_NOTE:
							html += "@"+note.created+" <a href='/"+note.from+"'>"+note.from+"</a> left a <a href='/"+uname+"/comments/'>comment</a> on your resume:<br><br>\""+note.data+"\"";
						break;
						case STACK_NOTE:
							html += "@"+note.created+" <a href='/"+note.from+"'>"+note.from+"</a> added your <a href='/"+uname+"'>resume</a> to their stack.";
						break;
						case APPRES_NOTE:
							html += "@"+note.created+" <a href='/"+note.from+"'>"+note.from+"</a> approves of your <a href='/"+uname+"'>resume</a>! ";
						break;
					}
					html += "</div>";
				}
			}
      $("#tabContents").html($("#tabContents").html() + html);
      setTimeout("resize(true)", 10);
    },
    error: function(a, b, data){
      alert("error: "+data);
    }
  });    
}
