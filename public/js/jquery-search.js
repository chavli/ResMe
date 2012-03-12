/******************************************************************************
*	jquery-search.js
*
*	handles UI functionality, communication with backend php files, and wrapping
*	json responses in html of search.php
*
******************************************************************************/
$(function(){
	//align the footer
	//setTimeout("setFooter()", 1);
	setFooter();
	$("select").change(onFormChange);
});

function onFormChange(){
	var data = $("#searchForm").serialize();
$.ajax({
		url: "/backend/search-backend.php",
		data: data,
    dataType: "json",
		success: function(data){
      //display results
      var results_div = document.getElementById("searchResults");
      results_div.innerHTML = "";
      for(var i = 0; i < data.length; i++){
        results_div.innerHTML += "<div id='"+data[i].username+"' class='searchResult'>"+
          //"<img class='shadowed-gray' style='margin-right:10px;' src='"+data[i].profile_picture+"'/>"+
          "<img class='shadowed-gray' src='"+data[i].thumbnail+"'/>"+
					"<span class='headerText' style='position:absolute;left:210px;'>"+
          "<a href='../"+data[i].username+"'>"+data[i].firstname+" "+data[i].lastname+"</a></span>"+
					"</div>";	
      }
      
      //resize container
      var length = $("#searchForm").height() + $("#searchResults").height();
      $("#leftContainer").css("height", length + 10);
		},
    error: function(a, b, data){
      alert(data);
    }
	});
}

function setFooter(){
  //dynamically set tag heights and positions
  var length = $("#searchForm").height() + $("#searchResults").height();
  $("#leftContainer").css("height", length + 10);
  
	var height = $(document).height();
	$("#pageFooter").css("top", height);
}
