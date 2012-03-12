/******************************************************************************
*	jquery-resumes.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of resumes.php
*
******************************************************************************/
$(function(){
  $("#output").hide();
  //align the footer
  $("#loading").hide();
	$("button").button();
  reposition();
  
  //resize event listener that keeps the tagger/viewer modal windows centered when
	//browser window is resized.
	$(window).resize(reposition);
  
    //load all the resumes to display
  $.ajax({
    url: "/backend/resumes-backend.php?act=show",
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
      //display returned resume data  
      if(data.code == 1)
        $("#formBody").html( getResumeHTML(data) ); 
      setFooter();
    },
    error: function(a, b, data){
      alert("error: " + data);
    }
  });

  //submit the form!!!
  $("#submit").click(function(){
    var data = $("#resumeForm").serialize();
    $("#loading").show();

    $.ajax({
      url: "/backend/resumes-backend.php?act=update",
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
        
        //display returned resume data  
        if(data.code == 1)
          $("#formBody").html( getResumeHTML(data) ); 
          
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
      if($("#resFile").val() != ""){
        $("#uploadForm").ajaxSubmit({
          url: "/backend/resumes-backend.php?act=upload",
          dataType: "json",
          clearForm: "true",
          error: function(val, error_type, error_text){
            alert(error_text);
            console.log(val);
          },
          success: function(data, statusText, xhr, $form){
            if(data.code == 1){
              $("#uploader").fadeOut("fast");
              $("#background").fadeOut("fast");
              $("#modalOutput").fadeOut("fast");
              $('#upload_frame').hide();
              $('#upload_frame').attr('src','');
              
              $("#formBody").html( getResumeHTML(data) ); 
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

//convert json resume info into html
function getResumeHTML(data){
  var html = "";
  for(res_index in data.resumes){
    var resume = data.resumes[res_index];
    html += "<div class='result'>" +
                    "<div class='resultLeft'>" +
                      "<img class='shadowed-gray'id=' "+resume.album_id+"' src='/users/"+resume.owner+"/albums/"+resume.album_id+"/0.jpg'/>"+
                      "<span id='data' style='position:absolute;left:210px;'>"+
                        "<span id='dataField' class='headerText' style='position:relative;margin-bottom:5px;'>"+
                          "Title: <input type='text' name='title[]' value='"+resume.title+"'/>"+
                          "<input type='hidden' name='id[]' value='"+resume.album_id+"'/>"+
                        "</span><br>"+
                        "<span id='dataField' class='headerText'style='position:relative;margin-bottom:5px;'>Date Uploaded: "+resume.created+"</span><br>"+
                        "<span id='dataField' class='headerText'style='position:relative;margin-bottom:5px;'>Pages: "+resume.pages+"</span><br>"+
                        "<span id='dataField' class='headerText'style='position:relative;margin-bottom:10px;'>Type:"+
                        "<select class='headerText' name='resType[]'>";
    //dumb, another for loop
    for(i in resume.alltypes){
      var selected = (Math.pow(2, i) == resume.type) ? "selected" : "";
      html += "<option value="+i+" "+selected+">"+resume.alltypes[i]+"</option>";
    }
    html += "</select></span><br>";
    
    var options = "";
    if(resume.path){
      html += "<span id='dataField' class='headerText'style='position:relative;margin-bottom:5px;'><a href='/"+resume.path+"'>Download PDF</a></span>";
      var checked = (resume.iscurrent == 1) ? "checked" : "";
      options =	"<div class='resultRight'>"+
                "<span id='dataField' class='headerText'>"+
                  "<input name='currentresume' type='radio' value='"+resume.album_id+"' "+checked+"/>Current Resume<br>"+
                  "<input name='deleteresume[]' type='checkbox' value='"+resume.album_id+"'/>Delete Resume"+
                "</span><br>"+
                "</div>";
    }
    html +=	"</span></div>"+options+"</div>";
  }
  return html;
}
