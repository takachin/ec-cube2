<?php
/*
 * Copyright(c) 2000-2007 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 */

require_once("../require.php");

$objQuery = new SC_Query();
$objFormParam = new SC_FormParam();

// トラックバック機能の稼働状況チェック
$arrSiteControl = $objQuery->select("*", "dtb_site_control", "control_id = ?", array(SITE_CONTROL_TRACKBACK));

// TODO:共通関数化する
if (count($arrSiteControl) > 0) {
	if ($arrSiteControl["control_flg"] == 2) {
		IfResponseNg();
	}
} else {
	// NG
	IfResponseNg();
}

// パラメータ情報の初期化
lfInitParam();

// エンコード設定(サーバ環境によって変更)
$beforeEncode = "auto";
$afterEncode = mb_internal_encoding();

if (isset($_POST["charset"])) {
	$beforeEncode = $_POST["charset"];
} else if (isset($_GET["charset"])) {
	$beforeEncode = $_GET["charset"];
}

// POSTデータの取得とエンコード変換

// ブログ名
if (isset($_POST["blog_name"])) {
	$arrData["blog_name"] = trim(mb_convert_encoding($_POST["blog_name"], $afterEncode, $beforeEncode));
} else if (isset($_GET["blog_name"])) {
	$arrData["blog_name"] = trim(mb_convert_encoding($_GET["blog_name"], $afterEncode, $beforeEncode));
}

// ブログ記事URL
if (isset($_POST["url"])) {
	$arrData["url"] = trim(mb_convert_encoding($_POST["url"], $afterEncode, $beforeEncode));
} else if (isset($_GET["url"])) {
	$arrData["url"] = trim(mb_convert_encoding($_GET["url"], $afterEncode, $beforeEncode));
} else {
	// TODO:URLは必須、さらにGETでの空アクセスを制御(livedoor blog)
	exit();
}

// ブログ記事タイトル
if (isset($_POST["title"])) {
	$arrData["title"] = trim(mb_convert_encoding($_POST["title"], $afterEncode, $beforeEncode));
} else if (isset($_GET["title"])) {
	$arrData["title"] = trim(mb_convert_encoding($_GET["title"], $afterEncode, $beforeEncode));
}

// ブログ記事内容
if (isset($_POST["excerpt"])) {
	$arrData["excerpt"] = trim(mb_convert_encoding($_POST["excerpt"], $afterEncode, $beforeEncode));
} else if (isset($_GET["excerpt"])) {
	$arrData["excerpt"] = trim(mb_convert_encoding($_GET["excerpt"], $afterEncode, $beforeEncode));
}

$log_path = DATA_PATH . "logs/tb_result.log";
gfPrintLog("s1--------------------", $log_path);
foreach($arrData as $key => $val) {
	gfPrintLog( "\t" . $key . " => " . $val, $log_path);
}
gfPrintLog("s1--------------------", $log_path);

$objFormParam->setParam($arrData);

// 入力文字の変換
$objFormParam->convParam();
$arrData = $objFormParam->getHashArray();

// エラーチェック(トラックバックが成り立たないので、URL以外も必須とする)
gfPrintLog("--- ERROR CHECK START ---", $log_path);
$objPage->arrErr = lfCheckError();
gfPrintLog("--- ERROR CHECK FINISH ---", $log_path);
gfPrintLog("--- ERROR COUNT : " . count($objPage->arrErr), $log_path);

// エラーがない場合はデータを更新
if(count($objPage->arrErr) == 0) {
	
	// 商品コードの取得(GET)
	if (isset($_GET["pid"])) {
		$product_id = $_GET["pid"];

		gfPrintLog("--- PRODUCT ID : " . $product_id, $log_path);

		// 商品データの存在確認
		$table = "dtb_products";
		$where = "product_id = ?";

		// 商品データが存在する場合はトラックバックデータの更新
		if (sfDataExists($table, $where, array($product_id))) {
			$arrData["product_id"] = $product_id;
			
			// データの更新
			if (lfEntryTrackBack($arrData) == 1) {
				IfResponseOk();
			}
		} else {
			gfPrintLog("--- PRODUCT NOT EXISTS : " . $product_id, $log_path);
		}
	}
}

// NG
IfResponseNg();
exit();

//----------------------------------------------------------------------------------------------------

/* パラメータ情報の初期化 */
function lfInitParam() {
	global $objFormParam;
	$objFormParam->addParam("URL", "url", URL_LEN, "KVa", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
	$objFormParam->addParam("ブログタイトル", "blog_name", MTEXT_LEN, "KVa", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
	$objFormParam->addParam("記事タイトル", "title", MTEXT_LEN, "KVa", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
	$objFormParam->addParam("記事内容", "excerpt", MLTEXT_LEN, "KVa", array("EXIST_CHECK", "MAX_LENGTH_CHECK"));
}

/* 入力内容のチェック */
function lfCheckError() {
	global $objFormParam;
	
	// 入力データを渡す。
	$arrRet =  $objFormParam->getHashArray();
	$objErr = new SC_CheckError($arrRet);
	$objErr->arrErr = $objFormParam->checkError();
	
	return $objErr->arrErr;
}

/* 更新処理 */
function lfEntryTrackBack($arrData) {
	global $objQuery;

	// ログ
	$log_path = DATA_PATH . "logs/tb_result.log";

	// スパムフィルター
	if (lfSpamFilter($arrData)) {
		$arrData["status"] = TRACKBACK_STATUS_NOT_VIEW;
	} else {
		$arrData["status"] = TRACKBACK_STATUS_SPAM;
	}

	$arrData["create_date"] = "now()";
	$arrData["update_date"] = "now()";

	gfPrintLog("e--------------------", $log_path);
	foreach($arrData as $key => $val) {
		gfPrintLog( "\t" . $key . " => " . $val, $log_path);
	}
	gfPrintLog("e--------------------", $log_path);

	// データの登録
	$table = "dtb_trackback";
	$ret = $objQuery->insert($table, $arrData);

	gfPrintLog("INSERT RESULT : " . $ret, $log_path);
	return $ret;
}

/* スパムフィルター */
function lfSpamFilter($arrData, $run = false) {
	$ret = true;
	
	// フィルター処理
	if ($run) {
	}
	return $ret;
}

// OKレスポンスを返す
function IfResponseOk() {
	header("Content-type: text/xml");
	print("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>");
	print("<response>");
	print("<error>0</error>");
	print("</response>");
	exit();
}

// NGレスポンスを返す
function IfResponseNg() {
	header("Content-type: text/xml");
	print("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>");
	print("<response>");
	print("<error>1</error>");
	print("<message>The error message</message>");
	print("</response>");
	exit();
}
//-----------------------------------------------------------------------------------------------------------------------------------
?>
