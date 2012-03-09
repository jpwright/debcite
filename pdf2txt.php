<?php

// new pdf extract

// Function    : pdf2txt()
// Arguments   : $filename - Filename of the PDF you want to extract
// Description : Reads a pdf file, extracts data streams, and manages
//               their translation to plain text - returning the plain
//               text at the end
// Author      : Jonathan Beckett, 2005-05-02
function pdf2txt($filename){

	$data = getUrlData($filename);
	var_dump($data);

	// grab objects and then grab their contents (chunks)
	$a_obj = getDataArray($data,"obj","endobj");
	foreach($a_obj as $obj){

		$a_filter = getDataArray($obj,"<<",">>");
		if (is_array($a_filter)){
			$j++;
			$a_chunks[$j]["filter"] = $a_filter[0];

			$a_data = getDataArray($obj,"stream\r\n","endstream");
			if (is_array($a_data)){
				$a_chunks[$j]["data"] = substr($a_data[0],strlen("stream\r\n"),strlen($a_data[0])-strlen("stream\r\n")-strlen("endstream"));
			}
		}
	}

	// decode the chunks
	foreach($a_chunks as $chunk){

		// look at each chunk and decide how to decode it - by looking at the contents of the filter
		$a_filter = split("/",$chunk["filter"]);
		
		if ($chunk["data"]!=""){
			// look at the filter to find out which encoding has been used			
			if (substr($chunk["filter"],"FlateDecode")!==false){
				$data =@ gzuncompress($chunk["data"]);
				if (trim($data)!=""){
					$result_data .= ps2txt($data);
				} else {
				
					//$result_data .= "x";
				}
			}
		}
	}
	
	return $result_data;
	
}


// Function    : ps2txt()
// Arguments   : $ps_data - postscript data you want to convert to plain text
// Description : Does a very basic parse of postscript data to
//               return the plain text
// Author      : Jonathan Beckett, 2005-05-02
function ps2txt($ps_data){
	$result = "";
	$a_data = getDataArray($ps_data,"[","]");
	if (is_array($a_data)){
		foreach ($a_data as $ps_text){
			$a_text = getDataArray($ps_text,"(",")");
			if (is_array($a_text)){
				foreach ($a_text as $text){
					$result .= substr($text,1,strlen($text)-2);
				}
			}
		}
	} else {
		// the data may just be in raw format (outside of [] tags)
		$a_text = getDataArray($ps_data,"(",")");
		if (is_array($a_text)){
			foreach ($a_text as $text){
				$result .= substr($text,1,strlen($text)-2);
			}
		}
	}
	return $result;
}


// Function    : getFileData()
// Arguments   : $filename - filename you want to load
// Description : Reads data from a file into a variable
//               and passes that data back
// Author      : Jonathan Beckett, 2005-05-02
function getFileData($filename){
	$handle = fopen($filename,"r");
	$data = fread($handle, filesize($filename));
	fclose($handle);
	var_dump($data);
	return $data;
}

function getUrlData($url) {
	$context = array (
         'http' => array (
               'proxy' => 'hostIP:hostPort', 'request_fulluri' => true,
         ),
    );
   $context = stream_context_create ($context); 
   $data = file_get_contents($url,0,$context);
   return $data;
}


// Function    : getDataArray()
// Arguments   : $data       - data you want to chop up
//               $start_word - delimiting characters at start of each chunk
//               $end_word   - delimiting characters at end of each chunk
// Description : Loop through an array of data and put all chunks
//               between start_word and end_word in an array
// Author      : Jonathan Beckett, 2005-05-02
function getDataArray($data,$start_word,$end_word){

	$start = 0;
	$end = 0;
	unset($a_result);
	
	while ($start!==false && $end!==false){
		$start = strpos($data,$start_word,$end);
		if ($start!==false){
			$end = strpos($data,$end_word,$start);
			if ($end!==false){
				// data is between start and end
				$a_result[] = substr($data,$start,$end-$start+strlen($end_word));
			}
		}
	}
	return $a_result;
}

?>
