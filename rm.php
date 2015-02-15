<?php
/**
 * 逐行扫描，SRT字幕文件的格式为
 * 第一行是数字 SRT_STATE_SUBNUMBER
 * 第二行是时间轴 SRT_STATE_TIME
 * 第三行之后全部都是字幕信息 SRT_STATE_TEXT
 * 直到遇到一个空行之后 SRT_STATE_BLANK，一个分段的字幕处理完毕
 * 思路：
 * 使用 file（） 将文件的每一行都读出来并组成数组，一行一行处理，如果这个文件是一个标准的字幕文件
 * 则它必然遵循上面所说的字幕格式，这个里面使用了一个 switch 语句从第一行处理，然后分析该行的内容，写入小数组，更改状态
 * 更改状态：默认状态是SRT_STATE_SUBNUMBER，检测到第一行是SRT_STATE_SUBNUMBER之后，更改状态为SRT_STATE_TIME
 * 一行一行处理最后将所有的字幕信息写入数组并返回
 *
 * 使用方法，将该文件放入lession文件夹下 使用 php rm.php 命令执行这个脚本即可自动运行，直接删除源字幕文件，因此做好备份哈。
 */
define('SRT_STATE_SUBNUMBER', 1);
define('SRT_STATE_TIME',      2);
define('SRT_STATE_TEXT',      3);
define('SRT_STATE_BLANK',     4);

class rm{

    public function write(){
        $files = $this->get_files(".");
        foreach($files as $file){
            // 解析双语字幕，并删除其中的英文，返回数组
            $ret = $this->convert($file);
            // 新的文件名字
            $fileName = str_replace(".en.srt",".zh_CN.srt",$file);
            for($i=0;$i<count($ret);$i++){
                // 用追加的方式将处理之后的内容写入新的文件
                file_put_contents($fileName, $ret[$i]["number"]."\n", FILE_APPEND);
                file_put_contents($fileName, $ret[$i]["startTime"]." --> ".$ret[$i]["stopTime"]."\n", FILE_APPEND);
                file_put_contents($fileName, $ret[$i]["text"]."\n", FILE_APPEND);
            }
            // 删除老的双语字幕文件
            unlink($file);
        }
    }

    private function convert($filename){

        $lines   = file($filename);
        $subs    = array();
        $state   = SRT_STATE_SUBNUMBER;
        $subNum  = 0;
        $subText = '';
        $subTime = '';
        $i=0;

        foreach($lines as $line) {
            $i++;
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
                    if (trim($line) == '' ) {
                        $sub = new stdClass;
                        $sub->number = $subNum;
                        list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                        $sub->text   = $subText;
                        $subText     = '';
                        $state       = SRT_STATE_SUBNUMBER;

                        $subs[]      = (array)$sub;
                    } else {
                        if (preg_match("/[\x7f-\xff]/", $line)) {
                            // 检测内容是否包含中文
                            $subText =$subText.$line;
                        }
                        if($i==count($lines)){
                            $sub = new stdClass;
                            $sub->number = $subNum;
                            list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                            $sub->text   = $subText;
                            $subText     = '';
                            $state       = SRT_STATE_SUBNUMBER;

                            $subs[]      = (array)$sub;
                        }
                    }
                    break;
            }
        }
        if ($subs){
            return $subs;
        }
        return false;
    }
    function get_files($dir) {
        $files = array();

        if(!is_dir($dir)) {
            return $files;
        }

        $handle = opendir($dir);
        if($handle) {
            while(false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $filename = $dir . "/"  . $file;
                    if(is_file($filename)) {
                        $files[] = $filename;
                    }else {
                        $files = array_merge($files, $this->get_files($filename));
                    }
                }
            }   //  end while
            closedir($handle);
        }
        return $files;
    }   //
}

$rm = new rm();
$rm->write();