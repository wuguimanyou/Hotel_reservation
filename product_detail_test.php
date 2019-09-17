<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
//require('../back_init.php'); 
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
 
//产品ID
$pid = -1;
if(!empty($_GET["pid"])){
	$pid = $configutil->splash_new($_GET["pid"]);
}

 

define("InviteUrl","http://".$http_host."/weixinpl/common_shop/jiushop/forward.php?type=2&customer_id=");
$linkurl =InviteUrl.$customer_id_en."&pid=".$pid;
//自身推广出去带自己的推广码
$linkurl =$linkurl."&exp_user_id=".passport_encrypt((string)$user_id);
/* $owner_id 		= -1;
 $customer_id    = 3243;
$pid            = 1568;
$user_id 	 	= 191099 ; */
$new_baseurl = "http://".$http_host; //新商城图片显示
//初始化--star

$sid                = -1;	//供应商ID
$pro_reward			= 0;	//产品分佣比例
$isvp	      		= 0;	//是否为vp产品
$is_QR              = -1;	//是否 二维码产品 0:否 1:是
$isout              = 0;	//上架下架, 1:下架 0:上架
$p_name             = "";	//产品名
$weight             = 0;	//产品重量
$p_unit             = "";	//单位
$type_id            = 0;	//所以分类id
$issnapup			= 0;	//是否抢购，1：是，0：否
$vp_score      	    = 0;	//单个vp值
$introduce          = "";	//简短介绍
$meu_level          = 0;	//中评数
$bad_level          = 0;	//差评数
$sell_count			= 0;	//销售量
$is_virtual    		=  0;	//是否为虚拟产品 0:非虚拟产品,1:虚拟产品
$good_level         = 0;	//好评数
$freight_id    		= -1;	//运费模板ID
$p_storenum         = 1;	//库存
$is_invoice         = -1;	//是否开启发票
$propertyids        = "";	//属性id
$for_price        	= 0;	//成本价
$cost_price        	= 0;	//供货价
$p_now_price        = 0;	//现价
$description        = "";	//详细介绍
$p_need_score       = 0;	//购买产品需要的积分
$pis_identity        = 1;	//产品是否需要身份证购买开关
$p_orgin_price      = 0;	//原价
$donation_rate      = 0;	//单品捐赠比率
$back_currency		= 0;	//购物币返佣金额
$pro_card_level		= 0;	//购买产品需要会员卡开关
$nowprice_title     = "";	//现价的自定义名称
$specifications 	= "";	//产品规格
$default_imgurl     = "";	//封面图片
$show_sell_count	= 0;	//虚拟销售量
$is_free_shipping	= 0;	//是否包邮，1是，0否
$customer_service  	= "";	//售后保障
$pro_card_level_id  = -1;	//购买产品需要会员卡等级
$is_guess_you_like	= 0;	//是否是猜您喜欢开关：0否1是
$define_share_image = '';	//自定义分先图片 产品分享图片
$first_division		= 0;	//一级分佣金额
$buystart_time		= "";	//商品抢购开始时间
$countdown_time		= "";	//商品抢购倒计时(结束时间)
$product_voice		= "";	//商品语音链接
$product_vedio		= "";	//商品视频链接
$shop_card_id		= -1;	//分销会员卡
$brand_supply_name 	= "";	//品牌供应商店铺名
$brand_tel	 		= "";	//品牌供应商店铺电话
$brand_logo 		= "";	//品牌供应商店铺logo
$advisory_flag		= "";	//咨询电话开关
$is_showdiscuss		= "";	//评论开关
$isOpenSales		= "";	//商城产品销量开关
$init_reward		= 0;	//商城分佣比例
$cashback_r			= -1;	//返现金额（产品价格按比例）
$cashback			= -1;	//返现金额（固定金额）
$isvalid			= true;
$issell				= 0;	//是否开启分销
$issell_model		= 0;	//是否开启分销

$query = 'SELECT 	
			id,
			isvp,
			pro_reward,
			name,
			is_QR,
			weight,
			type_id,
			cashback,
			cashback_r,
			cost_price,
			for_price,
			vp_score,
			issnapup,
			storenum,
			meu_level,
			bad_level,
			now_price,
			introduce,
			is_invoice,
			is_virtual,
			freight_id,
			unit,isout,
			sell_count,
			need_score,
			good_level,
			orgin_price,
			description,
			propertyids,
			is_identity,
			is_supply_id,
			donation_rate,
			back_currency,
			product_vedio,
			product_voice,
			buystart_time,
			default_imgurl,
			nowprice_title,
			countdown_time,
			specifications,	
			show_sell_count,
			customer_service,
			is_free_shipping,
			pro_card_level_id,
			is_guess_you_like,
			define_share_image
			FROM weixin_commonshop_products where  isvalid=true and id=' . $pid;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());

