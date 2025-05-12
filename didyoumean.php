<?php
/*
	if (isset($_POST['search'])) {
		$search=$_POST['search'];
		$oldsearch=$_POST['oldsearch'];
	} elseif (isset($_GET['search'])) {
		$search=$_GET['search'];
	} else {
		$search="";
		$oldsearch="";
	}
	if ($search > "" && $search != $oldsearch) {
//	        $key = "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";
		$key = "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";
	        $curl = curl_init();
	
	        curl_setopt_array($curl, array(
	          CURLOPT_URL => "https://api.openai.com/v1/completions",
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => "",
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 30,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => "POST",
	          CURLOPT_POSTFIELDS => json_encode(array(
	              "model" => "text-davinci-003",
              	      "prompt" => "What did this elementary library student mean to search for? They entered: ".$search.". Only respond with the phrase the student meant to type. If the student is asking a direct question, please append the answer to your response and remove all objectionable, abusive, or controversial words.",
	              "max_tokens" => 30,
		      "top_p" => 1,
		      "frequency_penalty" => 0,
		      "presence_penalty" => 0,
	              "temperature" => 0
	          )),
	          CURLOPT_HTTPHEADER => array(
	            "Content-Type: application/json",
	            "Authorization: Bearer ".$key
	          ),
	        ));
	        $response = curl_exec($curl);
	        $err = curl_error($curl);
	
	        curl_close($curl);
	
	        $r=json_decode($response,true);
	        $didyoumean = str_replace('"','',$r['choices'][0]['text']);
		if (strtolower($didyoumean) != strtolower($search)) {
	
			echo "<div style='font-size:1em;text-align:center;color:#FFF;'>DID YOU MEAN</div>";
			echo "<div style='line-height:1.1em;font-size:1.5em;font-weight:900;'><span id='didyoumean_openai'>".$didyoumean."</span></div>";
			echo "<div style='text-align:center;font-size:.6em;line-height:1em;padding-top:10px;color:#FFFF00;text-shadow:1px 1px 5px #000;'>Powered by INFOhio OpenAI Integration</div>";
		}
	} else {
		echo "ERR";
	}
*/
?>
