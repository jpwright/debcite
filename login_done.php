<?php
session_start();
?>
<html>
<head>
<?php
include 'style.php';
include 'settings.php';
?>
</head>
<body>
<?php 
include 'header.php';
?>

<?php 

$user = $_POST['user'];
$pass = $_POST['pass'];

$error = "";
$errorExist = false;

mysql_connect($sqlserver,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

$query = "SELECT pass FROM users WHERE user='".$user."'";
$result = mysql_query($query) or die("Failed: $query"); //Stops the script if the query failed
$numrows = mysql_num_rows($result); //The number of rows the query returned

if($numrows < 1) {
	$errorExist = true;
	$error = $error . "Username not found. ";
}

$result_row = mysql_fetch_row($result);
$hash = $result_row[0];

if(strcmp($hash,md5($pass))!=0) {
	$errorExist = true;
	$error = $error . "Incorrect password. ";
}

if(!$errorExist) {
	//session_start();
	$_SESSION['user'] = $user;
	echo "<h3>Success: User ".$_SESSION['user']." logged in</h3>";
} else {
	echo "<h3>Error(s): ".$error."</h3>";
}

?>

</body>
</html>