<?php
/**
 * 退货时填写退货单
 * User: zhaojing
 * Date: 16/6/18
 * Time: 上午1:15
 */
header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
// require('../common/jssdk.php');
// $jssdk = new JSSDK($customer_id);
// $signPackage = $jssdk->GetSignPackage();
//头文件----start
require('../common/common_from.php');
require('select_skin.php');
// $user_id = 194631;
//头文件----end

?>
<!DOCTYPE html>
<html>
<head>
    <title>填写退货单</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta content="no" name="apple-touch-fullscreen">
    <meta name="MobileOptimized" content="320"/>
    <meta name="format-detection" content="telephone=no">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <meta http-equiv="pragma" content="nocache">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">

    <link type="text/css" rel="stylesheet" href="./assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" />
    <link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" /> 


</head>

<link rel="stylesheet" href="./css/order_css/style.css" type="text/css" media="all">
<link rel="stylesheet" href="./css/order_css/tuihuo.css" type="text/css" media="all">

<!-- 基本dialog-->
<link type="text/css" rel="stylesheet" href="./css/goods/dialog.css" />
<link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />


<body data-ctrl=true>

<!-- 基本数据地区 - 开始 -->
<form id="returnForm" action="orderlist_operation.php?op=save_return&batchcode=<?php echo $configutil->splash_new($_GET["batchcode"])?>" method="post" >
    <div id="mainArea">
        <div class="line_gray10"></div>

        <!-- 快递 -->
        <div class="white-list frame_reason">
            <div class="list-one" style="padding:15px 0 0 0;font-size:15px;height:40px">
                <div class="left-title">快递名称</div>
                <div class="div-money"><input id="txt_expressName" name="express_name" type="text" placeholder="请输入快递名称" style="border:none;"/></div>
            </div>
        </div>
        <div class="line_gray10"></div>
        <!-- 快递单号 -->
        <div class="white-list frame_reason">
            <div class="list-one" style="padding:15px 0 0 0;font-size:15px;height:40px">
                <div class="left-title">快递单号</div>
                <div class="div-money"><input id="txt_expressNum" name="express_num" type="text" placeholder="请输入快递单号" style="border:none;"/></div>
            </div>
        </div>
        <div class="line_gray10"></div>
        <!-- 商家备注 -->
        <div class="itemComment" style="width:100%;" goodsId="1">
            <div id="frame_image" class="white-list frame_reason" style="height:200px;">
                <div class="list-one" style="margin-left:10px;">备注</div>
                <div class="frame_reason_textarea">
                    <p id="txt_remark"></p>
                    <textarea name="remark" style="width:100%;resize:none;border:none;height:100px;"></textarea>
                </div>
            </div>
        </div>
        <div class="line_gray10"></div>


    </div>
</form>
<!-- 基本数据地区 - 终结 -->

<!-- 下面的【提交】按钮地区 -->
<div class="white-list frame_button_area">
    <div class="list-one" >
        <div onclick="doSubmit();" class="btn_bottom">提交</div>
    </div>
</div>


</body>
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/js/amazeui.js"></script>
<script type="text/javascript" src="./js/global.js"></script>
<script type="text/javascript">
    function doSubmit(){
        var e_name = $("#txt_expressName").val();
        var e_num = $("#txt_expressNum").val();
        if(e_name == ""){
            showAlertMsg("操作提示","请填写快递名","好的",function(){
                $("#txt_expressName").focus();
            });
            return;
        }
        if(e_num == ""){
            showAlertMsg("操作提示","请填写快递号","好的",function(){
                $("#txt_expressNum").focus();
            });
            return;
        }
        //$("#hid_remark").val($("#txt_remark").text());
        $("#returnForm").submit();
    }
</script>
<!--引入微信分享文件----start-->
<script>
<!--微信分享页面参数----start-->
debug=false;
share_url=''; //分享链接
title=""; //标题
desc=""; //分享内容
imgUrl="";//分享LOGO
share_type=3;//自定义类型
<!--微信分享页面参数----end-->
</script>
<?php require('../common/share.php');?>
<!--引入微信分享文件----end-->
</body>
</html>