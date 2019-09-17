<?php
/**
 * Created by PhpStorm.  用于我的订单翻页
 * User: zhaojing
 * Date: 16/5/27
 * Time: 上午2:16
 */
header("Content-type: text/html; charset=utf-8");
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]


$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

// $user_id = -1;


if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
if(!empty($_GET["apptype"])){
    $apptype=$configutil->splash_new($_GET["apptype"]);
}else{
    if(!empty($_SESSION["apptype".$customer_id])){
        $apptype=$_SESSION["apptype".$customer_id];
    }
}
$currtype= 1;
if(!empty($_GET["currtype"])){
    $currtype = $_GET["currtype"];
}

$pagenum = 1;
if(!empty($_GET["pagenum"])){
    $pagenum = $_GET["pagenum"];
}
require('orderlist_prods.php');



