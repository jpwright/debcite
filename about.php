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
?>

<h3>what is debcite?</h3>
<p><i>debcite</i> is a citation retrieval engine to make retrieving evidence from the <a href="http://68.233.253.124/xwiki/wiki/opencaselist/view/Cornell/WebHome">public wiki</a> for intercollegiate policy debates easier and more efficient. It parses shorthand citations and uses a variety of techniques, including the Google and Yahoo search APIs, to try to retrieve the full text of the evidence being cited.</p>
<h3>give me an example</h3>
<p>Here's what the process of using debcite typically looks like:</p>
<p style="text-align:center;">
<b>1. Find citations using OpenCaselist, or similar.</b><br />
<img src="images/faq_1.png" />
</p>
<p style="text-align:center;">
<b>2. Copy citation into debcite and search.</b><br />
<img src="images/faq_2.png" />
</p>
<p style="text-align:center;">
<b>3. Receive full text.</b><br />
<img src="images/faq_3.png" />
</p>
<p>You can also run a bunch of cites at once for batch processing.</p>
<h3>who are you?</h3>
<p>I'm <a href="http://www.jpwright.net">Jason Wright</a>. I debated at Cornell University and twice qualified for the National Debate Tournament.</p>
<?php 
include 'footer.php';
?>
</body>
</html>