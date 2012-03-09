<?php
session_start();
?>
<html>
<head>
<?php
include 'style.php';
?>
</head>
<body>
<?php 
include 'header.php';

if(isset($_SESSION['user'])) {
	echo "<h3>Already logged in as ".$_SESSION['user']."</h3>";
	echo "<a href=\"logout.php\">Logout</a>";
} else {
	echo "<h3>Login</h3>";
	echo "<form enctype=\"multipart/form-data\" action=\"login_done.php\" method=\"POST\">";
	echo "<table style=\"margin:auto;\">";
	echo "<tr><td>Username</td><td><input type=\"text\" name=\"user\" /></td></tr>";
	echo "<tr><td>Password</td><td><input type=\"password\" name=\"pass\"></td></tr>";
	echo "</table><br />";
	echo "<input type=\"submit\" value=\"Login\" class=\"button\"/>";
	echo "</form>";
}

?>
<?php 
include 'footer.php';
?>
</body>
</html>