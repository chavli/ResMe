<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
	util_session_check();

	if(!isset($_SESSION["LAST_ACTIVITY"]) || !isset($_GET["act"]))
    header("Location: /");
    
  $up_id = uniqid();  
?>

<html>
	<head>
		<title>resMe[Your Resumes]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />
    <link rel='stylesheet' href='/public/css/jquery-ui-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
		<link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>        
		<link rel='stylesheet' href='/public/css/resumes.css' type='text/css' media='screen'/>
		<link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link href="/public/css/style_progress.css" rel="stylesheet" type="text/css" />    
		<script type="text/javascript" src="/public/js/jquery-core-1.4.4.js"></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
		<script type="text/javascript" src="/public/js/jquery-resumes.js"></script>
    <script type="text/javascript" src="/public/js/multifile/jquery.form.js"></script>
    <script type='text/javascript' src='/public/js/jquery-sitemenu.js'></script>
		<!-- widgets -->
    <script type='text/javascript' src='/public/js/widgets/ThumbContainer.js'></script>
    <script type='text/javascript' src='/public/js/widgets/TextElement.js'></script>
    <script type='text/javascript' src='/public/js/widgets/UrlElement.js'></script>

    <?php 
    if(isset($_SESSION["LAST_ACTIVITY"])){
      echo '
      <div id="uploader" class="modalwindow tabContainers" title="'.$up_id.'">
        <form method="post" enctype="multipart/form-data" name="uploadForm" id="uploadForm">
          <div id="container" >
            <span class="normalText">Select a PDF file to upload (limit 300KB)</span> <br>
            <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="'.$up_id.'"/>
            <input class="normalText" style="color:#fff;" name="resFile" type="file" id="resFile"/>
            <br />
            <iframe id="upload_frame" name="upload_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>
            <br />
            <button id="modalCancel" style="position:relative;width:80px;left:425px;" type="reset" class="normalText">Cancel</button>
            <button id="modalSubmit" style="position:relative;width:80px;left:425px;" type="submit" class="normalText">Submit</button>
          </div>
        </form>
      </div> 
      <div id="modalOutput" class="modalwindow"></div>
      <div id="background"></div>
      ';
    }
    ?>
	</head>
	<body>
    <div id="pageHeader" class="shadowed">
      <div id="menu" class="normalText">Menu</div>    
      <div id="info" class="normalText"><?php echo date("l, F jS");?></div>    
      <img id="logo" src="/public/images/resme_small.png"/>
      <div  id='siteMenu'>
          <div id="menuitems">
            <?php
              if(isset($_SESSION["LAST_ACTIVITY"])){
                echo '<span class="normalText">
                  <a href="/home/">Home Page</a>
                  <a href="/'.$_SESSION["username"].'">Profile</a>
                  <a href="/'.$_SESSION["username"].'/comments/">Resume Feedback</a>
                  <a href="/search/">Search</a>
                  <a href="/editprofile/">Settings</a>
                  <a href="#">Contact Us</a>
                  <a href="#">About</a>             
                  <a href="/goodbye/">Logout</a>
                 </span>';                
              }
            ?>
          </div>      
      </div>
    </div> <!-- end pageHeader -->
		<div id='pageBody'>
			<div id='resumesHeader'>
					<span class='pageTitle shadowed' style='position:absolute; left: 10px; top: 10px;'>Manage Your Resumes</span>
					<ul id='pageMenu'>
					</ul>
			</div>
			<div id='resumesBody' class='shadowed'>
        <div id='loading' style='position:relative;margin:10px;height:32px;width:100%;'>
            <img id='loader' src='/public/images/loader.gif'/><label for='loader' class='headerText' style='float:right;width:96%;padding:6px;'>Updating Information...</label>
        </div>
        <div id='output'></div>
				<div id='leftContainer'>
					<div id='controls' class='formopts' style='border-bottom:1px solid grey;padding-bottom:5px;'>
						<button id='upload' type='button'>+ New Resume</button>
					</div>
          <form id='resumeForm' name='updateresume' enctype='multipart/form-data'>
            <div id='formBody'></div>
            <div id='formopts' class='formopts' ><button id='submit' type='button'>Update</button></div>
          </form>
        </div>
			</div>
		</div>
		<div id='pageFooter' style='position:absolute;bottom:0'>
    	<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
      </div>
		</div>
	</body>
</html>
