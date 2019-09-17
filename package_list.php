<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
/* require('../common/jssdk.php'); */
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
mysql_query("SET NAMES UTF8");
/* $jssdk = new JSSDK($customer_id);
$signPackage = $jssdk->GetSignPackage(); */
$new_baseurl = "http://".$http_host; //新商城图片显示
require('../common/common_from.php'); 
/* $user_id 		= 202941;
$customer_id	= 3243;  */
if(!empty($_GET["package_user_id"])){
	$package_user_id	= $configutil->splash_new($_GET["package_user_id"]); //用户id
}
if(!empty($_GET["package_parent_id"])){
	$package_parent_id	= $configutil->splash_new($_GET["package_parent_id"]); //推荐人id
	$parent_id	= $package_parent_id;
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>礼品列表</title>
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
   	<!-- global css-->
    <link type="text/css" rel="stylesheet" href="assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="css/package/global.css" />
    <link type="text/css" rel="stylesheet" href="css/package/package_list.css" />
    <!-- global css-->
    
    <!-- basic js -->
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="assets/js/amazeui.js"></script>
    <!-- basic js -->
</head>
<body data-ctrl=true style="background:#f8f8f8;">
<!--悬浮按钮-->
<?php  include_once('float.php');?>
<!--悬浮按钮-->
	<!-- Loading Screen -->
	<!--<div id='loading' class='loadingPop'style="display: none; ">
		<div class = "h20-alt"></div>
		<img src='images/loading.gif'/>
		<p>数据加载中</p>
		<div class = "h5-alt"></div>
	</div>-->
	<!-- Loading Screen -->
	<!-- header部门-->
<!-- 	<header data-am-widget="header" class="am-header am-header-default header-wrapper">
		<div class="am-header-left am-header-nav header-btn">
			<img class="am-header-icon-custom"  src="images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">礼品列表</h1>
	    <div class="am-header-right am-header-nav">
		</div>
	</header> -->
	<!-- header部门-->
<!-- 	<div class = "header-alt"></div> --><!-- 暂时隐藏头部导航栏 -->
    <div class = "package">
		<ul class = "content-list" id="resultData" style="padding-top: 10px;">
		<?php
		$package_id     = 0;//礼包id
		$default_imgurl = "";//图片
		$query="SELECT 
				id,
				default_head_imgurl
				from package_list_t where isvalid=TRUE and isout=0 and customer_id=".$customer_id; 
		if(!empty($_GET["ptype"])){
			$ptype	= $configutil->splash_new($_GET["ptype"]); //礼包类型
			$level	= $configutil->splash_new($_GET["level"]); //类型等级
			$query .= " and package_type=".$ptype;
			if( $ptype == 2 ){
				$query .= " and shareholder_level=".$level;
			}elseif( $ptype == 3 ){
				$query .= " and three_level=".$level;
			}
		}
		if( !empty( $_GET["comeFrom"] ) ){
			$comeFrom  = $configutil->splash_new($_GET["comeFrom"]);
			if( $comeFrom == 1 ){
				$query .= " and package_type !=2";
			}
		}
		$result = mysql_query($query) or die('Query failed2: ' . mysql_error());
		while ($row = mysql_fetch_object($result)) {
			$package_id        = $row -> id;
			$default_imgurl    = $row -> default_head_imgurl;
			if( $default_imgurl == "" ){
				$default_imgurl		= "images/dalibao.jpg";
			}
		?>
    		<li class="itemWrapper">
				<div>
					<a href="package_detail.php?package_id=<?php echo $package_id; ?>&package_parent_id=<?php echo $parent_id; ?>">
						<img src="<?php echo $default_imgurl;?>">
					</a>
				</div>
				
			</li>
		<?php
		}
		?>	
		</ul>    
	</div>
	

<!-- <script src="js/mshop_share.js"></script> -->
<script>
debug=false;
/* appId='<?php echo $signPackage["appId"];?>';
timestamp=<?php echo $signPackage["timestamp"];?>;
nonceStr='<?php echo $signPackage["nonceStr"];?>';
signature='<?php echo $signPackage["signature"];?>'; */

share_url=""; //分享链接
title=""; //标题
desc=""; //分享内容
imgUrl=""//分享LOGO
share_type=3;//自定义类型
new_share(debug,appId,timestamp,nonceStr,signature,share_url,title,desc,imgUrl,share_type);
</script>
<script src="js/package/package_list.js"></script>
<?php require('../common/share.php'); ?>
</body>

</html>