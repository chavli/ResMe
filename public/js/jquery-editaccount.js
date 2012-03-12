/******************************************************************************
*	jquery-editaccount.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html for editaccount.php (settings page)
*
******************************************************************************/

$(function(){
    $("button").button();
		$("#loading").hide();
    //dynamically set height
    resize(false);

		$("#submit").click(function(){
			if(validate()){
				$("#loading").show();
				$("#output").hide();
				resize(true);
				var data = $("#updateForm").serialize();
				$.ajax({
					url: "/backend/editaccount-backend.php?act=update",
					data: data,
					dataType: "json",
					type: "post",
					success: function(data){
						if(data.code == 1)
              $("#output").css({"color": "green", "border":"solid 1px green"});
						else
              $("#output").css({"color": "red", "border":"solid 1px red"});
             
            $("#output").html("<span class='normalText'>"+ data.status +"</span>");
            $("#loading").hide();
            $("#output").show();       
					},
					error: function(a, errorType, errorText){
						alert(errorText);
					}
				});
			}
			else
        $("#output").css({"color": "red", "border":"solid 1px red"});
        $("#output").html("<span class='normalText'>Fill in all fields correctly.</span>");
        $("#output").show();    
		});
});

function resize(animate){
	var height = $("#updateForm").height();
  if(animate)
  	$("#editAccountBody").animate({
    	"height": (height + 15)
    });  
  else
  	$("#editAccountBody").css({
    	"height": (height + 15)
    });
	$("#pageFooter").css("top", $(document).height());  
}

//TODO: check the email and phone number fields
function validate(){
	var success = true;
	//check if email is valid
	var email = $("#email").attr("value");

	if(email.match(/[a-z0-9\.]+@[a-z0-9]+\.[a-z]+/) == null){
 		$("#output").html("<span class='errorText'>Invalid email</span>");
		$("#loading").hide();
		$("#output").show();
		success = false;
	}
	return success;
}

