<?php
// input a video id, export to the srt strings

require_once("getXMLfromYT.class.php");
require_once("srtConvert.class.php");

class exportSrt{
    var $youtubeId;
    
    public function __construct($youtubeId){
        $this->youtubeId = $youtubeId;
    }
    
    public function getSrt(){
        
        $getxml = new getXMLfromYT($this->youtubeId, "en", "0", "");
        $result = $getxml->getXML();
        $srtConvert = new srtConvert();
        $srt = $srtConvert->xmlToArray($result);
        
        $timeline = array();
        $linenumber = 1;
        $flag = false;
        // 保持英文70字符
        for ($i=0; $i<count($srt); $i++){
            if($flag){
                $flag = false;
                continue;
            }
            $text = $srt[$i]['text'];
            $last = substr($text,-2);
            if (strpos($last,"]") ||strpos($last,".") || strpos($last,"!") || strpos($last,"?")){
                $srt[$i]["number"] = $linenumber;
                $timeline[] = $srt[$i];
                $linenumber++;
            }else{
                $nextText = $srt[$i+1]['text'];
                $length = strlen($text.$nextText);
                if ($length < 70){
                    $flag = true;
                    $newline = array(
                        "number" => $linenumber,
                        "stopTime" => $srt[$i+1]['stopTime'],
                        "startTime" => $srt[$i]['startTime'],
                        "text" => $text." ".$nextText
                    );
                    $timeline[] = $newline;
                    $linenumber++;
                }else{
                    // 如果加起来不大于70的话，就照常输出呗
                    $srt[$i]["number"] = $linenumber;
                    $timeline[] = $srt[$i];
                    $linenumber++;
                }
            }
        }
        
        $srtConvert->arrayToSrt($timeline);
        
    }
    
}



?>