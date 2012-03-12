<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
  util_session_check();
  
  //if already logged in, send to home.php
  if(isset($_SESSION["LAST_ACTIVITY"])){
    header("Location: /home/");
  }
?>
<html>
<head>
  <title>ResMe - Never Forget Your Resume</title>
  <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
  <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />
  <link rel='stylesheet' href='/public/css/index.css' type='text/css' media='screen'/>
  <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
  <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
  <script type='text/javascript' src='/public/js/jquery-core-1.5.js'></script>
  <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
  <script type='text/javascript' src='/public/js/jquery-index.js'></script>
</head>
<body>
	<div id='pageHeader'>
		<ul id='siteMenu'>
			<li><span class='headerText'><a href='/search/'>Search</a></span></li>
			<li><span class='headerText'><a href='#'>Contact Us</a></span></li>
			<li><span class='headerText'><a href='#'>About</a></span></li>
		</ul>
	</div>
  <div id="logo" >
    <img  src="/public/images/resme.png"/>
    <div id="buttons">
      <button class="normalText" id="blogin" style='width:90px;'>Login</button>
      <button class="normalText" id="bregister" style='width:90px;'>Register</button>
    </div>
    <div id="login">
      <form id='loginForm' method='post' enctype='multipart/form-data' >
        <label for="username" class='headerText'>Username:</label>
        <input type='text' id='username' name="uname" style="width:160px;"/>
        <label for="secret" class='headerText'>Password:</label>
        <input type='password' id='secret' name="upass" style="width:160px;"/><br>
        <button class="normalText" type="reset" id="docancellogin" style='position:relative;margin-top:10px;width:90px;'>Cancel</button>
        <button class="normalText" type="button" id="dologin" style='position:relative;margin-top:10px;width:90px;'>Login</button>
      </form>
    </div>

    <div id="register" style="position:relative;">
      <form id='registerForm' name='signup' method='post' enctype='multipart/form-data' >
        <div style='position:relative;margin-bottom:5px;'>
          <label for="nfirst" class='headerText' style='display:block;'>First Name</label>
          <input type='text' id='nfirst' name='n_first'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
          <label for="nlast" class='headerText' style='display:block;'>Last Name</label>
          <input type='text'id='nlast' name='n_last'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
          <label for="nname" class='headerText' style='display:block;'>Username</label>
          <input type='text' id='nname' name='n_name'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
            <label for="nmail" class='headerText' style='display:block;'>Primary E-Mail</label>
           <input type='text' id='nmail' name='n_mail'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
          <label for="npass" class='headerText' style='display:block;'>Password</label>
          <input type='password' id='npass' name='n_pass'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
          <label for="ncpass" class='headerText' style='display:block;'>Confirm Password</label>
          <input type='password' id='ncpass' name='n_cpass'/>
        </div>
        <div style='position:relative;margin-bottom:5px;'>
          <label for="nkey" class='headerText' style='display:block;'>Alpha Key</label>
          <input type='text' id='nkey' name='n_key'/>
        </div>
        <button class="normalText" id="docancelregister" style='width:90px;' type="reset">Cancel</button>
        <button class="normalText" id="doregister" style='width:90px;' type="button">Register</button>
      </form>
    </div>
  </div>
  <!-- copyright -->			
  <div id='copyright' style='position:absolute;' >
    <div id='names'>
      <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
    </div>
  </div>
</body>
</html>
