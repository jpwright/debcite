<?php

include_once("googleanalytics.php");
echo "<a href=\"http://www.jpwright.net/debcite\"><img src=\"http://www.jasonline.net/debcite/debcite_logo.png\" \></a>";
echo "<hr />";
echo "<a href=\"about.php\">about</a> | <a href=\"login.php\">login</a> | <a href=\"register.php\">register</a>";
if (isset($_SESSION['user'])) {
	echo " | logged in as: ".$_SESSION['user']." | <a href=\"preferences.php\">preferences</a>";
}
echo "<hr />";
echo "<div id=\"main\">";

?>