/******************************************************************************
*	jquery-pictures.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of pictures.php
*
******************************************************************************/
$(function(){
  $("#output").hide();
  //align the footer
  $("#loading").hide();
  //$("#upload_frame").hide();
	$("button").button();
  reposition();
  
  //resize event listener that keeps the tagger/viewer modal windows centered when
	//browser window is resized.
	$(window).resize(reposition);
  
    //load all the pictures to display
  $.ajax({
    url: "/backend/pictures-backend.php?act=show",
    dataType: "json",
    success: function(data){
      var color = (data.code == 1) ? "green" : "red";
      $("#output").css({
          "position": "relative",
          "margin": "10px",
          "padding": "5px",
          "border": "solid 1px " + color
        });
      $("#output").html("<span class='headerText' style='color:"+ color +";'>"+data.status+"</span>");
      $("#output").show("blind");
      $("#loading").hide();
      //display returned picture data  
      if(data.code == 1)
        $("#formBody").html( getPictureHTML(data)); 
      setFooter();
    },
    error: function(a, b, data){
      alert("error: " + data);
    }
  });

  //submit the form!!!
  $("#submit").click(function(){
    var data = $("#pictureForm").serialize();
    $("#loading").show();
  
    $.ajax({
      url: "/backend/pictures-backend.php?act=update",
      data: data,
      dataType: "json",
      type: "post",
      success: function(data){
        var color = (data.code == 1) ? "green" : "red";
        $("#output").css({
            "position": "relative",
            "margin": "10px",
            "padding": "5px",
            "border": "solid 1px " + color
          });
        $("#output").html("<span class='headerText' style='color:"+ color +";'>"+data.status+"</span>");
        $("#output").show("blind");
        $("#loading").hide();
        
        //display returned picture data  
        if(data.code == 1)
          $("#formBody").html( getPictureHTML(data) ); 
          
        $('html, body').animate({scrollTop:0}, 'fast');
        setFooter();
      },
      error: function(a, b, data){
        alert("error: " + data);
      }
    });
  }); 
  
  /****************************************************************************
  *button listeners
  */
  
  $("#upload").click(function(){
      $("#uploader").fadeIn("fast");
      $("#background").fadeIn("fast");
  });
  
  $("#modalCancel").click(function(){
      $("#uploader").fadeOut("fast");
      $("#background").fadeOut("fast");
      $("#modalOutput").fadeOut("fast");
  });  
  
  $("#uploadForm").submit(function(){
      $("#modalOutput").html("");
      if($("#picFile").val() != ""){
        $("#uploadForm").ajaxSubmit({
          url: "/backend/pictures-backend.php?act=upload",
          dataType: "json",
          clearForm: "true",
          error: function(val, error_type, error_text){
            alert(error_text);
            console.log(val);
            alert("not good");
          },
          success: function(data, statusText, xhr, $form){
            if(data.code == 1){
              $("#uploader").fadeOut("fast");
              $("#background").fadeOut("fast");
              $("#modalOutput").fadeOut("fast");
              $('#upload_frame').hide();
              $('#upload_frame').attr('src','');
              $("#formBody").html( getPictureHTML(data) );
              $('#modalCancel').show("clip");
              $('#modalSubmit').show("clip");
              $(':input','#uploadForm')
                .not(':submit, :reset')
                .val('')                      
            }
            //server side file error
            else{
              $("#modalOutput").css({
                "color": "red"
              });
              $('#modalCancel').show("clip");
              $('#modalSubmit').show("clip");
              $('#upload_frame').hide("clip");
              //reset form
              $(':input','#uploadForm')
                .not(':submit, :reset')
                .val('')            
            }
            $("#modalOutput").html("<span class='headerText'>"+data.status+"</span>");
            $("#modalOutput").fadeIn("slow").delay(2000).fadeOut("slow");
          }
        });
        $('#modalCancel').hide("clip");
        $('#modalSubmit').hide("clip");
        $('#upload_frame').show("clip");
        
        var id= $("#progress_key").val();
        $('#upload_frame').attr('src','/backend/upload-frame.php?up_id='+ id);
      }
      //client side file error
      else{
        $("#modalOutput").css({
          "color": "red"
        });
        $("#modalOutput").html("<span class='headerText'>No File Selected.</span>");       
        $("#modalOutput").fadeIn("slow").delay(2000).fadeOut("slow");
      }
      return false; //returning  false prevents reposting when refreshing a page
  });
  
  /* End Listeners */
  
});

//center the modal window
function reposition(){
  //do some math to center modal window
  var width = $(window).width();
  var height = $(window).height();
                     
  //600x460 is defined in profile.css under #tagger 
  var _left = (width - 600) / 2; 
  var _top = height/4; 

  $("#uploader").css({
    "left": _left,
    "top": _top 
  }); 
  
  $("#modalOutput").css({
    "left": _left,
    "top": _top +  $("#uploader").height() + 30
  }); 
}

function setFooter(){
	var height = $(document).height();
	$("#pageFooter").css("top", height);
}

//convert json picture info into html
function getPictureHTML(data){
  var html = "";

  for(i = data.pictures.length - 1; i > 1; i--){
    var checked = (data.current_picture == data.pictures[i]) ? "checked" : "";
    html += "<div class='result'>" +
                    "<div class='resultLeft'>" +
                      "<img class='shadowed-gray resultImg'id=' "+data.pictures[i]+"' src='/users/"+data.owner+"/albums/"+data.album_id+"/"+data.pictures[i]+"'/>"+
                    "</div>"+
                    "<div class='resultRight'>"+
                      "<span id='dataField' class='headerText'>"+
                        "<input name='currentpicture' type='radio' value='"+data.pictures[i]+"' "+checked+"/>Current Picture<br>"+
                        "<input name='deletepicture[]' type='checkbox' value='"+data.pictures[i]+"'/>Delete Picture"+
                      "</span><br>"+
                    "</div>"+
                  "</div>";
  }
  return html;
}
