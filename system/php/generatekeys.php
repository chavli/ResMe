<?php
	date_default_timezone_set('America/New_York');
	require("credentials.php");
	require("SQLConnection.php");
	
	$chars = "0123456789abcdefghijklmnopqrstuvwxyz";
	$length = 20;
	$tocreate = 10;
	mt_srand(time());

	$sql_conn = new SQLConnection;	
	$sql_conn->establish();
	
	print "Generated Keys:\n";

	for($i = 0; $i < $tocreate; $i++){
		$key = "";
		for($j = 0; $j < $length; $j++){
			$key .= $chars[mt_rand(0, strlen($chars)- 1)];
		}
		$query = "insert into `accesskey` values(null, '".$key."', 0);";
		print $key."\n";
		$qra = mysql_query($query);
	}
	
	$sql_conn->disconnect();
	print "\n";

?>
