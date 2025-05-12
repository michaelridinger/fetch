<pre>
<?
	$json = file_get_contents("../catjricons.json");
	$cats = json_decode($json,true);
	foreach ($cats as $ndx => $cat) {
		echo $ndx."\n";
		foreach ($cat as $key => $val) {
			echo "    ".$val['name'];
			if ($val['query'] > "") {
				echo " - Search: ".$val['query'];
			}
			echo "\n";
		}
	}
?>