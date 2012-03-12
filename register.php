<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
  
  //redirect user if they're logged in
  if(isset($_SESSION["LAST_ACTIVITY"]))
    header("Location: /home/");
?>

<html>
	<head>
    <title>resMe[Registration]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/register.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>
    <script type='text/javascript' src='/public/js/jquery-core-1.5.js'></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
		<script type='text/javascript' src='/public/js/jquery-register.js'></script>
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
      <div id='registerHeader'>
        <span class='pageTitle shadowed' style='position:absolute; left: 10px; top: 10px;'>Create Your Account</span>
        <ul id='pageMenu'></ul>    
      </div>
      <div id='registerBody' class='shadowed'>
        <div id='leftContainer'>
          <form id='registerForm' name='signup' method='post' enctype='multipart/form-data' >
            <div style='position:relative;margin-bottom:7px;'>
              <label for="nfirst" class='fieldLabel'>First Name:</label>
              <input type='text' id='nfirst' name='n_first'/>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              <label for="nlast" class='fieldLabel'>Last Name:</label>
              <input type='text'id='nlast' name='n_last'/>
            </div>
            <div style='position:relative;vertical-align:top;margin-bottom:7px;'>
              <label for="profs" class='fieldLabel' style='vertical-align:top'>Your Profession(s):</label>
              <select id='profs' name='profs[]' multiple size=7>
                <?php
                  //parse the xml list of professions and display it in html
                  $xmlfile = "system/xml/professions.xml";
                  $parsedxml = util_xmlfile_to_array($xmlfile);
                  $i = -1; $prof_output = "";
                  foreach($parsedxml as $entry){
                    if(strcmp($entry["tag"], "PROFESSION") == 0){
                      $prof_output .= "<option value=".++$i.">".$entry["value"]."</option>";
                    }
                  }
                  echo $prof_output;
                ?>
              </select>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              <label for="nname" class='fieldLabel'>Username:</label>
              <input type='text' id='nname' name='n_name'/>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              	<label for="nmail" class='fieldLabel'>Email:</label>
               <input type='text' id='nmail' name='n_mail'/>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              <label for="npass" class='fieldLabel'>Password:</label>
              <input type='password' id='npass' name='n_pass'/>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              <label for="ncpass" class='fieldLabel'>Confirm Password:</label>
              <input type='password' id='ncpass' name='n_cpass'/>
            </div>
            <div style='position:relative;margin-bottom:7px;'>
              <label for="nkey" class='fieldLabel'>Alpha Key:</label>
              <input type='text' id='nkey' name='n_key'/>
            </div>
            <div style='position:relative;text-align:right;width:100%;'>
              <button class='normalText' type='reset'  onClick='window.location="/";'>Cancel</button>
              <button class='normalText' type='button' id='submit'>Create Account</button>
            </div>
            <div id='output'></div>
          </form>
        </div> <!-- end leftContainer -->
      </div> <!-- end editAccountBody-->
    </div><!-- end pageBody-->
		<div id='pageFooter' style='position:absolute;bottom:0' >
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
    </div>
	</body>
</html>