while ($row = mysql_fetch_object($result)) {
	$pro_reward			 = $row->pro_reward;
	$sid                 = $row->is_supply_id;
	$isvp		         = $row->isvp;
	$isout               = $row->isout;
    $for_price			 = $row->for_price;
    $cost_price			 = $row->cost_price;
    $is_QR			     = $row->is_QR;
    $p_name              = $row->name;
	$p_unit              = $row->unit;
	$cashback			 = $row->cashback;  
	$cashback_r			 = $row->cashback_r;  
	$weight              = $row->weight;  
	$meu_level           = $row->meu_level;
    $bad_level           = $row->bad_level;
	$introduce           = $row->introduce;
    $p_storenum          = $row->storenum;
	$good_level          = $row->good_level;
	$sell_count          = $row->sell_count;
    $p_need_score        = $row->need_score;
    $p_now_price         = $row->now_price;
    $description         = $row->description;
    $propertyids         = $row->propertyids;
	$p_orgin_price       = $row->orgin_price;
	$default_imgurl      = $row->default_imgurl;
    $show_sell_count     = $row->show_sell_count;
    $define_share_image  = $row->define_share_image;
    $nowprice_title      = $row->nowprice_title;
	$pro_card_level_id   = $row->pro_card_level_id;
	$pis_identity         = $row->is_identity;
	$donation_rate       = $row->donation_rate;
	$is_invoice		     = $row->is_invoice;
	$vp_score	         = $row->vp_score;
	$freight_id			 = $row->freight_id;
	$is_virtual			 = $row->is_virtual;
    $type_id             = $row->type_id;
    $specifications      = $row->specifications;
	$customer_service    = $row->customer_service;
	//$first_division    	 = $row->first_division;
	$is_free_shipping	 = $row->is_free_shipping;
	$back_currency		 = $row->back_currency;
	$issnapup		 	 = $row->issnapup;
	$buystart_time		 = $row->buystart_time;
	$countdown_time		 = $row->countdown_time;
	$product_voice		 = $row->product_voice;	
	$product_vedio		 = $row->product_vedio;		
	$is_guess_you_like	 = $row->is_guess_you_like;		
}

$introduce 				 = str_replace("\r","",$introduce);
$introduce 				 = str_replace("\n","",$introduce);
$total_evaluate 		 = $good_level+$meu_level+$bad_level;	//评论总数
$buystart_time_str		 = strtotime($buystart_time);
$countdown_time_str		 = strtotime($countdown_time);
 
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $p_name;?></title>
    <!-- 模板 -->
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
    
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <!-- 模板 -->
    <!-- 页联系style-->
    <link type="text/css" rel="stylesheet" href="./css/goods/global.css" />
    <link type="text/css" rel="stylesheet" href="./css/goods/product_detail.css" />
	<link type="text/css" rel="stylesheet" href="css/order_css/global.css" />
	<link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" /> 	
    <!-- 页联系style-->
    <style type="text/css">
    	 //ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }
		.bi{
		background:#F98C3E;
		margin-right: 4px;
	  }
	  .shangpin-addprice .span {
			line-height: 36px;
		}
    </style>
</head>
 
<body data-ctrl='true' >
	<!-- header部门-->
	<!-- <header data-am-widget="header" class="am-header am-header-default header">
		<div class="am-header-left am-header-nav header-btn" onclick="goBack();">
			<img class="am-header-icon-custom"  src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">商品详情</h1>
	    <div class="am-header-right am-header-nav">
		</div>
	</header>
	<div class="topDiv" style="height:49px;"></div>  -->  <!-- 暂时屏蔽头部 -->
	<!-- header部门-->
	
    <div id="containerDiv">
    	 
    	<div class = "shangpin-content shangpin-second1 shangpin-content-second">
			<?php echo html_entity_decode($description);?>
    	</div> 
</body>

<!-- <script src="js/mshop_share.js"></script> -->
</html>