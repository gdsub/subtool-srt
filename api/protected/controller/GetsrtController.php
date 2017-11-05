<?php
require_once dirname(__FILE__)."/../util/exportSrt.class.php";

class GetsrtController extends BaseController {
	// 首页
	function actionSrtops(){
		// echo $_GET["videoid"];
		$get = new exportSrt($_GET["videoid"]);
		$get -> getSrt();
	}
}