/******************************************************************************
*	jquery-home-resnews.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for the "Resme News" tab in home.php
*
******************************************************************************/

$(function(){
});

function displayResnews(){
//load resme news
resetTabContents();
//fetch user's notifications and display them
$.ajax({
  url: "/backend/home-backend.php?act=global",
  dataType: "json",
  success: function(data){
    html = "";
    if(data.code > 0){
      html = "<div class='headerText' style='padding:10px;'>Hey "+ data.name +"! We have some important news for you!</div>";
      for(i = data.global.length-1; i >= 0; i--)
        html += "<div class='normalText' style='border-bottom:1px solid LightGray;padding:10px;'>"+data.global[i].date+"<br><br>"+data.global[i].message+"</div>";
    }
    $("#tabContents").html($("#tabContents").html() + html);
    setTimeout("resize(true)", 10);
  },
  error: function(a, b, data){
    console.log("error" + data);
  }
}); 
}