<?php
  date_default_timezone_set("America/New_York");
	require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
  util_session_check();
  
	if(!isset($_SESSION["LAST_ACTIVITY"]) || !isset($_GET["act"])){
		header("Location: /");
	}
?>

<html>
  <head>
    <title>resMe[Your Privacy]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/page-elements.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/access.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>     
    <script type="text/javascript" src="/public/js/jquery-core-1.5.js"></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
    <script type="text/javascript" src="/public/js/jquery-access.js"></script>
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
        <div id='accessHeader' style='position:relative;'>
          <span class='pageTitle shadowed' style='position:absolute; left: 0.7em; top: 0.7em;'>Your Privacy Settings</span>
        </div>
        <div id='accessBody' class='shadowed'>
          <div id='leftContainer'>
            <form id='privacyForm' name="privacyForm" enctype="multipart/form-data">
              <div id='accessPermissions' style='position:relative;'>
                <span class="headerText" style='margin-bottom:10em;'>Permissions: Define how others interact with your profile</span><br>
                
                <div id='publicOpts' style='position:relative;height:50px;'>
                  <label for='public' class='headerText'>the Public:</label>
                  <!-- <input class='normalText'id="public" type="text" style="border:0; width:25em" readonly></input> -->
                  <div id='scrollBar' style='position:absolute;height:100px;'>
                    <div class="normalText" style="position:absolute;color:green;width:7em;">Less Access</div>
                    <div id="publicperms" style="position:absolute;width:25em;left:6.3em;top:3px;"></div><input name="pubval" id="val1" type="hidden"/>
                    <div class="normalText" style="position:absolute;left:37em;color:red;width:7em;">More Access</div>
                  </div>
                  <div id="publicDef" class="shadowed-gray normalText textBubble" style="position:absolute;left:47em;width:40em;">
                    People who are <span style="color:red">not</span> logged into ResMe fall into this category. Most of the time, this group will include people that just want to view
                    your resume but not interact with it. 
                    <span class="boldNormalText">It's recommended you set this to at least "View Profile" so you can hand out your ResMe link.</span>
                  </div>
                </div>
                <div id="public" class="shadowed-gray normalText textBubble" style="position:relative;width:90%;margin-bottom:10px;"></div>
                
                <div id='resmeOpts' style='position:relative;height:50px;'>
                  <label for="resme" class='headerText'>resMe Users:</label>
                  <!-- <input class='normalText' id="resme" type="text" style="border:0; width:25em" readonly></input> -->
                  <div id='scrollBar' style='position:absolute;height:100px;'>
                    <div class="normalText" style="position:absolute;color:green;width:7em;">Less Access</div>
                    <div id="resmeperms" style="position:absolute;left:6.3em;width:25em;top:3px;"></div><input name="resval" id="val2" type="hidden"/>
                    <div class="normalText" style="position:absolute;left:37em;color:red;width:7em;">More Access</div>
                  </div>
                  <div id="resmeDef" class="shadowed-gray normalText textBubble" style="position:absolute;left:47em;width:40em;">
                    People who are logged into ResMe fall into this category. This group is most likely to leave comments and interact with your resume.
                  </div>
                </div>
                <div id="resme" class="shadowed-gray normalText textBubble" style="position:relative;width:90%;margin-bottom:10px;"></div>
              </div> 
              <!-- end accessPermissions -->

              <div id='searchPermissions' style='position:relative;margin-bottom:0.49em;height:6.3em;'>
                <span class="headerText"  style='position:relative;margin-bottom:0.49em;font-size:0.90625em;'>Search: Define how your profile interacts with others:</span><br>
                <label for="resmeSearch" class='headerText'>Include your profile in resMe search?</label>
                <div id="resmeSearch">
                  <span class='normalText'>
                    <input type="radio" id="resmeyes" name="resmeSearch" value="yes"/><label for="resmeyes">Yes</label>
                    <input type="radio" id="resmeno" name="resmeSearch" value="no"/><label for="resmeno">No</label>
                  </span>
                </div>
                <label for="publicSearch" class='headerText'>Include your profile in public search(Google, Yahoo, Bing, etc.)?</label>
                <div id="publicSearch">
                  <span class='normalText'>
                    <input class='normalText' type="radio" id="publicyes" name="publicSearch" value="yes" disabled/><label for="publicyes">Yes</label>
                    <input class='normalText' type="radio" id="publicno" name="publicSearch" value="no" disabled/><label for="publicno">No</label>
                  </span>
                </div>
              </div> 
              <!-- end searchPermissions -->
              
              <div id='buttons' style='position:relative;margin-bottom:0.49em;height:1.6em;width:90%;text-align:right;'>
                <button class='normalText' type="button" onclick="window.location='<?php echo "/".$_SESSION["username"];?>'">Cancel</button>
                <button class='normalText' id='submit' type="button" name="update" id='submit'>Update</button>
              </div>
            
              <div id='loading' style='position:relative;height:2em;width:100%;'>
                  <img id='loader' src='/public/images/loader.gif'/><label for='loader' class='headerText' style='float:right;width:93%;padding:0.35em;'>Updating Information...</label>
              </div>
              <div id="output"></div>
            </form> 

          </div>
        </div>  <!-- end accessBody -->
      </div> <!-- end pageBody -->
    
    <!-- copyright -->			
		<div id='pageFooter' style='position:absolute;bottom:0' >
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
    </div>
  </body>
</html>
