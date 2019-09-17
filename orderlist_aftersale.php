<?php
/**
 * Created by PhpStorm. 申请售后操作
 * User: zhaojing
 * Date: 16/6/1
 * Time: 下午10:16
 */
header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('../common/jssdk.php');
require('select_skin.php');
$batchcode = -1;
$pid = -1;
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
$pid = -1;
if(!empty($_GET["pid"])){ //商品编号
    $pid=$configutil->splash_new($_GET["pid"]);
}
$prvalues = "";
if(!empty($_GET["prvalues"])){ //商品属性
    $pid=$configutil->splash_new($_GET["prvalues"]);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>申请售后</title>
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

    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>

</head>

<link rel="stylesheet" href="./css/order_css/style.css" type="text/css" media="all">
<link rel="stylesheet" href="./css/order_css/tuihuo.css" type="text/css" media="all">

<!-- 基本dialog-->
<link type="text/css" rel="stylesheet" href="./css/goods_css/dialog.css" />
<link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />


<body data-ctrl=true>
<!-- <header data-am-widget="header" class="am-header am-header-default">
    <div class="am-header-left am-header-nav" onclick="history.go(-1)">
        <img class="am-header-icon-custom" src="./images/center/nav_bar_back.png"/><span>返回</span>
    </div>
    <h1 class="am-header-title" style="font-size:18px;">退换货</h1>
</header>
<div class="topDiv"></div> -->  <!-- 暂时隐藏头部导航栏 -->
<div class="m_content" style="overflow-y:auto">
<form id="tijiaoFrom" action="./orderlist_save_aftersale.php?customer_id=<?php echo $customer_id_en;?>&user_id=<?php echo passport_encrypt($user_id);?>" method="post" enctype="multipart/form-data">
<!-- 换货请直接联系客服 -->
<div class="white-list link-kefu">
    <div class="re_type" style="display:inline-block;height:50px;line-height:50px;margin-left:12px;overflow:hidden;">
        <input style="float:left;margin-top:18px" type="radio" name="re_type" id="refund" value="1" checked="checked" ><label style="float:left;margin-right:5px" id="for_refund" for="refund" style="margin-left:5px;">退款</label>
        <input style="float:left;margin-top:18px" type="radio" name="re_type" id="returngoods" value="2" style="margin-left:5px;" ><label style="float:left" id="for_returngoods" for="returngoods" style="margin-left:5px;margin-right:5px">退货</label>
        <input style="float:left;margin-top:18px" type="radio" name="re_type" id="changegoods" value="3" style="margin-left:5px;"><label style="float:left" id="for_changegoods"for="changegoods" style="margin-left:5px;margin-right:5px">换货</label>
    </div>
		<span class="text_right exchange_goods">
			<span>联系客服</span>
		</span>
</div>
<?php
    $totalprice = 0 ; //单价商品总价
    $rcount = 0 ;//数量
    $sendstatus = 0; //发货状态
    $supply_id = 0;
    $sql_changeprice = "select totalprice from weixin_commonshop_changeprices where status=1 and isvalid=1 and batchcode='".$batchcode."' order by id desc limit 1";
    $result_cp = mysql_query($sql_changeprice) or die('Query sql_changeprice failed: ' . mysql_error());
    if ($row_cp = mysql_fetch_object($result_cp)) {
        $totalprice = $row_cp->totalprice;
    }else{
        //查询订单价格表中的记录
        $sql_price = "select price,NoExpPrice,ExpressPrice from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."'";
        $result_price = mysql_query($sql_price) or die('Query sql_price failed: ' . mysql_error());
        if ($row_price = mysql_fetch_object($result_price)) {
            //获取订单的真实价格（可能是折扣总价）
            $totalprice    = $row_price->price;
        }
    }
    $sql_query = "select rcount,sendstatus,supply_id from weixin_commonshop_orders
            where isvalid = true
              and batchcode = '".$batchcode."' ";
    $result = mysql_query($sql_query) or die("sql_query error : ".mysql_error());
    if($row = mysql_fetch_object($result)){

        $rcount = $row -> rcount;
        $sendstatus = $row -> sendstatus;
        $supply_id = $row -> supply_id;
    }
	$pay_batchcode = '';
	$query_paybatchcode = "select pay_batchcode from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and batchcode='".$batchcode."' limit 1";
	$result_paybatchcode = mysql_query($query_paybatchcode) or die('query_paybatchcode failed:'.mysql_error());
	while($row_paybatchcode =mysql_fetch_object($result_paybatchcode)){
		$pay_batchcode = $row_paybatchcode->pay_batchcode;
	}
	
	$coupon	= 0;	//代金券金额
	$query_cac = "select coupon from order_currencyandcoupon_t where user_id=".$user_id." and customer_id=".$customer_id." and pay_batchcode='".$pay_batchcode."'";
	$result_cac = mysql_query($query_cac) or die('query_cac failed:'.mysql_error());
	while($row_cac = mysql_fetch_object($result_cac)){
		$coupon	= $row_cac->coupon;
	}
?>
<!-- 基本数据地区 - 开始 -->

    <div id="mainArea">
        <input type="hidden" name="dotype" value="<?php if($sendstatus==0){echo 'refund';}else if($sendstatus==1){echo 'backgoods';}else{echo 'aftersale';}?>">
        <input type="hidden" name="batchcode" value="<?php echo $batchcode;?>">
       <!-- <input type="hidden" name="pid" value="<?php echo $pid;?>">
        <input type="hidden" name="supply_id" value="<?php echo $supply_id;?>">
        <input type="hidden" name="prvalues" value="<?php echo $prvalues;?>"> -->
        <!-- 退货原因 -->
        <div onclick="selectTuiKuanReason();" class="white-list frame_reason">
            <div class="list-one">
                <div class="left-title" style="width:30%"><span class="change-text1">退货原因</span></div>
                <div style="float:right;margin-right:10px;">
                    <span id="selectedReason">请选择</span>
                    <input type="hidden" id="re_reason" name="re_reason" value="">
                    <img class="btn_right_arrow" src="./images/order_image/btn_right.png">
                </div>
            </div>
        </div>
        <div class="line_gray10"></div>

        <!-- 退货原因描述 -->
        <div class="itemComment" style="width:100%;" goodsId="1">
            <div id="frame_image" class="white-list frame_reason" style="height:300px;">
                <div class="list-one change-text2" style="margin-left:10px;">退货原因描述</div>
                <div class="frame_reason_textarea">
                    <textarea name="re_remark" id="reasonContent" placeholder="请填写您遇到的问题，最多125字。 "maxlength="125"></textarea>
                </div>

                <!-- 图片地区 -->
                <div id="image-area" class="pic_0">
                    <div class="area-one">
                        <img id="img_0" src="./images/order_image/icon_image_add.png" class="frame_image_select">
                        <input type="file" id="addfile_0" accept="image/*" name="filedata[]" class="frame_image_select" style="opacity:0">
                    </div>
                </div>
                <div id="div_tip" class="text_gray_13">最多可上传3张图片</div>
            </div>
        </div>
        <div class="line_gray10"></div>

        <!-- 退款金额 -->
        <div class="white-list frame_reason">
            <!--
            <div class="list-one" style="padding:15px 0 0 0;font-size:15px;">
                <div class="left-title">退货数量</div>
                <div class="right"><div class="digit-pane"><div class="minus">-</div><div class="count" id="div_count"><?php echo $rcount;?></div><div class="plus">+</div></div></div>
            </div> -->
            <input type="hidden" name="return_count" id="return_count" value="1"/>
            <div class="list-one" id="div_account" style="padding:15px 0;font-size:15px;">
                <div class="left-title">退款金额</div>
                <div class="div-money"><input id="money" name="return_account" type="text" onkeyup="clearNoNum(this)" onafterpaste="clearNoNum(this)" onchange="clearNoNum(this)" placeholder="请输入退款金额" style="border:none;"/></div>
            </div>
        </div>
        <div class="line" style="width:100%;height:1px;background:#eee;"></div>
        <div class="white-box" id="div_show_account">
            <p class="font-grey;">仅可退款金额<span class="font-red">￥<?php echo round($totalprice,2); //round($totalprice / $rcount ,2);?></span></p>
        </div>

    </div>
</form>
</div>
<!-- 基本数据地区 - 终结 -->

<!-- 下面的【提交】按钮地区 -->
<div class="white-list frame_button_area">
    <div class="list-one" >
        <div onclick="tijiao();" style="color: #fff;" class="btn_bottom">提交</div>
    </div>
</div>

<!-- 弹出来的【选择退款原因】窗口 - 开始 -->
<div id="reasonSelectArea">
    <div class="frame_list">
        <div onclick="reasonSelect(this,1);" class="item_list">质量原因</div>
        <div onclick="reasonSelect(this,2);" class="item_list">商品信息描述不好</div>
        <div onclick="reasonSelect(this,3);" class="item_list">功能/效果不好</div>
        <div onclick="reasonSelect(this,4);" class="item_list">少件/漏件</div>
        <div onclick="reasonSelect(this,5);" class="item_list">包装/商品破损</div>
        <div onclick="reasonSelect(this,6);" class="item_list">发票问题</div>
        <div onclick="reasonSelect(this,7);" class="item_list" style="border-bottom:none;">其他</div>
    </div>
</div>
<!-- 弹出来的【选择退款原因】窗口 - 终结 -->

</body>
<script type="text/javascript">

    var imageCount = 0;
    var tuikuanReason = -1;//退款原因
    var maxcount = '<?php echo $rcount;?>';
    var unitprice = <?php echo round($totalprice / $rcount ,2);?>;
    var totalprice = <?php echo round($totalprice ,2);?>;
    var sendstatus = '<?php echo $sendstatus; ?>';

    //设置主内容高度
    var wh=$(window).height();
    $('.m_content').height(wh-65);

    //点击【换货请直接联系客服】
    $(".exchange_goods").click(function(){
        showAlertMsg ("提示：","换货请直接联系客服","知道了");
    });

    $(function(){
        if(sendstatus > 1){
            $("#refund").hide();
            $("#for_refund").hide();
            $("#returngoods").attr("checked","checked");
        }
        if(sendstatus == 0){
            $("#returngoods").hide();
            $("#for_returngoods").hide();
            $("#changegoods").hide();
            $("#for_changegoods").hide();
        }
    });

    $("input[name='re_type']").click(function(){ //根据选择退货或换货、显示不同的文字
        var val = $(this).val();
        if(val == 3){
            $("#div_account").hide();
            $("#div_show_account").hide();
            $('.change-text1').text('换货原因');
            $('.change-text2').text('换货原因描述');
        }else{
            $("#div_account").show();
            $("#div_show_account").show();
            $('.change-text1').text('退货原因');
            $('.change-text2').text('退货原因描述');
        }
    });

    //选择一个【退款原因】，从【退款原因】列表
    function reasonSelect(obj,kind){
        tuikuanReason = kind;
        $("#selectedReason").html($(obj).html());
        $("#re_reason").val($(obj).html());
        $("#reasonSelectArea").hide();
    }

    //点击上面的【请选择-退款原因】
    function selectTuiKuanReason(){
        $("#reasonSelectArea").show();
    }

    //点击【提交】
    function tijiao(){
        if(tuikuanReason == -1){
            showAlertMsg ("提示：","请选择退款原因","知道了");
            return;
        }

        var reasonContent = $("#reasonContent").val();
        if(reasonContent == ""){
            showAlertMsg ("提示：","请输入退货原因描述","知道了");
            return;
        }


       // var count = $("#div_count").html();
        var type = $(":radio").filter(":checked").val();
        if(type == 3){
            $("#money").val(0);
        }
        console.log(type);
        var inputMoney = $("#money").val();

        if(isNaN(inputMoney) || inputMoney==""){
            showAlertMsg ("提示：","请正确输入退款金额！","知道了");
            return;
        }
        //if(inputMoney > parseInt(count)*unitprice){
        if(inputMoney > totalprice){
            showAlertMsg ("提示：","输入金额不能大于总价！","知道了");
            return;
        }
		if(inputMoney < 0){
            showAlertMsg ("提示：","输入金额不能小于0！","知道了");
            return;
        }
       // console.log("inputMoney : "+inputMoney+" count : "+count+" unitprice : "+unitprice);

       // $("#return_count").val(count); //退货数量

        $("#tijiaoFrom").submit();
    }

    $("#frame_image").on("change",":file",function(){
        fileSelect_banner(this);
    });

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
                    var imgcount = $("#frame_image").find(".area-one").length;
                    if(imgcount < 3){
                        var content = '<div id="image-area" class="pic_'+imgcount+'">'
                                        +'<div class="area-one">'
                                        +'<img id="img_'+imgcount+'" src="./images/order_image/icon_image_add.png" class="frame_image_select">'
                                        +'<input type="file" id="addfile_'+imgcount+'" accept="image/*" name="filedata[]" class="frame_image_select" style="opacity:0">'
                                        +'</div>'
                                        +'</div>';
                        $(content).insertBefore("#div_tip");
                    }
                    $(currfile).prev("img").eq(0).attr("src",dataURL);
                };
            }(file));
            reader.readAsDataURL(file);
            sendFile = file;
        } else {
            showAlertMsg ("提示：","该浏览器不支持文件管理。","知道了");
        }
    }

    //点击minus
    $(".minus").click(function(){
        value = $(this).parent().find(".count").html();
        value = value*1;
        if(value > 1) value--;
        $(this).parent().find(".count").html(value);

        var t_price = unitprice*value;
        $("#small-price").html("￥"+t_price);
        $("#total-price").html("￥"+t_price);
    });

    //点击plus
    $(".plus").click(function(){
        if(value >= parseInt(maxcount)){
            return;
        }
        value = $(this).parent().find(".count").html();
        value++;
        $(this).parent().find(".count").html(value);
        var t_price = unitprice*value;
        $("#small-price").html("￥"+t_price);
        $("#total-price").html("￥"+t_price);
    });
	
/*只能输入数字和两位小数*/
function clearNoNum(obj){
	obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
	obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
	obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
	obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
	obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
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