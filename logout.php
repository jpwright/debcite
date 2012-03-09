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
	unset($_SESSION['user']);
	echo "<h3>Logged out!</h3>";
}

?>

</body>
</html>