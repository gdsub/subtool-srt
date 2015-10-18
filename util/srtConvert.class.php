<?php
/**
 * Created by PhpStorm.
 * User: chenglu
 * Date: 15-1-18
 * Time: 上午10:56
 * Description: 从 XML 字幕转换成可以处理的 Array，从 Array 转换成 SRT 字符串
 */

define('SRT_STATE_SUBNUMBER', 0);
define('SRT_STATE_TIME',      1);
define('SRT_STATE_TEXT',      2);
define('SRT_STATE_BLANK',     3);

class srtConvert{

    function xmlToArray($xml){
        $lines = "";
        $sxe = simplexml_load_string($xml);
        $i=1;
        foreach($sxe->text as $a)
        {
            $end=(float)$a['start']+(float)$a['dur'];
            $endtime=gmstrftime('%H:%M:%S',(int)$end);
            $start=gmstrftime('%H:%M:%S',(int)$a['start']);
            $szhi=(float)$a['start'];
            $ezh=$this->change($end);
            $szh=$this->change($szhi);
            if($a[0]!=''){
                $lines.=$i."\r\n".$start.",".$szh." --> ".$endtime.",".$ezh."\r\n".$a[0]."\r\n\r\n";
                $i++;
            }
        }
        $lines = explode("\r\n",$lines);

        $subs    = array();
        $state   = SRT_STATE_SUBNUMBER;
        $subNum  = 0;
        $subText = '';
        $subTime = '';

        foreach($lines as $line) {
            switch($state) {
                case SRT_STATE_SUBNUMBER:
                    $subNum = trim($line);
                    $state  = SRT_STATE_TIME;
                    break;

                case SRT_STATE_TIME:
                    $subTime = trim($line);
                    $state   = SRT_STATE_TEXT;
                    break;

                case SRT_STATE_TEXT:
                    if (trim($line) == '') {
                        $sub = new stdClass;
                        $sub->number = $subNum;
                        list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                        $sub->text   = $subText;
                        $subText     = '';
                        $state       = SRT_STATE_SUBNUMBER;

                        $subs[]      = (array)$sub;
                    } else {
                        $subText =$subText." ".$line;
                        $subText = $this->textOp($subText);
                    }
                    break;
            }
        }
        if ($subs){
            return $subs;
        }
        return false;
    }

    function arrayToSrt($array){
        for ($i=0;$i<count($array);$i++){
            $final_output = $array[$i];
           # echo "<pre>";
            echo $final_output['number'];
            echo "\n";
            $start_time_echo = $final_output['startTime'];
            if ($start_time_echo == ""){
                $start_time_echo = "00:00:00,000";
            }
            $end_time_echo = $final_output['stopTime'];

            echo $start_time_echo." --> ".$end_time_echo;
            echo "\n";
            echo trim($final_output['text']);
            echo "\n";
            echo "\n";
           # echo "</pre>";
        }
    }

    private function change($time){
        $t=$time - floor($time);
        $t = round($t,3);
        if($t<0.1){
            $t=$t*1000;
            $t = substr(strval($t+1000),1,3);
        }else{
            $t=(int)($t*1000);
        }
        return $t;
    }

    private function textOp($str){
        // 删除 srt 字幕中的英文换行
        $str = trim($str);
        $str = preg_replace('/\n/', ' ', $str);
        $str = preg_replace('/\r/', ' ', $str);
        $str = preg_replace('/\r\n/', ' ', $str);
        $str = preg_replace('/  /', ' ', $str);
        return $str;
    }
}
