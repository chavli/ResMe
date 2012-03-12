/******************************************************************************
*	jquery-comments.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for comments.php
*
******************************************************************************/

$(function(){
  var user = $("body").attr("id");
  $("button").button();  
  //fetch initial data to display
  $.ajax({
    url: "/backend/comment-backend.php?act=show&uname="+user,
    dataType: "json",
    success: function(data){  //display returned json
      if(data.code == 1){
				$("#fullname").html(data.firstname + " " + data.lastname + "'s Resume Comments");
				$("#fullname").attr("class", "pageTitle shadowed");
        //display resume pages
        $("#resPages").css({
          "position":"relative",
          "left":"10px",
          "height":"250px",
          "text-align":"center"
        });
        var html = "";
        for(i = 0; i < data.pages.length; i++){
					var thumbnail = $(document.createElement("div")).thumbcontainer(data.pages[i].src);
					thumbnail = thumbnail.data("thumbcontainer");
					thumbnail.setImgId(data.pages[i].id);
       	 	$("#resPages").append(thumbnail.prepare());
        }
        $("#resDisplay").html("<img id='resPicture' src='"+ data.pages[0].src +"' style='width:845px;'/>");
        
        initThumbListeners();
        //end display pages
        
        //display comments and input box
        html = "";
        if(data.readonly == 0){
          html += 
            "<span class='headerText'>Suggestion Box:</span>"+
            "<form enctype='multipart/form-data' id='commentForm'>"+
              "<div id='commentbox' align='right'>"+
             	  "<textarea class='normalText' style='width:100%;height:80px;resize:none;' rows=5 name='comment' id='comment'></textarea>"+
                "<button type='button' class='normalText' id='submit'>Leave Tip</button>"+
              "</div>"+
              "</form>";
        }
        html += "<span class='headerText'>Feedback:</span>";
        $("#commentInput").html(html);
        $("button").button();  
        html = "";
        for(i = 0; i < data.comments.length; i++){
					var comment = $(document.createElement('div')).commentbox();
					comment = comment.data("commentbox");
					comment.setTitle(data.comments[i].source + " @ " + data.comments[i].time);
					comment.setBody(data.comments[i].message);
					$("#comments").append(comment.prepare());
        }
        initCommentListeners();
        //end display comments
      }
      else{
        $("#resDisplay").html("<span class='headerText'>The owner of this resume has set their comments page to private.</span>");
      }
      
    },
    error: function(a, b, data){
      alert("error: " + data);
    }
  });
});

function initThumbListeners(){
  $("#resPages img").click(function(){
    document.getElementById("resPicture").src = $(this).attr("src");
  }); 
}

function initCommentListeners(){
  $("#comments").show("slide", {"direction":"up"}, 1000);	//jquery ui
  
  $("#submit").click(function(){
    var data = $("#commentForm").serialize();
    var user = $("body").attr("id");
    //only post a comment if there is a comment
    if($("#comment").val().length > 0){
      $.ajax({
        url: "/backend/comment-backend.php?act=post&uname="+user,
        data: data,
        dataType: "json",
        type: "post",
        success: function(data){
          //successful comment!
          if(data.code == 1){
					var comment = $(document.createElement('div')).commentbox();
					comment = comment.data("commentbox");
					comment.setTitle(data.from + " @ " + data.date);
					comment.setBody(data.comment);
					$("#comments").prepend(comment.prepare());
          $("#comment").val("");
          }
          //error :(
          else if(data.code == 0){
            //TO DO: display error here
          }
        },
        error: function(a, b, data){
          alert("error: " + data);
        }
      });
    }
  });
}
