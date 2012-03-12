/******************************************************************************
*	jquery-register.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of register.php
*
******************************************************************************/
$(function(){
  $( "button" ).button();
  //dynamically set height
  resize(false);
    
  $("#submit").click(function(){  
    var data = $("#registerForm").serialize();
    $.ajax({
      url: "/backend/register-backend.php?act=validate",
      data: data,
      dataType: "json",
      type: "post",
      //json will only be returned if an error occured with registration
      success: function(data){
        if(data.code == 0){
          var color = "red";
          $("#output").css({
            "position": "relative",
            "margin-bottom": "7px",
            "padding": "5px",
            "border": "solid 1px " + color
          });
          $("#output").html("<span class='normalText' style='color:"+ color +";'>"+data.status+"</span>");
          resize(true);
          $("#loading").hide();  
        }
        else if(data.code == 1){
          //everything checked out so create accounts
          window.location = data.status;
        }
      },
      error: function(a, b, data){
        alert("error: " + data);
      }
    });
  });
    
});

function resize(animate){
  var height = $("#registerForm").height();
  if(animate)
	  $("#registerBody").animate({
  	  "height": (height + 15)
		});
  else
    $("#registerBody").css({
      "height": (height + 15) 
    });
}






