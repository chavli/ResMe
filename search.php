<?php
  date_default_timezone_set("America/New_York");
  require_once($_SERVER["DOCUMENT_ROOT"]."/system/php/utilities.php");
  require_once($_SERVER["DOCUMENT_ROOT"]."/backend/session_handler.php");
	new SessionHandler();
	session_start();
	util_session_check();
?>

<html>
	<head>
		<title>ResMe[search]</title>
    <link rel="icon" type="image/x-ico" href="/public/images/resme.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="/public/images/resme.ico" />    
    <link rel='stylesheet' href='/public/css/jquery-ui-1.8.10.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/pagelayout.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/sitemenu.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/search.css' type='text/css' media='screen'/>
    <link rel='stylesheet' href='/public/css/fonts.css' type='text/css' media='screen'/>    
    <script type="text/javascript" src="/public/js/jquery-core-1.5.js"></script>
    <script type="text/javascript" src="/public/js/jquery-ui-1.8.10.js"></script>
    <script type="text/javascript" src="/public/js/jquery-search.js"></script>
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
              else{
                echo '<span class="normalText">
                  <a href="/home/">Home Page</a>
                  <a href="/search/">Search</a>
                  <a href="/home/">Contact Us</a>
                  <a href="/search/">About</a>
                 </span>';
              }
            ?>
          </div>      
      </div>
    </div> <!-- end pageHeader -->
    <div id='pageBody'>
      <div id='searchHeader'>
        <span class='pageTitle shadowed' style='position:absolute; left: 10px; top: 10px;'>Instant Search</span>
      </div>
      <div id='searchBody'>
        <div id='leftContainer' class='shadowed'>
      		<!-- <div id='searchResults'> -->
	       	 	<form	id='searchForm' action='/search/' method='GET' enctype='multipart/form-data'>
							<div style='position:relative;margin-bottom:5px;'>
								<label for='profs' class='headerText' >Profession(s):</label>
	 		       	  <select class='fieldInput' id='profs' name='profs[]' style='width:40%;' multiple>
 	        	   		<?php
 	        	     		//parse the xml list of professions and display it in html
 	        	     		$xmlfile = "system/xml/professions.xml";
  	      	      	$parsedxml = util_xmlfile_to_array($xmlfile);
  		           	 		$i = -1; $output = "";
   		         	  	foreach($parsedxml as $entry){
   	           	  		if(strcmp($entry["tag"], "PROFESSION") == 0){
   	         	      		$output .= "<option value=".++$i.">".$entry["value"]."</option>";
											}
    	      	    	}
      	  	      	echo $output;
      	      		?>            
    	      		</select>
								<span class='minorText fieldInput'>(hold down 'ctrl' to select multiple items)</span>
							</div>
							<div style='position:relative;margin-bottom:5px;'>
								<label for='restypes' class='headerText'>Resume Type(s):</label>
 		       	  	<select class='fieldInput' id='restypes' name='restypes[]' style='width:40%;'multiple>
         	   			<?php
         	   	  		//parse the xml list of professions and display it in html
         	   	  		$xmlfile = "system/xml/resumetypes.xml";
        	   		   	$parsedxml = util_xmlfile_to_array($xmlfile);
             		 		$i = -1; $output = "";
            		  	foreach($parsedxml as $entry){
	              		  	if(strcmp($entry["tag"], "RESUMETYPE") == 0){
            	 		     	$output .= "<option value=".++$i.">".$entry["value"]."</option>";
											}
          	   	 		}
        	      		echo $output;
      	      		?>            
    	      		</select>
								<span class='minorText fieldInput'>(hold down 'ctrl' to select multiple items)</span>
							</div>
	        	</form>
						<div id='searchResults'>
						</div>
          <!-- </div> -->
        </div>
      </div> <!-- end searchBody -->
    </div> <!-- end pageBody -->
		<div id='pageFooter' style='position:absolute;' >
			<div id='names'>
        <span class='footerText'>ResMe -- Copyright 2010, <?php echo date('Y'); ?></span>
			</div>
		</div>

	</body>
</html>
