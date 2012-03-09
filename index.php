<?php
session_start();
?>
<html>
<head>
<?php
session_start();
include 'style.php';
?>
</head>
<body>
<?php 
include 'header.php';
?>
<div id="searchbox">
<form enctype="multipart/form-data" action="search.php" method="POST">
<textarea cols="100" rows="20" name="query"></textarea><br />
<input type="checkbox" name="debug" />debug mode on<br />
<input type="submit" value="Search" class="button" />
</form>
</div>
<p>
<?php
$filename = "cardsfound.txt";
$fh = fopen($filename, 'r');
$cardsfound = fread($fh, filesize($filename));
fclose($fh);
$filename = "cardsmissed.txt";
$fh = fopen($filename, 'r');
$cardsmissed = fread($fh, filesize($filename));
fclose($fh);
$percentage = $cardsfound . " cards found & ".$cardsmissed." cards missed = ".(100*$cardsfound/($cardsfound+$cardsmissed))."%";
$p = fopen("stats.txt",w);
fwrite($p,$percentage);
fclose($p);
?>
</p>
<p style="text-align: left;"><h3>alpha release notes:</h3>
if cite isn't working but should, try this
<p>
- make sure cite boundaries are delimited by ... or AND (all caps)<br />
- make sure nothing else in the cite has AND in all caps (e.g. LINK AND IMPACT, RAND Corporation will cause errors for the time being)<br />
- make sure the URL in the cite does not have unintentional spaces in it (this is an occasional bug when you copy/paste from opencaselist, working on it)<br />
- pdf support is extremely temperamental, so if the only location is a pdf that Google doesn't cache things are likely to fail.<br />
- the script occasionally terminates early if you try to give it a lot of difficult cards at once (e.g. PDFs or long cards) - if this happens, try one or two at a time.<br />
</p>
known bugs
<p>
- strange characters appear when reading certain PDFs<br />
- occasional script termination.<br />
- occasionally, the "results" thing at the top doesn't update on time even when the script does terminate - try looking for "final output" at the bottom of the trace.<br />
</p>
features in progress
<p>
- logins<br />
- lexis, muse, jstor et al credential saving<br />
- better PDF support (anyone know any good libraries for pdf2text? Command line stuff (like xpdf) is not feasible because they can't remotely access files.)<br />
- implementation of various other search APIs (google news, etc)<br />
- implementation of various other access APIs (docstoc, etc)<br />
- quals finding<br />
- better title finding<br />
- perhaps something that looks up titles in a library to tell you if a card is book-only<br />
- advanced search/config page<br />
- archive htmltext (if you have multiple cards from a PDF that takes a while to load, it wastes time reading and converting it for each card)<br />
</p>
</p>
<?php 
include 'footer.php';
?>
</body>
</html>