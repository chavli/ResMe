/******************************************************************************
*	jquery-home-articles.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for the "User Articles" tab in home.php
*
******************************************************************************/

//constants used by the article submission code
var CHAR_LIMIT = 110;
var ERR_OK = 0;	    //error free :D
var ERR_LONG = 1;	//text exceeds limit
var ERR_EMPT = 2;	//no text entered

$(function(){
  //initialize char counts
	$("#tcharcount").html(CHAR_LIMIT);
	$("#dcharcount").html(CHAR_LIMIT);
  
  /*
  * submit button for the user articles tab
  */
	$('#submitArticle').click(function(){
		count1 = charCount("title", "tcharcount");
		count2 = charCount("description", "dcharcount");

		var print = function(output, color){
			$("#output").css({
				"position": "relative",
				"padding": "5px",
				"border": "solid 1px " + color
			}); 
			$("#output").html("<span class='normalText' style='color:"+color+"'>"+ output +"</span>");
			$("#output").show("blind");
			resize(true);
		}
		
    if(count1 == ERR_OK && count2 == ERR_OK){
      if(	
        $("#jobs").attr("checked") ||
        $("#business").attr("checked") || 
        $("#economy").attr("checked") ||
        $("#advice").attr("checked")
      ){
        var data = $("#submissionForm").serialize();
        $.ajax({
          url: "/backend/home-backend.php?act=submit",
          data: data,
          type: "POST",
          dataType: "json",
          success: function(json, output, color){
            //$("#output").hide();
            color = (json.code == 1) ? "green" : "red";
            output = json.status;
            print(output, color);
            resetSubmissionForm();
            displayUserArticles(false);
          },
          error: function(a, errorType, errorText){
            console.log(errorText);
          }
        });
      }
      else
        print("Please select at least one category.", "red");
    }
    else{
      color = "red";
      if(count1 == ERR_LONG || count2 == ERR_LONG)
        output = "Character Limit Exceeded"; 
      else if(count1 == ERR_EMPT || count2 == ERR_EMPT)
        output = "Please Fill in all Fields";

      print(output, color);
    }
  });
  
  //keyboard listeners used to keep track of character counts
	$("#title").keyup(function(){charCount("title", "tcharcount");});
	$("#description").keyup(function(){charCount("description", "dcharcount");});
});

function displayUserArticles(doreset){
	if(doreset)
  	resetTabContents();
	$("#submissionForm").show();
	$.ajax({
		url: "/backend/home-backend.php?act=articles",
		dataType: "json",
		success: function(json){
			html = "";
			categories = "";
			if(json.code == 1 && json.length > 0){
				for(i = 0; i < json.articles.length; i++){
					article = json.articles[i];
					categories = "";
					for(j = 0; j < article.category_strings.length; j++){
						categories += article.category_strings[j];
						categories += (j == article.category_strings.length - 1) ? "" : ", ";
					}
					label = (article.judged) ? "Unapprove" : "Approve";
					html += 
						"<div style='position:relative;padding:10px;'>"+
							"<button class='minorText judge' type='button' id='"+ article.id +"'>"+ label +"</button>"+
							"<a href='"+ article.data +"' class='headerText' style='margin-left:10px;'>"+ article.title +"</a><br>"+
							"<span class='headerText'>"+ article.description +"</span>"+
							"<div class='minorText'>"+
								"<span>Categories: <b>" + categories + "</b></span>" +
							"</div>"+
							"<div class='minorText'>"+
								"<span class='mdata'>By: <a href='/"+ article.submitter +"'>"+ article.submitter +"</a></span>"+
								"<span class='mdata'>Submitted: "+ article.time  +"</span> --- "+
								"<span class='mdata' style='color:green;'>Likes: <span id='likes-" + article.id + "'>"+ article.upvotes +"</span></span>"+
								"<span class='mdata' style='color:red;'>Dislikes: <span id='dislikes-" + article.id + "'>"+ article.downvotes +"</span></span>"+
								//"<span class='mdata'>report</span>"+
							"</div>" +
						"</div>";
				}
				$("#tabContents").html(html);
  			$("button").button();
				
				//add the id to the user's list of likes
				$(".judge").click(function(){
					data = new Object;
					data.id = $(this).attr("id");
					self = $(this);
					$.ajax({
						url: "/backend/home-backend.php?act=judge",
						data: data,
						dataType: "json",
						success: function(json){
							if(json.code == 1){
								self.button("option", "label", json.display);
								$("#likes-"+data.id).html(json.likes);
							}
						},
						error: function(a, type, text){
							console.log("jquery-home.php:276");
							console.log(text);
						}
					});			
				});

      	setTimeout("resize(true)", 10);
			}
		},
		error: function(a, type, text){
			console.log("jquery-home.php:286");
			console.log(text);
		}
	});
}

//input: elem id to count the chars in
//output: elem id to output the count
//returns the appropriate error code 
function charCount(input, output){
	retval = ERR_OK;

	text = $("#"+input).attr("value");
	remainder = CHAR_LIMIT - text.length;
	color = (remainder <= 0) ? "red" : "black";
	$("#"+output).html(remainder);
	$("#"+output).css({color: color});

	//check for errors	
	if(remainder < 0)
		retval = ERR_LONG;
	else if(remainder == CHAR_LIMIT)
		retval = ERR_EMPT;

	return retval;
}

