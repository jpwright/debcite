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

function isValidEmail($email){ 
	$pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$"; 
	if (eregi($pattern, $email)){ 
	 return true; 
	} 
	else { 
	 return false; 
	}    
} 

$user = $_POST['user'];
$pass = $_POST['pass'];
$email = $_POST['email'];

$error = "";
$errorExist = false;

mysql_connect($sqlserver,$username,$password);
@mysql_select_db($database) or die( "Unable to select database");

$query = "SELECT * FROM users WHERE user='".$user."'";
$result = mysql_query($query) or die("Failed: $query"); //Stops the script if the query failed
$numrows = mysql_num_rows($result); //The number of rows the query returned

if($numrows > 0) {
	$errorExist = true;
	$error = $error . "Username already exists. ";
}

if(strlen($pass) < 6) {
	$errorExist = true;
	$error = $error . "Password is too short (must be at least 5 characters). ";
}

if(!isValidEmail($email)) {
	$errorExist = true;
	$error = $error . "Invalid email. ";
}

if(!$errorExist) {
	$query = "INSERT INTO users (user,pass,email,usefirst,maxdistance,google,yahoo) VALUES ('".$user."','".md5($pass)."','".$email."','o','15','o','o')";
	$result = mysql_query($query) or die("Failed: $query"); //Stops the script if the query failed
	echo "<h3>Success: User ".$user." created</h3>";
} else {
	echo "<h3>Error(s): ".$error."</h3>";
}

?>

</body>
</html>