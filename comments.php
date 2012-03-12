<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
  session_start();
  util_session_check();
  
	if(!isset($_GET["uname"]))
		header('Location: /');        
?>

<html>
  <head>
    <title>ResMe[Comments]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>       
    <link rel='stylesheet' href='/public/css/comments.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
		<script type='text/javascript' src='/public/js/jquery-core-1.5.js'></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
		<script type='text/javascript' src='/public/js/jquery-comments.js'></script>
    <script type='text/javascript' src='/public/js/jquery-sitemenu.js'></script>          
    <script type='text/javascript' src='/public/js/widgets/CommentBox.js'></script>          
    <script type='text/javascript' src='/public/js/widgets/ThumbContainer.js'></script>          
    <script type='text/javascript' src='/public/js/widgets/TextElement.js'></script>          
    <script type='text/javascript' src='/public/js/widgets/UrlElement.js'></script>          

  </head>
  <body id='<?php echo $_GET["uname"]; ?>'>
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
                  <a href="/home/">Contact Us</a>
                  <a href="/search/">About</a>                      
                  <a href="/search/">Search</a>          
								</span>';              
              }
            ?>          
          
          </div>      
      </div>
    </div> <!-- end pageHeader -->
    
    <div id='pageBody' width='100%'>
      <div id='commentHeader'>
        <span id='fullname' style='position:absolute; left: .7em; top: .7em;'></span>
        <ul id='pageMenu'></ul>    
      </div>
      <div id='commentBody' class='shadowed'>
        <div id='leftContainer'>
					<div id='resPages' ></div>
					<div id='resDisplay' style='position:relative;left:.7em;border:solid 1px LightGray;' class='shadowed-gray'></div>
        </div>
        <div id='rightContainer'>
          <div id='commentInput'></div>
          <div id='comments'></div><!-- end comments-->          
        </div><!-- end rightContainer -->
      </div><!-- end commentBody -->
    </div><!-- end pageBody -->
		<div id='pageFooter' style='position:absolute;top:1400;' >
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
		</div>
  </body>
</html>
