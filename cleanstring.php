<?php

function cleanString($string) {
	$string = str_replace("&quot;","\"",$string);
	$string = str_replace("&apos;","'",$string);
	$string = str_replace("Â"," ",$string);
	$string = str_replace("\n"," ",$string);
	$string = preg_replace('/[^a-zA-Z0-9-.:&#;,\/_\'…é"!’£?\[\]]/', ' ', $string);
	$string = preg_replace('/\s\s+/', ' ', $string);
	return $string;
}

function cleanQuery($string) {
	$string = str_replace("\\'","'",$string);
	$string = str_replace(" '","'",$string);
	$string = str_replace("\\\"","\"",$string);
	//$string = str_replace(".","",$string);
	$string = trim($string);
	return $string;
}

function cleanLine($string) {
	$string = str_replace("“","\"",$string);
	$string = str_replace("”","\"",$string);
	return $string;
}

function cleanGoogle($string) {
	$string = trim($string);
	$string = str_replace(" ","%20",$string);
	$string = str_replace("\"","",$string);
	$string = str_replace(".","",$string);
	$string = trim($string);
	return $string;
}

function cleanOutputVar($string) {
	$string = str_replace("\n","<br />",$string);
	$string = str_replace("\"","\\\"",$string);
	$string = str_replace("'","\\'",$string);
	return $string;
}

function cleanBrackets($string) {
	$string = str_replace("<","&lt;",$string);
	$string = str_replace(">","&gt;",$string);
	return $string;
}

function addDisclaimer($qlineout,$urlout) {
	$disclaimer = "---";
	$qlineout = $qlineout."\n".$disclaimer."";
	return $qlineout;
}

function checkEndOfCard($card,$first,$last) {
	$lastNeedle = substr($last,0,3);
	$cardLength = strlen($card);
	$out = $card;
	$done = false;
	for ($i=3;$i<$cardLength;$i++) {
		if(!$done) {
			$check = substr($card,$cardLength-$i,3);
			if (strcmp($lastNeedle,$check) == 0) {
				$newCard = substr($card,0,$cardLength-$i);
				$out = $newCard . $last;
				$done = true;
			}
		}
	}
	return $out;
}

?>