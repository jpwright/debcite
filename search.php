<?php
session_start();
?>
<html>
<head>
<?php
//set_error_handler(all);
$time_start = microtime_float();
error_reporting(0);
set_time_limit(0);
include 'style.php';
?>
</head>
<body>
<?php 
include 'header.php';
include 'pdf2text.php';
include 'pdf2txt.php';
include 'cleanstring.php';
include 'citeparse.php';
include 'urlparse.php';
include 'settings.php';
require_once 'class.html2text.inc'; 
require_once 'class.pdf2text.php';
require_once 'class.url.php';
require_once 'docx/PHPWord.php';
?>
<script type="text/javascript">
function updateStatus(status){
	document.getElementById('status').innerHTML = status;
}
function updateResult(result){
	document.getElementById('result').innerHTML = result;
}
function updateError(error){
	var oldError = document.getElementById('error').innerHTML;
	document.getElementById('error').innerHTML = oldError + error + '<br />';
}
</script>

<div class="status">
<b id='status'>status</b>
</div>
<div class="error">
<span id='error'></span>
</div>
<div class="result">
<span id='result'>result</span>
</div>
<br />
<?
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function googleSearch($searchquery,$debug) {
	include 'settings.php';
	$node = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=";

	$url = $node . $searchquery;
	$url = $url . "&key=". $googleApiKey;
	$url = $url . "&rsz=large";
	// $url = $url . "&userip=USERS-IP-ADDRESS";
	
	if($debug) {
		echo "<p><h3>api url</h3>".$url."</p>";
	}
	// sendRequest
	// note how referer is set manually
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, "http://www.jasonline.net/debcite");
	curl_setopt($ch, CURLOPT_URL, $url);
	$body = curl_exec($ch);
	curl_close($ch);
	// now, process the JSON string
	$json = object2array(json_decode($body));
	// now have some fun with the results...
		
	echo "<script language=javascript>updateStatus('Interpreting Google search results: ".str_replace("%20"," ",$searchquery)."')</script>";

	$responseData = object2array($json['responseData']);

	$results = object2array($responseData['results']);
	if($debug) {
		echo "<p><h3>google results</h3>".var_dump($results)."</p>";
		echo "<p><h3>google response data</h3>".var_dump($responseData)."</p>";
	}

	return $results;
}

function yahooSearch($searchquery,$debug) {

	$searchquery = str_replace("\"","%22",$searchquery);
	$searchquery = str_replace("’","%E2%80%99",$searchquery);

	include 'settings.php';
	
	$node = "http://boss.yahooapis.com/ysearch/web/v1/";
	$url = $node . $searchquery;
	$url = $url . "?appid=".$yahooAppId;
	$url = $url . "&format=xml";
	
	if($debug) {
		echo "<p><h3>yahoo api url</h3>".$url."</p>";
	}
	// sendRequest
	// note how referer is set manually
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, "http://www.jpwright.net/debcite");
	curl_setopt($ch, CURLOPT_URL, $url);
	$body = curl_exec($ch);
	curl_close($ch);
	
	require_once('class.xml2array.php');
	
	$xmlObj = new XmlToArray($body);
	$xmlArray = $xmlObj->createArray();
	
	$statusquery = str_replace("%20"," ",$searchquery);
	$statusquery = str_replace("%22","\"",$statusquery);
	$statusquery = str_replace("%E2%80%99","'",$statusquery);
	echo "<script language=javascript>updateStatus('Interpreting Yahoo search results: ".$statusquery."')</script>";
	
	if($debug) {
		echo "<p><h3>yahoo results</h3>".print_r($xmlArray)."</p>";
	}

	$ysearchresponse = $xmlArray['ysearchresponse'];
	$resultset_web = $ysearchresponse['resultset_web'];
	$zero = $resultset_web[0];
	$results = $zero['result'];
	return $results;
}
	
function object2array($object) {
    if (is_object($object)) {
        foreach ($object as $key => $value) {
            $array[$key] = $value;
        }
    }
    else {
        $array = $object;
    }
    return $array;
}

