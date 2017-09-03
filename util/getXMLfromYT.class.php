<?php

class getXMLfromYT{

	var $youtubeUrl;
	var $language;
	var $cc_sub;
	var $cc_sub_name;
	var $base_url = "https://video.google.com/timedtext";

	public function __construct($youtubeUrl, $language = "en", $cc_sub, $cc_sub_name){
		$this->youtubeUrl = $youtubeUrl;
		$this->language = $language;
		$this->cc_sub = $cc_sub;
		$this->cc_sub_name = $cc_sub_name;
	}

	public function getXML(){
//		var_dump($this->getOriginSrtUrl());
		
		$result = file_get_contents($this->getOriginSrtUrl());
		if($result){
			$this->cc_sub_name = true;
			$result = file_get_contents($this->getOriginSrtUrl());
		}
		
		return $result;
		
		
//		// https://video.google.com/timedtext?lang=en&v=iQTxMkSJ1dQ&list=UU_x5XG1OV2P6uZZ5FSM9Ttw
//		$target = $this->base_url .
//						 "?lang=" . $this->language.
//							"&v=" . $this->getYoutubeVideoId();
//	if($this->cc_sub){
//		$target = $target."&name=CC";
//	}
//		$result = file_get_contents($target);
//		if( strlen($result)!==0 ){
//			return $result;
//		}else{
////			$target = $target."&name=CC";
//			$result = file_get_contents($target."&name=CC");
//			if (strlen($result)!==0){
//				return $result;
//			} else {
////				$target = $target."&name=CC%20(English)";
//				$result = file_get_contents($target."&name=CC%20(English)");
//				if (strlen($result)!==0){
//					return $result;
//				}
//			}
//		}
//		return false;
	}

	function getYoutubeVideoId() {
		$urlParts = parse_url($this->youtubeUrl);
		if($urlParts === false || !isset($urlParts['host']))
			return false;
		if(strtolower($urlParts['host']) === 'youtu.be')
			return ltrim($urlParts['path'], '/');
		if(preg_match('/^(?:www.)?youtube.com$/i', $urlParts['host']) && isset($urlParts['query'])) {
			parse_str($urlParts['query'], $queryParts);
			if(isset($queryParts['v']))
				return $queryParts['v'];
		}
		return false;
	}
	
		
		public function getOriginSrtUrl() {
			$cc_sub = $this->cc_sub;
			
			// get the right langcode and cc name
			$langcode = null;
			$cc_sub_name = null;
			
			$videoId = $this->getYoutubeVideoId();
			
			// this is the url that could get all the captions info of the video
			$videoCaptionInfoUrl = $this->base_url .
					"?hl=en&type=list".
								"&v=" . $videoId;
				
			$res = file_get_contents($videoCaptionInfoUrl);
			if ($res == null){
				echo (" error: API error ");
				return false;
			}
		
			$videoCaptionInfo = $this->simplest_xml_to_array($res);
								
			// check if there has English captions
			if ($videoCaptionInfo["track"]==null) {
				echo (" error: No captions ");
				return false;
				}
			
			$captionNames = array();
			for ($i=0; $i<count($videoCaptionInfo["track"]); $i++) {
				$langcode = $videoCaptionInfo["track"][$i]["@attributes"]["lang_code"];
				$captionName = $videoCaptionInfo["track"][$i]["@attributes"]["name"];
				if ($captionName){
					$captionNames[] = $captionName;
				}
			}
			if ($langcode != "en") {
				echo (" error: No English captions ");
				return false;
				}
		
			// check if there has CC captions
			for ($i=0; $i<count($captionNames); $i++) {
				if (strpos($captionNames[$i], "CC")){
					$cc_sub_name = $captionNames[$i];
				}
			}
			// check the CC caption's name
//			var_dump($captionNames);
//			var_dump($cc_sub_name);
			
			// return the CC URL
			if($cc_sub){
				$target = $this->base_url .
								"?lang=en".
									"&v=" . $videoId.
								 "&name=" . $captionNames[0];
			}else {
				$target = $this->base_url .
								"?lang=en".
									"&v=" . $videoId;
			}
			
//			echo $target;
			
			return $target;
		}
		
		/**
		* 最简单的XML转数组
		* @param string $xmlstring XML字符串
		* @return array XML数组
		*/
		public function simplest_xml_to_array($xmlstring) {
			return json_decode(json_encode((array) simplexml_load_string($xmlstring)), true);
		}


		
}
