<?php
	session_start();

	print_r($_SESSION);
	echo "<br>";
	session_destroy();
	session_unset();
?>

<html>
	<head>
		<title>myRes[fatal error]</title>
	</head>
	<body>
		If you see this, it means you found a very bad bug in my code. You should probably tell me exactly what you did before you ruined everything -> chavli@gmail.com
	</body>
</html>
