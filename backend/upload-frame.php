<?php

$url = basename($_SERVER['SCRIPT_FILENAME']);

//Get file upload progress information.
if(isset($_GET['progress_key'])) {
	$status = apc_fetch('upload_'.$_GET['progress_key']);

	echo $status['current']/$status['total']*100;
	die;
}
else if(!isset($_GET['up_id']))
  header("Location: /");
//

?>
<script src="/public/js/jquery-core-1.5.js" type="text/javascript"></script>
<link rel='stylesheet' href='/public/css/jquery-ui-1.8.10.css' type='text/css' media='screen'/>
<link href="/public/css/style_progress.css" rel="stylesheet" type="text/css" />

<script>
$(document).ready(function() { 
//
	setInterval(function(){
	$.get("<?php echo $url; ?>?progress_key=<?php echo $_GET['up_id']; ?>&randval="+ Math.random(), { 
	},
		function(data)	//return information back from jQuery's get request
			{
        //$('#progress_container').show();	//fade in progress bar	
        $('#progress_container').fadeIn(100);	//fade in progress bar	
				$('#progress_bar').width(data +"%");	//set width of progress bar based on the $status value (set at the top of this page)
				$('#progress_completed').html(parseInt(data) +"%");	//display the % completed within the progress bar
			}
		)},500);	//Interval is set at 500 milliseconds (the progress bar will refresh every .5 seconds)

});


</script>

<body style="margin:0px">
<!--Progress bar divs-->
<div id="progress_container">
	<div id="progress_bar">
  		 <div id="progress_completed"></div>
	</div>
</div>
<!---->
</body>
