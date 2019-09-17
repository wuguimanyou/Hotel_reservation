<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('../common/utility.php');
require('../common/utility_shop.php');
//头文件----start
require('../common/common_from.php');
//头文件----end
require('select_skin.php');
define("InviteUrl","http://".$http_host."/weixinpl/mshop/WeChatPay/weipay_payother.php?customer_id=");
$linkurl =InviteUrl.$customer_id_en;

$payother_desc_id = $_GET["payother_desc_id"];

$linkurl = $linkurl."&payother_desc_id=".$payother_desc_id;
$linkurl = $linkurl."&user_id=".$user_id."&showwxpaytitle=1";

$pay_batchcode = '';
if(!empty($_GET['pay_batchcode'])){
	$pay_batchcode = $_GET['pay_batchcode'];
	$linkurl = $linkurl."&pay_batchcode=".$pay_batchcode;
}

$weixin_name 	 = '';	//微信名
$headimgurl  	 = '';	//头像
$weixin_fromuser = '';
$query="select weixin_name,weixin_headimgurl,weixin_fromuser from weixin_users where isvalid=true and id=".$user_id." limit 0,1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$weixin_name = "";
$headimgurl  = "";
while ($row = mysql_fetch_object($result)) {
   $weixin_name 	 = $row->weixin_name;
   $headimgurl  	 = $row->weixin_headimgurl;
   $weixin_fromuser  = $row->weixin_fromuser;
}

if(empty($headimgurl) || empty($weixin_name)){	//获取个人信息
	$qr =  new qr_Utlity();
	$qr->GetNewWeixinName($customer_id,$weixin_fromuser,$user_id);
}

$query = "select pay_desc,batchcode from weixin_commonshop_otherpay_descs where isvalid=true and id=".$payother_desc_id;
$paydesc   = "";
$batchcode = "";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $paydesc	  = $row->pay_desc;
   $batchcode = $row->batchcode;
}

$pid = -1;
$query = "select pid from weixin_commonshop_orders where isvalid=true and pay_batchcode='".$batchcode."' limit 0,1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
   $pid = $row->pid;
}

$new_baseurl = "http://".CLIENT_HOST;
$product_img = "";
$query = 'SELECT default_imgurl FROM weixin_commonshop_products where  isvalid=true and id='.$pid;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$default_imgurl = $row->default_imgurl;
	$product_img 	= $new_baseurl.$default_imgurl;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>找人代付</title>
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
    <link type="text/css" rel="stylesheet" href="./css/extends_css/extends.css" />
    <link type="text/css" rel="stylesheet" href="./css/goods/dialog.css" />
    <link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />
    <link type="text/css" rel="stylesheet" href="./css/personal.css" />
    
</head>
<style>
.am-header-icon-custom{height:16px;margin-left:2px;}
 .alert {overflow: auto;margin-bottom:0px;}
  
</style>
<body data-ctrl=true style="background:white;">
	<!-- Loading Screen -->
	<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif'/><p>数据加载中</p></div>
	<!-- Loading Screen -->
	<!-- header部门-->
	<!--<header data-am-widget="header" class="am-header am-header-default header-wrapper">
		<div class="am-header-left am-header-nav header-btn" onclick="goBack();">
			<img class="am-header-icon-custom"  src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">找人代付</h1>
	    <div class="am-header-right am-header-nav" onclick="">
			<img class="am-header-icon-custom" src="./images/extends_image/more_btn.png" style="height:6px" />
		</div>
	</header> -->
	<!-- header部门-->
    <div id="containerDiv" class="detail">
    	<div class="last-top"></div>	
    	
	    <div class="content_top" style="background:white;padding-top:0px;">
			<div class="details" style="padding:15px;">
	            <img class="am-img-thumbnail am-circle" src="<?php echo $headimgurl;?>" alt="" style="width: 80px;border:none;"/>
	        </div>
	    </div>
	    
    	<div class="daifu_tixing" style="font-size:15px;">
	        <?php echo $paydesc;?>
	    </div>
	    <div class="btn" onclick="commit()" ><span style="letter-spacing:0px!important;">找小伙伴帮忙付款</span></div>
	</div>
	<!-- global js -->
	<script type="text/javascript" src="./js/loading.js"></script>
	<script src="./js/global.js"></script>
	
	<!-- global js -->
</body>
<script type="text/javascript">
var user_id 		 = '<?php echo $user_id;?>';
var payother_desc_id = '<?php echo $payother_desc_id;?>';
var customer_id_en   = '<?php echo $customer_id_en;?>';
var pay_batchcode    = '<?php echo $pay_batchcode;?>';
var from_type    	 = '<?php echo $from_type;?>';

function commit(){
	var msg_content = '';
	if(1==from_type){
		msg_content = '<div class="method" style = "overflow:auto;">';
		msg_content +=' 	<div class="method-left" style="padding-top:3px;">';
		msg_content +='			方法一：'; 
		msg_content +='		</div>';
		msg_content +='		<div class="method-right">';
		msg_content +='			点击上角 <img src="./images/extends_image/more.png"/> 图标,然后';
		msg_content +='			<img src="./images/extends_image/zhuan.png"/>转发给好友或<img src="./images/extends_image/tencent.png"/>分享到朋友圈。';
		msg_content +='		</div>';
		msg_content +='</div>';
		msg_content += '<div class="method">';
		msg_content +=' 	<div class="method-left">';
		msg_content +='			方法二：'; 
		msg_content +='		</div>';
		msg_content +='		<div class="method-right">';
		msg_content +='			邀请好友扫描二维码 <img class="qr_img" src="" style="width:130px;"/>';
		msg_content +='		</div>';
		msg_content +='</div>';
	}else{
		msg_content += '<div class="method">';
		msg_content +=' 	<div class="method-left">';
		msg_content +='			方法：'; 
		msg_content +='		</div>';
		msg_content +='		<div class="method-right">';
		msg_content +='			邀请好友扫描二维码 <img class="qr_img" src="" style="width:130px;"/>';
		msg_content +='		</div>';
		msg_content +='</div>';
	}
		showAlertMsg("",msg_content,"知道了");
		
	$.ajax({		//获取二维码
		url: 'get_payother_url.php?customer_id='+customer_id_en,
		data:{
			user_id:user_id,
			payother_desc_id:payother_desc_id,
			pay_batchcode:pay_batchcode
		},
		dataType: 'json',
		type: 'post',
		async: true,
		success:function(res){
			$('.qr_img').attr('src',res);
		},
		error:function(er){
		
		}
	});
}

</script>
<!--引入微信分享文件----start-->
<script>
<!--微信分享页面参数----start-->
debug=false;
share_url='<?php echo $linkurl;?>'; //分享链接
title="我想对你说:"; //标题
desc="<?php echo $paydesc;?>"; //分享内容
imgUrl="<?php echo $product_img;?>"//分享LOGO
share_type=1;//自定义类型
<!--微信分享页面参数----end-->
</script>
<?php require('../common/share.php');?>
<!--引入微信分享文件----end-->
<!--引入侧边栏 start-->
<?php  include_once('float.php');?>
<!--引入侧边栏 end-->
</html>