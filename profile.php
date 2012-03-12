<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
	util_session_check();
  
  $up_id = uniqid(); 
  if ($_POST) {

    //specify folder for file upload
    $folder = "/tmp/"; 

    //specify redirect URL
    $redirect = "upload.php?success";

    //upload the file
    move_uploaded_file($_FILES["vidFile"]["tmp_name"], $folder.$_FILES["vidFile"]["name"]);

    //do whatever else needs to be done (insert information into database, etc...)

    //redirect user
   header('Location: /'.$_SESSION["username"]); die;
  }
  
    if(isset($_GET['uname'])){
      $uid = $_GET['uname'];
    }
    else{
      //profile.php wasn't given a username, redirect
      header("Location: /");
    }
  ?>

<html>
  <head>
    <title>ResMe</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <script type='text/javascript' src='/public/js/jquery-core-1.4.4.js'></script> <!-- multifile requires 1.4.4 -->
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
    <script type='text/javascript' src='/public/js/phototagger.js'></script>
    <script type="text/javascript" src="/public/js/multifile/jquery.form.js"></script>
    <script type="text/javascript" src="/public/js/multifile/jquery.MultiFile.js"></script>
	 	<script type="text/javascript" src="/public/js/galleria/galleria.js"></script>
    <script type="text/javascript" src="/public/js/galleria/themes/classic/galleria.classic.js"></script>
		<script type="text/javascript" src="/public/js/jquery-profile.js"></script>
    <script type='text/javascript' src='/public/js/jquery-sitemenu.js'></script>
    <script type='text/javascript' src='/public/js/widgets/ThumbContainer.js'></script>
    <script type='text/javascript' src='/public/js/widgets/TextElement.js'></script>
    <script type='text/javascript' src='/public/js/widgets/UrlElement.js'></script>
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>       
    <link rel='stylesheet' href='/public/css/profile.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link href="public/css/style_progress.css" rel="stylesheet" type="text/css" />
    <div id='taggerWrapper' title='<?php echo $up_id;?>'>
    <?php 
      #enable tagging only if user is logged in
      if( isset($_SESSION["LAST_ACTIVITY"]) && strcmp($_SESSION["username"], $uid) == 0){
        echo '
        <div id="tagger" class="modalwindow">
          <ul class="tabs">
            <li><a href="#tab1">Text</a></li>
            <li><a href="#tab2">Pictures</a></li>
            <li><a href="#tab3">Video</a></li>
            <li><a href="#tab4">Audio</a></li>
            <li><a href="#tab5">Documents</a></li>
          </ul>
          <div class="tabContainers">
            <div id="tab1" class="tabContents">
							<span class="headerText">Enter Text:</span>
              <textarea class="normalText" id="textInput" style="height:320px; width:100%;resize:none;"></textarea>
              <button class="normalText" id="textSubmit" style="position:relative;width:80px;left:480px;" type="button">Submit</button>
            </div>  
            <div id="tab2" class="tabContents">
              <form name="picForm" id="picForm" action="" method="POST" enctype="multipart/form-data">
                <span class="normalText">Select images to upload from your computer.</span> <br>
                <span class="normalText">Allowed image types: .jpg, .png, .bmp, .gif, .tiff</span> <br><br>
								<input id="userfile" type="file" class="multi normalText" maxlength="100" name="userfile[]"/>
              	<button class="normalText" style="position:relative;width:80px;left:480px;"type="submit">Submit</button>
              </form>            
            </div>
            <div id="tab3" class="tabContents">
              <form action="" method="post" enctype="multipart/form-data" name="vidForm" id="vidForm">
                <input id="vidType" name="vidType" type="radio" value="0" checked/><span class="normalText">Upload Video (50MB limit)</span><br>
                <div id="vidOption1" style="margin-left:20px;">
                  <span class="normalText">Select a video to upload from your computer.</span> <br>
                  <span class="normalText">Allowed video types: .mpeg, .mp4, .flv</span> <br><br>
                  <input type="hidden" name="APC_UPLOAD_PROGRESS" id="progress_key" value="'.$up_id.'"/>
                  <input class="normalText" name="vidFile" type="file" id="vidFile"/>
                  <br />
                  <iframe id="upload_frame" name="upload_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>
                  <br />
                  <button id="vidSubmit" style="position:relative;width:80px;left:460px;"type="submit" class="normalText">Submit</button>
                </div>
                
                <input id="vidType" name="vidType" type="radio" value="1"/><span class="normalText">YouTube Link</span><br>
                <div id="vidOption2" style="margin-left:20px;">
                  <input type="text" id="urlInput" name="urlInput" value="http://" class="normalText" style="width:540px;left:0px;"/><br>
                  <div id="vidPreview" style="text-align:center;"></div>
                  <button id="urlSubmit" type="button" style="position:relative;width:80px;left:460px;" class="normalText">Submit</button>
                </div>
              </form>
            </div>
            <div id="tab4" class="tabContents">Tab 4</div>
            <div id="tab5" class="tabContents">Tab 5</div>
          </div>
          <button class="normalText" id="closeTagger" type="button">Close</button>
        </div>
				<div id="viewer" class="modalwindow">
					<div id="tagData"></div>
					<button class="normalText" id="deleteTag" type="button">Delete Tag</button>
					<button class="normalText" id="closeViewer" type="button">Close</button>
				</div>
        <div id="background"></div>	
				<div id="error" class="modalwindow"></div>
        ';
        }
    ?>
   </div>
  </head>
  <body id="<?php echo $_GET['uname']; ?>">
  <div id="wrapper">
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
              else{
                echo '<span class="normalText">
											<a href="/home/">Home Page</a>
                      <a href="/search/">Search</a>          
										</span>';              
              }
            ?>          
          
          </div>      
      </div>
    </div> <!-- end pageHeader -->
    
    <div id='pageBody'>
      <div id='profileHeader'></div><!-- end profileHeader -->
      
      <div id='profileBody'  class='shadowed'>
        <div id='leftContainer'>
          <img id='profilePicture'/> 
          <br><br>
          <div id='contactInfo' style="position:static"></div>
					<br>
          <div id='resPages' class='resPages' style='text-align:center;'></div>
        </div>
        <div id='rightContainer' class='resumediv'>
        </div>
      </div><!-- end profile container -->
    </div> <!-- end pageBody -->
		<!-- copyright -->			
		<div id='pageFooter' style='position:absolute;top:1400;' >
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
		</div>
        </div>
  </body>
</html>
