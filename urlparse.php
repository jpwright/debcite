<?php

function urlClean($in) {
	$url = str_replace("%3F","?",$in);
	$url = str_replace("%3D","=",$url);
	$url = str_replace("%2B","+",$url);
	$url = str_replace("%25","%",$url);
	
	$url = fullPageMod($url);
	
	return $url;
}

function fullPageMod($url) {
	
	//docstoc
	if (strpos($url,"docstoc.com") != false) {
		$docsPos = strpos($url,"docs/");
		$docId = "";
		$docIdEnd = false;
		for ($i=$docsPos+5;$i<strlen($url);$i++) {
			if (strcmp($url{$i},"/") == 0) {
				$docIdEnd = true;
			}
			if (!$docIdEnd) {
				$docId = $docId . $url{$i};
			}
		}
		$docId = (int)$docId;
		echo "docstoc ID: ".$docId."<br />";
		include 'rijndael.php';
		$appKey = "29ff951cf6b54e7caa1968e7b282e1ce";
		$user = encrypt_string("debcite");
		$pass = encrypt_string("debcite");
		$node = "http://rest.docstoc.com/authentication/AuthenticateUser?";
		$node = $node . "Key=".$appKey;
		$node = $node . "&UserName=".$user;
		$node = $node . "&Password=".$pass;
		echo "<p><h3>docstoc api auth url</h3>".$node."</p>";
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $node);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, "http://www.jasonline.net/debcite");
		$body = curl_exec($ch);
		curl_close($ch);
		
		require_once('class.xml2array.php');
		$xmlObj = new XmlToArray($body);
		$xmlArray = $xmlObj->createArray();
		print_r($xmlArray);		
		$result = $xmlArray['Result'];
		if (strcmp($result['Code'],"")==0) {
			$docnode = "http://rest.docstoc.com/document/DownloadDocument?";
			$docnode = $docnode . "Key=".$appKey;
			$docnode = $docnode . "&Ticket=".$result['Message'];
			$docnode = $docnode . "&DocId=".$docId;
			$docnode = $docnode . "&DocKey=debcite&DocPass=debcite";
			echo "<p><h3>docstoc api doc url</h3>".$docnode."</p>";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $docnode);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, "http://www.jasonline.net/debcite");
			$body = curl_exec($ch);
			curl_close($ch);
			
			$docXmlObj = new XmlToArray($body);
			$docXmlArray = $docXmlObj->createArray();
			print_r($docXmlArray);
		} else {
			echo "<script language=javascript>updateError('Warning: Docstoc API failed to respond. Check log for more details.')</script>";
		}
	}
	//ft.com/cms
	if (strpos($url,"ft.com/cms") != false) {
		$url = str_replace("Authorised=false","Authorised=true",$url);
	}
	//theage.com.au
	if (strpos($url,"theage.com.au") != false) {
		$url = $url . "?page=fullpage#contentSwap1";
	}
	return $url;
}

function checkBanned($url) {
	$banned = array('opencaselist','debatecoaches.org/wiki','planetdebate','nfacaselist');
	$ban = false;
	for ($i=0;$i<count($banned);$i++) {
		if (strstr($url,$banned[$i])) {
			$ban = true;
		}
	}
	return $ban;
}

?>