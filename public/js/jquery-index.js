/******************************************************************************
*	jquery-index.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of index.php
*
******************************************************************************/

$(function(){
  centerPage();
  placeCopyright($(document));
  $(window).resize(centerPage);
  
  $( "button" ).button();
  $("#login").hide();
  $("#register").hide();
  
  $("#blogin").click(function(){
    $("#buttons").hide("blind");
    $("#login").show("blind");
  });

  //display register field
  $("#bregister").click(function(){
    $("#buttons").hide("blind");
    $("#register").show("blind",{}, 1500);
    animateCopyright();
  });  
  
  //create account
  $("#doregister").click(function(){
    var data = $("#registerForm").serialize();
    $.ajax({
      url: "/backend/register-backend.php?act=validate",
      data: data,
      dataType: "json",
      type: "post",
      success: function(data){
        if(data.code == 0){
					alert(data.status); //FIX ME
          //there was an error, so redirect user to full registration page
          window.location = "/register/";
        }
        else if(data.code == 1){
          //everything checked out so create accounts
          window.location = "/backend/create-account.php?act=create";
        }
      },
      error: function(a, b, data){
        alert("error: " + data);
      }
    });
  });  
  
  
  $("#docancellogin").click(function(){
    $("#login").hide("blind");
    $("#buttons").show("blind");
  });
 
	$("#dologin").click(function(){
		var data = $("#loginForm").serialize();
		$.ajax({
			url: "/backend/login-backend.php?act=welcome",
			data: data,
			type: "post",
			dataType: "json",
			success: function(data){
				if(data.code == 1)
					window.location = "/home/";
				else
					window.location = "/login.php?act=fail";
			},
			error: function(obj, b, data){
				alert("error: " + data);
			}
		});
	});

  $("#docancelregister").click(function(){
    $("#register").hide("blind", {}, 1000);
    $("#buttons").show("blind");
    animateCopyright();
  });
});

function animateCopyright(){
  var ref_elem;
  var offset = 0;
  if($(window).height() < $(document).height()){
    ref_elem = $(window);
    offset = $("#copyright").height();
  }
  else{
    ref_elem = $(document);
    offset = -110;
  }
  
  $("#copyright").animate({
    "top": ref_elem.height() - offset
  }, 1500);
}

function placeCopyright(){
  var ref_elem;
  var offset = 0;
  if($(window).height() < $(document).height())
    ref_elem = $(window);
  else
    ref_elem = $(document);

  $("#copyright").css({
    "top": ref_elem.height() - ($("#copyright").height() + 5)
  });
}

function centerPage(){
  var width = $(window).width();
  var height = $(window).height();
  
  //500x196 are the dimensions of the logo
  var _left = (width - 500)/2;
  var _top = (height - 196)/2;
  
  $("#logo").css({
    "top": _top,
    "left": _left
  });
  placeCopyright();
}
