/******************************************************************************
*	jquery-login.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of login.php
*
******************************************************************************/
$(function(){
  //dynamically set height
  setTimeout("resize(false)", 10);
    
	$("#submit").click(function(){
		var data = $("#loginForm").serialize();
		$.ajax({
			url: "/backend/login-backend.php?act=welcome",
			data: data,
			dataType: "json",
			type: "post",
			success: function(data){
				if(data.code == 1){
					window.location = "/home/";
				}
				else{
					$("#output").css({
						"position": "relative",
						"margin-bottom": "7px",
						"padding": "5px",
						"border": "solid 1px red"
					});
					$("#output").html("<span class='normalText' style='color:red;'>"+data.status+"</span>");
					resize(true);
				}
			},
			error: function(a, b, data){
				alert("error: " + data);
			}
		});


	});
  $( "button" ).button();
});

function resize(animate){
  var height = $("#loginForm").height();
	if(animate)
	  $("#loginBody").animate({
  	  "height": (height + 15)
		});
	else
	  $("#loginBody").css({
  	  "height": (height + 15)
	  });
	$("#pageFooter").css("top", $(document).height());  
}





