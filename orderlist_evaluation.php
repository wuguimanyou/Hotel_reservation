<?php
/**
 * Created by PhpStorm. 订单评价
 * User: zhaojing
 * Date: 16/5/27
 * Time: 下午11:09
 */
header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
// require('../common/jssdk.php');
// $jssdk = new JSSDK($customer_id);           //实例化
// $signPackage = $jssdk->GetSignPackage()
require('../common/common_from.php'); 
require('select_skin.php');

// $customer_id = 3243;
if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
//$user_id = 196282;
$batchcode = -1;
if(!empty($_GET["batchcode"])){ //获取订单号
    $batchcode=$configutil->splash_new($_GET["batchcode"]);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>商品评价</title>
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
<link type="text/css" rel="stylesheet" href="./css/order_css/pingjia.css" />

<body data-ctrl=true>
<!-- <header data-am-widget="header" class="am-header am-header-default">
    <div class="am-header-left am-header-nav" onclick="history.go(-1)">
        <img class="am-header-icon-custom icon_back" src="./images/center/nav_bar_back.png"/><span>返回</span>
    </div>
    <h1 class="am-header-title" style="font-size:18px;">评价</h1>
</header>
<div class="topDiv"></div> --> <!-- 暂时屏蔽头部 -->

<!-- 基本地区-开始 -->
<form id="evalForm" action="./orderlist_save_evaluation.php" method="post" enctype="multipart/form-data">
    <div id="mainArea">
        <input type="hidden" name="batchcode" value="<?php echo $batchcode;?>">
        <?php
        $sql_pid = "select pid,is_discuss from weixin_commonshop_orders where isvalid=true and batchcode=".$batchcode;
        $result_pid = mysql_query($sql_pid) or die('query sql_pid failed'.mysql_error());
        $j = 0;
        while($row_pid = mysql_fetch_object($result_pid)){
            $pid = $row_pid->pid;

            $sql_prod = "select default_imgurl,is_supply_id from weixin_commonshop_products where isvalid=true and id=".$pid;
            $result_prod = mysql_query($sql_prod) or die('query sql_prod failed2'.mysql_error());
            if($row_prod = mysql_fetch_object($result_prod)){
                $product_default_imgurl = $row_prod->default_imgurl;	//产品封面图
                $supply_id = $row_prod->is_supply_id;					//代理商ID
            }
            ?>
            <div class="itemComment" style="width:100%;">
                <input type="hidden" name="pid[]" value="<?php echo $pid;?>">
                <input type="hidden" name="supply_id[]" value="<?php echo $supply_id;?>">
                <div id="middle-tab" style="background-color: white;" data-proid="<?php echo $pid;?>">
                    <div class="area-one select" style="border:none;">
                        <img class="border_goods" src="<?php echo $product_default_imgurl;?>">
                    </div>
                    <div class="area-one comment-mark sel"  data-level="1">
                        <img class="imgSel" src="./<?php echo $images_skin?>/order_image/icon_comment_good_sel-orange.png">
                        <img class="imgDef" src="./images/order_image/icon_comment_good.png">
                        <div>好评</div>
                    </div>
                    <div class="area-one comment-mark"  data-level="2">
                        <img class="imgSel"  src="./<?php echo $images_skin?>/order_image/icon_comment_middle_sel-orange.png">
                        <img class="imgDef"  src="./images/order_image/icon_comment_middle.png">
                        <div>中评</div>
                    </div>
                    <div class="area-one comment-mark"  data-level="3">
                        <img class="imgSel"  src="./<?php echo $images_skin?>/order_image/icon_comment_bad_sel-orange.png">
                        <img class="imgDef"  src="./images/order_image/icon_comment_bad.png">
                        <div>差评</div>
                    </div>
                    <input type="hidden" name="level[]" id="level_<?php echo $pid;?>" value="1" />
                </div>
                <div class="line_gray1"></div>
                <div class="frame_comment">
                    <div style="padding:12px;">
                        <span style="font-weight:bold;">评论</span>
                    </div>
                    <div class="frame_textarea">
                        <textarea name="discuss[]" id="discuss_<?php echo $pid;?>" placeholder="这次商品满意吗？写点心得5-100字。"></textarea>
                    </div>
                    <?php
                        $sql_ispic = "select is_pic from weixin_commonshops where isvalid = true and customer_id = ".$customer_id;
                        $result_ispic = mysql_query($sql_ispic) or die("sql_ispic query error : ".mysql_error());
                        $is_pic = 0;
                        if($row_ispic = mysql_fetch_object($result_ispic)){
                            $is_pic = $row_ispic -> is_pic;
                        }
                        if($is_pic > 0){
                    ?>
                    <div class="frame_image" id="frame_img_<?php echo $pid;?>">
                        <div class="area-one">
                            <img id="img_<?php echo $pid;?>_0" src="./images/order_image/icon_image_add.png" width="90" height="90">
                            <input type="file"  id="addFile_<?php echo $pid;?>_0"
                                   accept="image/*"
                                   class="frame_image_select"
                                   name="Filedata_<?php echo $pid;?>[]"
                                   data-pid="<?php echo $pid;?>">
                        </div>
                    </div>
                    <div class="frame_text">
                        <span>最多可上传3张图片</span>
                    </div>
                    <?php } ?>
                </div>
              <!--   <div class="line_gray10"></div> -->
            </div>
        <?php }?>
    </div>

    <!-- 基本地区-终结 -->

    <!-- 下面的按钮地区-开始 -->
    <div class="white-list">
        <div class="list-one">
            <div class="left-title"><span >匿名评价</span></div>
            <div class="center-content"></div>
            <div class="right-action"><input type="checkbox" onclick="checkNicheng();" id="checkbox_c1" class="chk_3"/><label for="checkbox_c1"></label></div>
            <input type="hidden" id="is_anonymous" name="is_anonymous" value="0">
        </div>
        <div class="list-one" style="background-color:#f8f8f8;">
            <div onclick="tijiao();" class="btn_type1">提交</div>
        </div>
    </div>
    <!-- 下面的按钮地区-终结 -->
</form>
</body>
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>
<script type="text/javascript" src="./assets/js/amazeui.js"></script>
<script type="text/javascript" src="./js/global.js"></script>
<script type="text/javascript" src="./js/loading.js"></script>
<script src="./js/jquery.ellipsis.js"></script>
<script src="./js/jquery.ellipsis.unobtrusive.js"></script>
<script type="text/javascript">
    var f_NiCheng = false;					//匿名评价

    $(function() {
        $(".frame_image").on("change",":file",function(){
            fileSelect_banner(this);
        });
        $(".comment-mark").click(function(){
            $(this).parent().find(".comment-mark").removeClass("sel");
            $(this).addClass("sel");
            var pid = $(this).parent().data("proid");
            $("#level_"+pid).val($(this).data("level"));
        });
    });


    //匿名评价
    function checkNicheng(){
        if(f_NiCheng){
            f_NiCheng = false;
            $('#is_anonymous').val(0);
        }else{
            f_NiCheng = true;
            $('#is_anonymous').val(1);
        }
    }

    //点击【商品图片】
    function gotoProductDetail(productID){
        alert("跳转到商品信息页面---"+productID);
    }

    //点击【提交】
    function tijiao(){
        $("#evalForm").submit();
    }

    //获取本地的图片
    function fileSelect_banner(evt) {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            currfile = evt;
            var files = evt.files;//直接传入file对象，evt.target改成evt
            var pid = $(evt).data("pid");	//现在选择的商品的pid
            var file;
            file = files[0];
            if (!file.type.match('image.*')) {
                return;
            }
            reader = new FileReader();
            reader.onload = (function (tFile) {
                return function (evt) {
                    dataURL = evt.target.result;
                    var imgcount = $("#frame_img_"+pid).find(".area-one").length;
                    if(imgcount < 3){
                             var html = $("#frame_img_"+pid).html();
                             var content = ' <div class="area-one">'
                                +'<img id="img_'+pid+'_'+imgcount+'" src="./images/order_image/icon_image_add.png" width="90" height="90">'
                                +'<input type="file" id="addFile_'+pid+'_'+imgcount+'"'
                                +'accept="image/*"'
                                +'class="frame_image_select"' 
                                
                                +'name="Filedata_'+pid+'[]"'
                                +'data-pid="'+pid+'" />'
                                +'</div>';
                            $("#frame_img_"+pid).append(content);
                        }
                    $(currfile).prev("img").eq(0).attr("src",dataURL);
                    // $(currfile).hide();
                    }
            }(file));
            reader.readAsDataURL(file);
            sendFile = file;
        } else {
            alert('该浏览器不支持文件管理。');
        }
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