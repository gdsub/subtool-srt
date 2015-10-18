<?php
/**
 * Created by PhpStorm.
 * User: chenglu
 * Date: 15-1-18
 * Time: 上午10:57
 * Description: 核心业务逻辑处理在这里喔~
 */
require_once dirname(__FILE__)."/util/getXMLfromYT.class.php";
require_once dirname(__FILE__)."/util/srtConvert.class.php";

$youtubeUrl = "";
$cc_sub = FALSE;
$srt_name = "";

if($_POST['submit']){
    $youtubeUrl = $_POST['wz'];
    $youtubeUrl = base64_decode($youtubeUrl);
	if($_POST['cc']){
		$cc_sub = TRUE;
	}
	if($_POST['srtname']){
		$srt_name = $_POST['srtname'];
	}else{
		$srt_name = "【注意】文件名待修改";
	}
}else{
    echo "非法提交";
}

$getxml = new getXMLfromYT($youtubeUrl,"en",$cc_sub);
$xml = $getxml->getXML();
if ($xml == false){
    exit("啥也没有得到啊亲");
}else{

header("content-type: text/html; charset=UTF-8"); //页面编码
header("content-type:text/plain");
header("content-disposition:attachment;filename=".$srt_name."_en.srt");
header("pragma:no-cache");
header("expires:0");

}

$srtConvert = new srtConvert();
$srt = $srtConvert->xmlToArray($xml);

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
