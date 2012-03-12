<?php
 	date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
  session_start();
  util_session_check();

	if(isset($_SESSION["LAST_ACTIVITY"]) && isset($_GET['act'])){
		$uname = $_SESSION["username"];
	}
	else{
		//if a user tries to access this page without logging in, they will be sent back to the homepage
    header("Location: /");   
	}
?>

<html>
	<head>
    <title>ResMe[Your Settings]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>        
    <link rel='stylesheet' href='/public/css/editaccount.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <script type='text/javascript' src='/public/js/jquery-core-1.4.4.js'></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
		<script type='text/javascript' src='/public/js/jquery-editaccount.js'></script>
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
      <div id='editAccountHeader'>
        <span class='pageTitle shadowed' style='position:absolute; left:0.65em; top:0.65em;'>Your Profile Settings</span>
        <ul id='pageMenu'>
        </ul>    
      </div>
      <div id='editAccountBody' class='shadowed'>
        <div id='leftContainer'>
          <form action='' id='updateForm' name='updateform' enctype='multipart/form-data' method='post'>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="profile" class='headerText fieldLabel'>Profile Picture:</label>
                <span id="profile" class="normalText" style="margin-left:0.8em;"><a href="/editpictures/">Add/Manage Profile Pictures</a></span>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="resume" class='headerText fieldLabel'>Resume PDF:</label>
                <span id="resume" class="normalText" style="margin-left:0.8em;"><a href="/editresumes/">Add/Manage Resumes</a></span>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="first" class='headerText fieldLabel'>First Name:</label>              
                <input class='fieldInput' type="text" id="first" name="first" value='<?php if(isset($_SESSION['firstname'])){echo $_SESSION['firstname'];}?>'/>
              </div>              
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="last" class='headerText fieldLabel'>Last Name:</label>
                <input class='fieldInput' type="text" id="last" name="last" value='<?php if(isset($_SESSION['lastname'])){echo $_SESSION['lastname'];}?>'/>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="email" class='headerText fieldLabel'>E-Mail:</label>
                <input class='fieldInput' type="text" id="email" name="email" value='<?php if(isset($_SESSION['email'])){echo $_SESSION['email'];} ?>'/>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="professions" class='headerText fieldLabel' style='vertical-align:top'>Profession(s):</label>
                <select class='fieldInput' id="professions" name="profs[]" multiple>
                  <?php
                    //parse the xml list of professions and display it in html
                    $xmlfile = "system/xml/professions.xml";
                    $parsedxml = util_xmlfile_to_array($xmlfile);
                    $i = -1; $output = "";
                    foreach($parsedxml as $entry){
                      if(strcmp($entry["tag"], "PROFESSION") == 0)
                        $output .= "<option value=".++$i.">".$entry["value"]."</option>";
                    }
                    echo $output;
                  ?>
                </select>
                <script type="text/javascript">
                  /* extract the user's professions from the profession value */
                  var doc = window.document;
                  var prof_val = <?php echo $_SESSION['profs']; ?>;
                  var prof_menu = doc.getElementById('professions');
                  for(var i = 0; i < prof_menu.options.length; i++){
                    var val = prof_menu.options[i].value;
                    if((prof_val & Math.pow(2,val)) == Math.pow(2, val)){
                      prof_menu.options[i].selected = true;
                    }
                  }
                </script>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="mphone" class='headerText fieldLabel'>Main Number:</label>
                <input class='fieldInput' type="text" id="mphone" name="mphone" value='<?php if(isset($_SESSION['mainphone'])){echo $_SESSION['mainphone'];} ?>'/>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="cphone" class='headerText fieldLabel'>Mobile Number:</label>
                <input class='fieldInput' type="text" id="cphone" name="cphone" value='<?php if(isset($_SESSION['cellphone'])){echo $_SESSION['cellphone'];} ?>'/>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="ophone" class='headerText fieldLabel'>Office Number:</label>
                <input class='fieldInput' type="text" id="ophone" name="ophone" value='<?php if(isset($_SESSION['officephone'])){echo $_SESSION['officephone'];} ?>'/>
              </div>
              <div style='position:relative;margin-bottom:0.6em;'>
                <label for="privacy" class='headerText fieldLabel'></label>
                <span id="privacy" class="normalText" style="margin-left:0.8em;"><a href="/privacy/">Privacy Settings</a></span>
              </div>                                      
              <div style='position:relative;margin-bottom:0.6em;text-align:right;width:100%;'>
                  <button class='normalText' type='button' onClick='window.location="<?php echo "/".$uname; ?>";'/>Cancel</button>	
                  <button class='normalText' type='button' id='submit'>Update</button>
              </div>
							<div id='loading' style='position:relative;margin:0.8em;height:2em;width:100%;'>
									<img id='loader' src='/public/images/loader.gif'/><label for='loader' class='headerText fieldLabel' style='float:right;width:93%;'>Updating Information...</label>
							</div>
              <div id="output"></div>
            </form>
        </div> <!-- end leftContainer -->
      </div> <!-- end editAccountBody-->
    </div><!-- end pageBody-->
		<div id='pageFooter' style='position:absolute;bottom:0' >
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
    </div>
	</body>
</html>
