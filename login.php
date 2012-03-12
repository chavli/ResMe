<!-- login.php, this file handles authenticating users as the attempt to login -->

<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
  
	if(isset($_SESSION['LAST_ACTIVITY'])){
  	header("Location: /".$_SESSION['username']);
  }

?>

<html>
  <head>
    <title>ResMe[Login]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
		<link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/login.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>     
    <script type='text/javascript' src='/public/js/jquery-core-1.5.js'></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
    <script type='text/javascript' src='/public/js/jquery-login.js'></script>
    <script type='text/javascript' src='/public/js/jquery-sitemenu.js'></script>          
  </head>
	<body>
    <div id="pageHeader" class="shadowed">
      <div id="menu" class="normalText">Menu</div>    
      <div id="info" class="normalText"><?php echo date("l, F jS");?></div>    
      <img id="logo" src="/public/images/resme_small.png"/>
      <div  id='siteMenu'>
          <div id="menuitems">
            <?php
              if(!isset($_SESSION['LAST_ACTIVITY'])){
                echo '<span class="normalText">
                  <a href="/home/">Home Page</a>
                  <a href="/search/">Search</a>
                  <a href="#">Contact Us</a>
                  <a href="#">About</a>
                 </span>';
              }
            ?>
          </div>      
      </div>
    </div> <!-- end pageHeader -->
    <div id='pageBody'>
      <div id='loginHeader'>
        <span class='pageTitle shadowed' style='position:absolute; left: 10px; top: 10px;'>Welcome to ResMe!</span>
        <ul id='pageMenu'>
          <li><span class='headerText' ><a href='#'>Forgot Password</a></span></li>
          <li><span class='headerText' ><a href='/registration/'>Join ResMe</a></span></li>
        </ul>  
      </div>
      <div id='loginBody' class='shadowed'>
        <div id='leftContainer'>
          <form id='loginForm' enctype='multipart/form-data'>
            <label class='headerText' for='username' style='position:relative;top:5px;width:100px;float:left;'>Username:</label><input id='username' class='normalText' type='text' name='uname' style='width:200px;'/><br>
            <label class='headerText' for='password' style='position:relative;top:10px;width:100px;float:left;'>Password:</label><input id='password' class='normalText' type='password' name='upass' style='position:relative;top:5px;width:200px;'/><br>
            <div style='position:relative;top:5px;width:300;min-width:300;text-align:right;'><button class='normalText' id="submit" type='button' style="color: 0;">Login</button></div>
        		<div id='output'>
							<?php
								//failed logging in from index.php
								if(isset($_GET["act"]) && strcmp($_GET["act"], "fail") == 0){
	                echo "<span class='normalText' style='color:red'>Invalid Username/Password</span>";
								}
						?>
					</div>
					</form>
        </div>
        <div id='rightContainer'>
          <!-- maybe put a graphic here or something -->
        </div>
      </div>
    </div>
		<!-- copyright -->			
		<div id='pageFooter' style='position:absolute;bottom:0' >
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
		</div>
	</body>
</html>
