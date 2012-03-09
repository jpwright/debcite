<?php

function getUrlFromCite($line) {
	$remove = array("(",")","[","]","<",">",",");
	for ($i=0;$i<count($remove);$i++) {
		$line = str_replace($remove[$i]," ",$line);
	}
	
	$words = explode(" ",$line);
	
	$out = "";
	
	$matches = array("http","www",".com",".net",".org",".gov",".edu",".mil",".co.uk",".htm",".php",".asp",".pdf",".doc",".txt",".cfm");
	for ($j=0;$j<count($words);$j++) {
		for ($k=0;$k<count($matches);$k++) {
			if (strpos(strtolower($words[$j]),strtolower($matches[$k])) != false) {
				$out = trim($words[$j]);
			}
		}
	}
	return $out;
}

function getUrlFromCite_old($line) {
	//echo "getting URL!<br />";
	//echo "line: ".$line."<br />";
	$matches = array(" ",")","]",">");
	$start = strpos($line,"http");
	//echo "start: ".$start."<br />";
	$end = 0;
	$found = false;
	for ($i=(int)$start + 1;$i<strlen($line);$i++) {
		//echo "char #: ".$i.", ".$line{$i}."<br />";
		if (in_array($line{$i},$matches)) {
			if($end == 0) {
				//echo "yes!<br />";
				$end = $i - 1;
				$found = true;
			}
			//echo "done already!<br />";
		} else {
			//echo "no!<br />";
		}
	}
	if(!$found) {
		$end = strlen($line) - 1;
	}
	//echo "end: ".$end."<br />";
	$url = trim(substr($line,$start,($end-$start)+1));

	//remove trailing commas
	if(strcmp($url{strlen($url)-1},",")==0) {
		$url = substr($url,0,strlen($url)-1);
	}
	return $url;
}

function getTitleFromCite($line) {
	//echo "getting title";
	$matches = array("\"","'");
	$start = 0;
	for ($i=0;$i<strlen($line);$i++) {
		//echo "char #: ".$i.", ".$line{$i}."<br />";
		if (in_array($line{$i},$matches)) {
			if($start == 0) {
				//echo "yes!<br />";
				$start = $i;
			}
			//echo "done already!<br />";
		} else {
			//echo "no!<br />";
		}
	}
	//echo "start: ".$start."<br />";
	$end = 0;
	for ($i=(int)$start + 1;$i<strlen($line);$i++) {
		//echo "char #: ".$i.", ".$line{$i}."<br />";
		if (in_array($line{$i},$matches)) {
			if($end == 0) {
				//echo "yes!<br />";
				$end = $i - 1;
			}
			//echo "done already!<br />";
		} else {
			//echo "no!<br />";
		}
	}
	//echo "end: ".$end."<br />";
	$title = substr($line,$start+1,($end-$start));
	//echo $title . "<br />";
	return $title;
}
function getFirstLast($line, $cust) {
	$delimiters = array("…","...","AND",$cust);
	$firstLast = false;
	for ($i=0;$i<count($delimiters);$i++) {
		if (strpos($line,$delimiters[$i]) != false) {
			$firstLast = true;
		} 
	}
	return $firstLast;
}

?>