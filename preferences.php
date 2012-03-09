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

function isValidEmail($email){ 
	$pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$"; 
	if (eregi($pattern, $email)){ 
	 return true; 
	} 
	else { 
	 return false; 
	}    
} 

$user = $_SESSION['user'];
$error = "";
$errorExist = false;

if(isset($_SESSION['user'])) {

	mysql_connect($sqlserver,$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");

	if(isset($_POST['email'])) {
		if(!isValidEmail($_POST['email'])) {
			$errorExist = true;
			$error = $error . "Invalid email. ";
		}
		if(!is_numeric($_POST['maxdistance'])) {
			$errorExist = true;
			$error = $error . "Maximum Levenshtein distance must be a number. ";
		}
		if (!$errorExist) {
			$query = "UPDATE users SET email='".$_POST['email']."', usefirst='".$_POST['usefirst']."', maxdistance='".$_POST['maxdistance']."', google='".$_POST['google']."', yahoo='".$_POST['yahoo']."', custdelim='".$_POST['custdelim']."', custmark='".$_POST['custmark']."' WHERE user='".$_SESSION['user']."'";
			mysql_query($query);
			echo "<div style=\"text-align:center;\"><h3>Updated.</h3></div>";
		} else {
			echo "<h3>Error(s): ".$error."</h3>";
		}
	}
	if(isset($_POST['pass']) && strlen($_POST['pass']) > 0) {
		$query = "UPDATE users SET pass='".md5($_POST['pass'])."' WHERE user='".$_SESSION['user']."'";
		mysql_query($query);
		echo "<h3>Password updated.</h3>";
	}

	$query = "SELECT email,usefirst,maxdistance,google,yahoo,custdelim,custmark FROM users WHERE user='".$user."'";
	$result = mysql_query($query) or die("Failed: $query"); //Stops the script if the query failed
	$numrows = mysql_num_rows($result); //The number of rows the query returned

	$result_row = mysql_fetch_row($result);
	$email = $result_row[0];
	$usefirst = $result_row[1];
	$maxdistance = $result_row[2];
	$google = $result_row[3];
	$yahoo = $result_row[4];
	$custdelim = $result_row[5];
	$custmark = $result_row[6];

	echo "<form enctype=\"multipart/form-data\" action=\"preferences.php\" method=\"POST\">";
	echo "<table style=\"margin:auto;\">";
	echo "<tr><td>Email</td><td><input type=\"text\" name=\"email\" value=\"".$email."\" /></td></tr>";
	echo "<tr><td>Use First Card Found</td><td><input type=\"checkbox\" name=\"usefirst\" ";
	if ($usefirst == 'o') {
		echo "checked";
	}
	echo " /></td></tr>";
	echo "<tr><td>Maximum Levenshtein Distance<br /><span style='font-size:small;'>This is the allowed variance between cite and full text.<br />Turn it up if you're having trouble finding things.</span></td><td><input type=\"text\" name=\"maxdistance\" value=\"".$maxdistance."\" /></td></tr>";
	echo "<tr><td>Use Google API</td><td><input type=\"checkbox\" name=\"google\" ";
	if ($google == 'o') {
		echo "checked";
	}
	echo " /></td></tr>";
	echo "<tr><td>Use Yahoo API</td><td><input type=\"checkbox\" name=\"yahoo\" ";
	if ($yahoo == 'o') {
		echo "checked";
	}
	echo " /></td></tr>";
	echo "<tr><td>Custom Delimiters<br /><span style='font-size:small;'>These are what separates the first and last words of a card.</span></td><td><input type=\"text\" name=\"custdelim\" value=\"".$custdelim."\" /></td></tr>";
	echo "<tr><td>Custom Marker<br /><span style='font-size:small;'>This is added to all your cards.</span></td><td><input type=\"text\" name=\"custmark\" value=\"".$custmark."\" /></td></tr>";
	echo "<tr><td>New Password</td><td><input type=\"password\" name=\"pass\"></td></tr>";
	echo "</table><br />";
	echo "<div style=\"text-align:center;\"><input type=\"submit\" value=\"Submit\" class=\"button\" /></div>";
	echo "<input type=\"hidden\" name=\"submitted\" value=\"true\" />";
	echo "</form>";
} else {
	echo "<h3>You are not logged in!</h3>";
	echo "<a href=\"login.php\">login</a> or <a href=\"register.php\">register</a>";
}

?>
<?php 
include 'footer.php';
?>
</body>
</html>