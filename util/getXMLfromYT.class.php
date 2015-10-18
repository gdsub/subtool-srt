<?php
/**
 * Created by PhpStorm.
 * User: chenglu
 * Date: 15-1-18
 * Time: 上午10:56
 * Description: 这个文件是用来从 YouTube 获取 XML 字幕的，发给一个 YT 链接，返回一个英文的 XML
 */

class getXMLfromYT{

    var $youtubeUrl;
    var $language;
    var $cc_sub;
    var $base_url = "https://video.google.com/timedtext";

    public function __construct($youtubeUrl, $language = "en", $cc_sub){
        $this->youtubeUrl = $youtubeUrl;
        $this->language = $language;
	$this->cc_sub = $cc_sub;
    }

    public function getXML(){
        // https://video.google.com/timedtext?lang=en&v=iQTxMkSJ1dQ&list=UU_x5XG1OV2P6uZZ5FSM9Ttw
        $target = $this->base_url .
                         "?lang=" . $this->language.
                            "&v=" . $this->getYoutubeVideoId();
	if($this->cc_sub){
		$target = $target."&name=CC";
	}
        $result = file_get_contents($target);
        if( strlen($result)!==0 ){
            return $result;
        }
        return false;
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
}