function url_exists($strURL) {
    $resURL = curl_init();
    curl_setopt($resURL, CURLOPT_URL, $strURL);
    curl_setopt($resURL, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($resURL, CURLOPT_HEADERFUNCTION, 'curlHeaderCallback');
    curl_setopt($resURL, CURLOPT_FAILONERROR, 1);

    curl_exec ($resURL);

    $intReturnCode = curl_getinfo($resURL, CURLINFO_HTTP_CODE);
    curl_close ($resURL);

    if ($intReturnCode != 200 && $intReturnCode != 302 && $intReturnCode != 304) {
       return false;
    } else {
        return true ;
    }
}

function parseResultSet($results,$cardarray,$urlarray,$first_clean,$last_clean,$debug) {
	//echo "parsing result set with cardarray length " . count($cardarray);
	include 'settings.php';
	$foundGoodCard = false;
	for ($i=0;$i<count($results);$i++) {
		if (!$foundGoodCard || !$useFirst) {
			$site = object2array($results[$i]);
			$url = urlClean($site['url']);
			if($debug) {
				echo $url . "<br />";
			}
			if($site['cacheUrl'] != null) {
				$cacheUrl = urlClean($site['cacheUrl']);
			} else {
				$cacheUrl = null;
			}
			//echo "the Resultset URL is: ".$url."<br />";
			$htmltext = parseUrl($url,$cacheUrl,$debug);
			if($debug) {
				echo "<p><h3>HTML Text: Length ".strlen($htmltext)."</h3></p>";
				echo "<p>".$htmltext."</p>";
			}
			$needle = findCard($htmltext,$first_clean,$last_clean,$debug);
			if($debug) {
				echo "<p><h3>Exact Match Card</h3>".$needle."</p>";
			}
			if(strcmp($needle,$cardError) == 0) {
				$needle = findCardByDistance($htmltext,$first_clean,$last_clean,$debug);
			}
			
			if($debug) {
				echo "<p><h3>Deep Search Card</h3></p>";
				echo "<p>".$needle."</p>";
				//echo "updating card array at point ".count($cardarray);
			}
			
			$cardarray[count($cardarray)] = $needle;
			$urlarray[count($urlarray)] = $url;
			
			if ((strpos($needle,$cardError) == false) && (strlen($needle) > 50)) {
				if($debug) {
					echo "<h1>Found good card</h1>";
				}
				$foundGoodCard = true;
			}
		}
	}
	$returnvar = array('foundGoodCard' => $foundGoodCard, 'cardarray' => $cardarray, 'urlarray' => $urlarray);
	return $returnvar;
} 

function parseUrl($url,$cacheUrl,$debug) {
	echo "<script language=javascript>updateStatus('Contacting url: ".$url."')</script>";
	$html = fopen($url,"r");
	$htmltext = "";
	$pdfRead = false;
	if (strpos($url,".pdf") != false) {
		//$htmltext = readPDF($url);
		if($debug) {
			echo "pdf in url<br />";
		}
		$pdfRead = true;
	} else {
		if($debug) {
			echo "no pdf in url<br />";
		}
		if (strpos(fgets($html),"PDF") != false) {
			if($debug) {
				echo "pdf in first line<br />";
			}
			$pdfRead = true;
		}
	}
	$banned = checkBanned($url);
	
	if($pdfRead) {
		if($debug) {
			echo "its a pdf<br />";
		}
		//echo "the cache URL is " . $site['cacheUrl'] . "!<br />";
		if ($cacheUrl != null) {
			if($debug) {
				echo "using cache url<br />";
			}
			// Fixes html newline error for Google PDF cache
			$fixedCache = str_replace("</nobr>","***addspace***",readHtml($cacheUrl));
			if (strpos($url,".txt") == false) {
				$htmltext = html2text2($fixedCache);
			} else {
				$htmltext = $fixedCache;
			}
			$htmltext = str_replace("***addspace***"," ",$htmltext);
		} else {
			if($debug) {
				echo "not using cache url<br />";
			}
			$htmltext = readPDF($url);
		}
	} else {
		if($debug) {
			echo "its not a pdf<br />";
		}
		if (!$banned) {
			$htmltext = str_replace("\n","***addspace***",readHtml($url));
			$htmltext = html2text2($htmltext);
			$htmltext = str_replace("***addspace***"," ",$htmltext);
		} else {
			$htmltext = "banned result";
		}
	}
	$htmltext = str_replace("\n","",$htmltext);
	return $htmltext;
}

function readPDF($url) {
	$PDF = new PDF2Text();
	$PDF->setFilename($url);
	$PDF->decodePDF();
	$htmltext = $PDF->output();
	return $htmltext;
}

function readHtml($url) {
	include 'settings.php';
	$html = fopen($url,"r");
	$htmltext = "";
	$urlWorks = true;
	try	{
		if (Url::exists($url)) {
			$urlWorks = true;
		}
	} catch (Exception $ex) {
		$urlWorks = false;
	}
	//if(url_exists($url)) {
	if($html != false) {
		while (!feof($html)) {
			$line = fgets($html);
			$htmltext = $htmltext . $line . " ";
		}
	} else {
		echo "<script language=javascript>updateError('".$fourohfour."')</script>";
		$htmltext = "404";
	}
	return $htmltext;
}

function stripBasicTags($html,$debug) {
	$out = $html;
	$removeNext = false;
	$pos_prev = 0;
	$close_prev = 0;
	$num = substr_count($html,"<");
	if($debug) {
		echo "number of basic tags: ".$num."<br />";
	}
	for ($i = 0; $i < $num; $i++) {
		// echo $i . ": ";
		$pos = strpos($out,"<");
		$close = strpos($out,">");
		if ($removeNext == true) {
			for ($k = $close_prev + 1; $k <= $pos - 1; $k++) {
				$out{$k} = "";
			}
		}
		$str = "";
		for ($j = $pos; $j <= $close; $j++) {
			// print htmlentities($out{$j});
			$str = $str . $out{$j};
			$out{$j} = "";
		}
		if (strpos($str,"script") != false) {
			$removeNext = true;
		} else {
			$removeNext = false;
		}
		// echo "<br />";
		$pos_prev = $pos;
		$close_prev = $close;
	}
	$out = cleanString($out);
	return $out;
}

function html2text($html) {
	$h2t =& new html2text($html);
	$text = $h2t->get_text(); 
	return $text;
}

function html2text2($html)
{
    $tags = array (
    0 => '~<h[123][^>]+>~si',
    1 => '~<h[456][^>]+>~si',
    2 => '~<table[^>]+>~si',
    3 => '~<tr[^>]+>~si',
    4 => '~<li[^>]+>~si',
    5 => '~<br[^>]+>~si',
    6 => '~<p[^>]+>~si',
    7 => '~<div[^>]+>~si',
    );
    $html = preg_replace($tags,"\n",$html);
    $html = preg_replace('~</t(d|h)>\s*<t(d|h)[^>]+>~si',' - ',$html);
    $html = preg_replace('~<[^>]+>~s',' ',$html);
    // reducing spaces
    $html = preg_replace('~ +~s',' ',$html);
    $html = preg_replace('~^\s+~m','',$html);
    $html = preg_replace('~\s+$~m','',$html);
    // reducing newlines
    $html = preg_replace('~\n+~s',"\n",$html);
	
	$html = str_replace("â€™","'",$html);
    return $html;
}

function findCard($htmltext,$first_clean,$last_clean,$debug) {
	include 'settings.php';
	$fpos = strpos($htmltext,$first_clean);
	$lpos = strpos($htmltext,$last_clean);
	if($debug) {
		echo "fpos: ".$fpos."<br />";
		echo "lpos: ".$lpos."<br />";
	}
			
	while(($lpos < $fpos) && ($lpos != false)) {
		$lpos = $fpos + strpos(substr($htmltext,$fpos),$last_clean);
		if($debug) {
			echo "lpos: ".$lpos."<br />";
		}
	}

	if ($fpos != false && $lpos != false && $lpos > $fpos) {
		$needle = substr($htmltext,$fpos,($lpos+strlen($last_clean))-$fpos);
		echo "<script language=javascript>updateStatus('Card found: \"".substr($needle,0,100)."\"')</script>";
	} else {
		$needle = $cardError . "-- " . $first_clean . "..." . $last_clean;
		//echo "<script language=javascript>updateStatus('No card found: result ".$i."')</script>";
	}
			
	$needle = cleanString($needle);
	return $needle;
}

function findCardByDistance($htmltext,$first_clean,$last_clean,$debug) {
	if($debug) {
		echo "starting deep search";
	}
	include 'settings.php';
	$htmltext = substr($htmltext,0,50000);
	$first_clean = substr($first_clean,0,255);
	$last_clean = substr($last_clean,0,255);
	$flen = strlen($first_clean);
	$llen = strlen($last_clean);
	$fdistance = $maxLevDistance;
	$ldistance = $maxLevDistance;
	$fpos = 0;
	$lpos = 0;
	for ($i=0;$i<strlen($htmltext);$i++) {
	// echo "char: ".$i."<br />";
		$ftest = substr($htmltext,$i,$flen);
		// echo "ftest: ".$ftest."<br />";
		if (levenshtein($first_clean,$ftest) < $fdistance) {
			$fpos = $i;
			$fdistance = levenshtein($first_clean,$ftest);
		}
		$ltest = substr($htmltext,$i,$llen);
		// echo "ltest: ".$ltest."<br />";
		if (levenshtein($last_clean,$ltest) < $ldistance) {
			$lpos = $i;
			$ldistance = levenshtein($last_clean,$ltest);
			if($ldistance < 15) {
				$i = strlen($htmltext);
			}
		}
		//echo "<script language=javascript>updateStatus('reading: ".$ftest."')</script>";
	}
	if($debug) {
		echo "fdistance: ".levenshtein($first_clean,substr($htmltext,$fpos,$flen))." at string ".substr($htmltext,$fpos,$flen)."<br />";
		echo "ldistance: ".levenshtein($last_clean,substr($htmltext,$lpos,$llen))." at string ".substr($htmltext,$lpos,$llen)."<br />";
		echo "fpos: ".$fpos."<br />";
		echo "lpos: ".$lpos."<br />";
	}
	if ($fpos != false && $lpos != false && $lpos > $fpos) {
		$needle = substr($htmltext,$fpos,($lpos+strlen($last_clean))-$fpos);
		echo "<script language=javascript>updateStatus('Card found: \"".substr($needle,0,100)."\"')</script>";
	} else {
		$needle = $cardError;
		echo "<script language=javascript>updateStatus('No card found in given ')</script>";
	}
			
	$needle = cleanString($needle);
	return $needle;
}

function cleanQboxForWiki($qbox) {
	
	$qboxFix = str_replace("\n", "LINEBREAK", $qbox);
	$qboxFix2 = str_replace("LINEBREAKLINEBREAKANDLINEBREAKLINEBREAK", "AND ", $qboxFix);
	$qboxFix3 = str_replace("LINEBREAKANDLINEBREAK", "AND ", $qboxFix2);
	$qbox = str_replace("LINEBREAK", "\n", $qboxFix3);
	$qboxFix = str_replace("\r\n", "LINEBREAK", $qbox);
	$qboxFix2 = str_replace("LINEBREAKANDLINEBREAK", "AND ", $qboxFix);
	$qbox = str_replace("LINEBREAK", "\n", $qboxFix2);
	$qbox = str_replace("\nAND \n","AND ", $qbox);
	return $qbox;
}

if(isset($_SESSION['user'])) {
	//Get user preferences

	mysql_connect($sqlserver,$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");
	
	$query = "SELECT email,usefirst,maxdistance,google,yahoo,custdelim,custmark FROM users WHERE user='".$_SESSION['user']."'";
	$result = mysql_query($query) or die("Failed: $query"); //Stops the script if the query failed
	$numrows = mysql_num_rows($result); //The number of rows the query returned

	$result_row = mysql_fetch_row($result);
	$email = $result_row[0];
	$useFirstPref = $result_row[1];
	$maxLevDistancePref = $result_row[2];
	$google = $result_row[3];
	$yahoo = $result_row[4];
	$custdelim = $result_row[5];
	$custmark = $result_row[6];
	
	if($useFirstPref === 'o') {
		$useFirst = true;
	}
	$maxLevDistance = (int)$maxLevDistancePref;
	
	//echo "useFirst: ".$useFirstPref."<br />";
	//echo "maxLev: ".$maxLevDistance;
	
	$qbox = $_POST['query'];
	//echo "qbox1: ".nl2br(str_replace("\r\n","LINEBREAK",$qbox))."<br />";
	$qbox = cleanQboxForWiki($qbox);
	$debug = $_POST['debug'];
	if($debug) {
		echo $qbox . "<br />";
	}
	$qarray = explode("\n",$qbox);
	$output = "";
	$numCards = 0;
	for ($qline = 0; $qline < count($qarray); $qline++) {
		echo "<script language=javascript>updateStatus('Beginning qline ".$qline."')</script>";
		$query = cleanLine($qarray[$qline]);
		$qlineout = $query;
		$cardSearch = getFirstLast($query,$custdelim);
		if ($cardSearch) {
			$numCards = $numCards + 1;
			echo "<script language=javascript>updateStatus('Parsing query')</script>";
			$foundGoodCard = false;
			if (strpos($query,"…") != false) {
				$qsplit = explode("…",$query);
			} elseif (strpos($query,"...") != false) {
				$qsplit = explode("...",$query);
			} elseif (strpos($query,$custdelim) != false) {
				$qsplit = explode($custdelim,$query);
			} else {
				$qsplit = explode("AND",$query);
			}
			$first = cleanGoogle($qsplit[0]);
			$last = cleanGoogle($qsplit[1]);
			$first_clean = cleanQuery(cleanString(str_replace("\n","",trim($qsplit[0]))));
			$last_clean = cleanQuery(cleanString(str_replace("\n","",trim($qsplit[count($qsplit)-1]))));
			
			$cardarray = array();
			$urlarray = array();
			
			$needle = "";
			
			//BEGIN MANUAL URL CHECK
			
			echo "<script language=javascript>updateStatus('Checking for embedded URLs.')</script>";
			$foundURL = false;
			$pline = 0;
			for ($p=0;$p<4;$p++) {
				if($qline-$p >= 0) {
					if (strstr($qarray[$qline-$p],"http")) {
						$pline = $qline - $p;
					}
				}
			}
			//echo "pline: ". $pline ."<br />";
			//echo $qarray[$pline] . ", http?: ".strpos($qarray[$pline],"http")."<br />";
			if(strstr($qarray[$pline],"http")) {
				echo "<script language=javascript>updateStatus('Parsing embedded URL.')</script>";
				$url = getUrlFromCite($qarray[$pline]);
				if($debug) {
					echo $url . "<br />";
				}
				$htmltext = parseUrl(urlClean($url),null,$debug);
				if($debug) {
					echo "<p><h3>HTML Text: Length ".strlen($htmltext)."</h3></p>";
					echo "<p>".$htmltext."</p>";
				}
				$needle = findCard($htmltext,$first_clean,$last_clean,$debug);
				if($debug) {
					echo "<p><h3>Exact Match Card</h3></p>";
					echo "<p>".$needle."</p>";
				}
			}
			if(strcmp($needle,$cardError) == 0) {
				echo "<script language=javascript>updateStatus('Deep searching embedded URL.')</script>";
				$needle = findCardByDistance($htmltext,$first_clean,$last_clean,$debug);
				if($debug) {
					echo "<p><h3>Deep Search Card</h3></p>";
					echo "<p>".$needle."</p>";
				}
			}
			
			$cardarray[0] = $needle;
			$urlarray[0] = urlClean($url);
			
			if ((strpos($needle,$cardError) == false) && (strlen($needle) > 50)) {
				$foundGoodCard = true;
			}
			
			// BEGIN SEARCHING APIs
			
			if($debug) {
				echo "first_clean: ".$first_clean."<br />";
				echo "last_clean: ".$last_clean."<br />";
			}
			
			$first_array = explode(" ",$first_clean);
			$last_array = explode(" ",$last_clean);
			
			if ((count($first_array) + count($last_array)) <= 7) {
				$addTitle = true;
			} else {
				$addTitle = false;
			}

			$searchquery = "\"".cleanQuery($first)."\"%20\"".cleanQuery($last)."\"";
			
			if($addTitle && ($title != null)) {
				$searchquery = $searchquery . "%20\"" . $title . "\"";
			}
			
			if($debug) {
				echo "searchquery: ".str_replace("%20"," ",$searchquery)."<br />";
			}
		
			
			// BEGIN GOOGLE SEARCH
			
			if (!$foundGoodCard || !$useFirst) {
				echo "<script language=javascript>updateStatus('Contacting Google')</script>";
				$results = googleSearch($searchquery,$debug);

				if($debug) {
					echo "result count: ".count($results)."<br />";
				}
				
				$returnvar = parseResultSet($results,$cardarray,$urlarray,$first_clean,$last_clean,$debug);
				$foundGoodCard = $returnvar['foundGoodCard'];
				$cardarray = $returnvar['cardarray'];
				$urlarray = $returnvar['urlarray'];
			}
			
			// YAHOO SEARCH
			
			if (!$foundGoodCard || !$useFirst) {
				echo "<script language=javascript>updateStatus('Contacting Yahoo')</script>";

				$results = yahooSearch($searchquery,$debug);

				if($debug) {
					echo "result count: ".count($results)."<br />";
				}
				
				$returnvar = parseResultSet($results,$cardarray,$urlarray,$first_clean,$last_clean,$debug);
				$foundGoodCard = $returnvar['foundGoodCard'];
				$cardarray = $returnvar['cardarray'];
				$urlarray = $returnvar['urlarray'];
			}
			
			// ITERATING CARD ARRAY

			$maxlength = 0;
			//print_r($cardarray);
			//print_r($urlarray);
			for ($k=0;$k<count($cardarray);$k++) {
				if (strlen($cardarray[$k]) > $maxlength) {
					$qlineout = $cardarray[$k];
					$urlout = $urlarray[$k];
					$maxlength = strlen($qlineout);
				}
			}
			if ((strlen($qlineout) <= strlen($first_clean.$last_clean) + 5) || ($qlineout == null)) {
				$qlineout = $cardError;
				$filename = "cardsmissed.txt";
				$fh = fopen($filename, 'r');
				$cardsmissed = fread($fh, filesize($filename));
				fclose($fh);
				$fw = fopen($filename, 'w');
				fwrite($fw, $cardsmissed + 1);
				fclose($fw);
			}
			if (strcmp($qlineout,$cardError) != 0) {
				$qlineout = checkEndOfCard($qlineout,$first_clean,$last_clean);
				$qlineout = addDisclaimer($qlineout,$urlout);
				$filename = "cardsfound.txt";
				$fh = fopen($filename, 'r');
				$cardsfound = fread($fh, filesize($filename));
				fclose($fh);
				$fw = fopen($filename, 'w');
				fwrite($fw, $cardsfound + 1);
				fclose($fw);
			}
			if (strcmp(trim($qarray[$qline-1]),"") != 0) {
				$qlineout = "\n" . $qlineout;
			}
			$qlineout = $custmark . " " . $qlineout;
		} elseif (substr_count($query,"\"") > 1 || substr_count($query,"'") > 1) {
			$title = getTitleFromCite($query);
			if($debug) {
				echo "title found: ".$title."<br />";
			}
			$qlineout = "<b>".cleanBrackets(cleanQuery($query))."</b>";
		} else {
			$qlineout = "<b>".cleanBrackets(cleanQuery($query))."</b>";
		}
		if($debug) {
			echo "<strong>LINE:</strong>".$qlineout ."<br />";
			echo "<p><h3>mem usage</h3>".memory_get_usage().", time ".(microtime_float()-$time_start)." sec</p>";
		}
		$output = $output . $qlineout . "\n";
		echo "<script language=javascript>updateResult('".cleanOutputVar($output)."')</script>";
		$cardarray = array();
		$urlarray = array();
		$foundGoodCard = false;
	}

	if ($numCards == 0) {
		echo "<script language=javascript>updateError('Warning: No cards parsed.')</script>";
	}
	if($debug) {
		echo "<p><h3>Final Output</h3>".str_replace("\n","<br />",$output)."</p>";
	}
	echo "<script language=javascript>updateStatus('Finished')</script>";
	
	// Create a new PHPWord Object
	$PHPWord = new PHPWord();
	$PHPWord->addFontStyle('bold', array('name'=>'Times New Roman', 'size'=>12, 'color'=>'1B2232', 'bold'=>true));
	// Every element you want to append to the word document is placed in a section. So you need a section:
	$section = $PHPWord->createSection();
	
	$output_split = explode("<b>",$output);
	for($m=0;$m<count($output_split);$m++) {
		$bold_split = explode("</b>",$output_split[$m]);
		$section->addText($bold_split[0],'bold');
		$section->addText($bold_split[1]);
	}

	// At least write the document to webspace:
	$filename = "debcite-".$_SESSION['user']."-".date("m-d-y")."--".date("H-i-s").".docx";
	$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
	$objWriter->save($filename);
	echo "<a href=\"".$filename."\">".$filename."</a>";

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