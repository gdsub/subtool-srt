<?php

/*
* 这个 API 主要用于输出 srt 字幕
* This API is using for srt output
*/

class gdsubSrtApi{
    var $videoID = null;
    var $format = null;
    
    function getSrtByFormat(){
        $videoID = isset($videoID)? $videoID: $_GET["v"];
        $format  = isset($this->format)? $this->format: $_GET["f"];
        
        if ($format === "json"){
            // output json file logic
        }else if($format === "txt"){
            // just output the subtitle
            
        }
        
    }
}

?>