<?php
	$data = explode("INFOHIO",$_SERVER['QUERY_STRING']);
	$source = $data[0];
	$profile = $data[1];
	$server = $data[2];
	$query = $data[3];
	
	$url = strtolower("https://".$server.".infohio.org/discovery-search/os?pr=").strtoupper($profile)."&rw=1&st=".strtoupper($source)."&q=".$query."&term=".$query."&ct=0&slx=".strtoupper($profile);
echo $url;
	$out = file_get_contents($url);

	preg_match("/(.*)<opensearch:totalResults>(.*)<\/opensearch:totalResults>(.*)/",$out,$arr,PREG_OFFSET_CAPTURE);
	echo $arr[2][0];
?>
