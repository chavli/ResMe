<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
  new SessionHandler();
	session_start();
  util_session_check();

  //if not logged in, redirect to index.php
  if(!isset($_SESSION["LAST_ACTIVITY"])){
    header("Location: /");
  }
?>
<html>
  <head>  
    <title>ResMe[Home]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-custom-button-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>    
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/home.css' type='text/css' media='screen'/>
    <script type='text/javascript' src='/public/js/jquery-core-1.5.js'></script>
    <script type='text/javascript' src='/public/js/jquery-ui-1.8.10.js'></script>
    <script type='text/javascript' src='/public/js/jquery-sitemenu.js'></script>    
    <script type='text/javascript' src='/public/js/jquery-home.js'></script>
		<!-- widgets --> 
    <script type='text/javascript' src='/public/js/widgets/ThumbContainer.js'></script>
    <script type='text/javascript' src='/public/js/widgets/UrlElement.js'></script>
    <script type='text/javascript' src='/public/js/widgets/TextElement.js'></script>
		<!-- js files for each tab -->
    <script type='text/javascript' src='/public/js/jquery-home-articles.js'></script>
    <script type='text/javascript' src='/public/js/jquery-home-notifications.js'></script>
    <script type='text/javascript' src='/public/js/jquery-home-resnews.js'></script>
    <script type='text/javascript' src='/public/js/jquery-home-resumes.js'></script>
    <script type='text/javascript' src='/public/js/jquery-home-stack.js'></script>

  </head>
  <body id="<?php echo $_SESSION['username'];?>"> 
    <div id="pageHeader" class="shadowed">
      <div id="menu" class="normalText" >Menu</div> 
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
      <div id='homeHeader'>
        <span class='pageTitle shadowed' style='position:absolute; left: 120px; top: 10px;'><?php echo $_SESSION['firstname']." ".$_SESSION['lastname'] ?>'s Frontpage</span>
      </div>
      <div id='homeBody'>
        <div id='leftContainer'>
          <div id='tabs' class='verticalTabs headerText' style='position:absolute;'>
            <div class='tab shadowed' id='resnews' style='position:relative;'>ResMe News</div>
            <div class='tab shadowed' id='notifications' style='position:relative;'>Notifications (<span id='quantity'></span>)</div>
            <div class='tab shadowed' id='usernews' style='position:relative;'>User Articles</div>
            <div class='tab shadowed' id='resumes' style='position:relative;'>Explore Resumes</div>
            <div class='tab shadowed' id='stack' style='position:relative;'>Your Stack</div>
          </div>
         	<div id='tabContainer' class='shadowed'>
            <form id='submissionForm'>
							<div id='output'></div>
              <div class='normalText'>Submit an article</div>
              <div id='details'>
                <!-- url input -->
                <div style='position:relative;margin-bottom:0.4em;' class='normalText'>
                  <label for='url' class='fieldLabel'>URL:</label>
                  <input id='url' class='fieldInput' name='url' type='text' value='http://'>
                </div>
                
								<!-- title input -->
                <div style='position:relative;margin-bottom:0.4em;' class='normalText'>
                  <label for='title' class='fieldLabel' >Title:</label>
                  <input id='title' class='fieldInput' name='title' type='text'/>
									<span id='tcharcount' style='margin-left:10px;'></span>
                </div>

                <!-- description input -->
                <div style='position:relative;margin-bottom:0.4em;' class='normalText'>
                  <label for='description' class='fieldLabel'>Description:</label>
                  <input id='description' class='fieldInput' name='description' type='text'/>
									<span id='dcharcount' style='margin-left:10px;'></span>
                </div>
                
                <!-- category selection -->
                <div style='position:relative;margin-bottom:0.4em;'>
                  <label for='categories' class='fieldLabel normalText'>Categories:</label>
                  <div class='normalText' id='categories'>
                    <input type='checkbox' id='jobs' name='jobs'/><label for='jobs'>Job Market</label>
                    <input type='checkbox' id='business' name='business'/><label for='business'>Business</label>
                    <input type='checkbox' id='economy' name='economy'/><label for='economy'>Economy</label>
                    <input type='checkbox' id='advice' name='advice'/><label for='advice'>Advice/Tips</label>
                  </div>
                </div>
              </div>
              
              <!-- form buttons -->
              <div style='position:relative;;text-align:right;width:100%;'>
                <button class='normalText' type='reset' id='reset'>Reset</button>
                <button class='normalText' type='button' id='submitArticle'>Submit</button>
              </div>
            </form>
						<div id='tabContents'>

            </div>
					</div>
        </div>
      </div> <!-- end homeBody -->
    </div> <!-- end pageBody -->
    <div id='copyright' style='position:absolute;'>
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
    </div>
  </body>
</html>
