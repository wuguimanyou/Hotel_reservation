<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
//require('../back_init.php'); 
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
/* require('../common/jssdk.php'); */
require('../proxy_info.php');
require('../common/utility.php');
/* $jssdk = new JSSDK($customer_id);
$signPackage = $jssdk->GetSignPackage(); */

//头文件----start
require('../common/common_from.php');
require('select_skin.php');
//头文件----end
	
/*清除确认订单页面的session 开始*/
	
	$_SESSION['bug_post_data_'.$user_id] = '';			//清除购物车数据

	$_SESSION['sendtime_id_'.$user_id] = '';			//清除送货时间
	$_SESSION['rtn_sendtime_array_'.$user_id] = '';
	
	$_SESSION['a_type_'.$user_id] = -1;					//清除选择地址的session
	
	$_SESSION['diy_area_id_'.$user_id] = '';			//清除自定义区域	
	$_SESSION['rtn_diy_area_array_'.$user_id] = '';

/*清除确认订单页面的session 结束*/
	
//产品ID
$pid = -1;
if(!empty($_GET["pid"])){
	$pid = $configutil->splash_new($_GET["pid"]);
}


$query="select is_godefault from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$is_godefault= 0;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());   
while ($row = mysql_fetch_object($result)) {
  $is_godefault = $row->is_godefault;
}
$flag_is_godefault=0;
if(!empty($_GET["flag_is_godefault"])){
	$flag_is_godefault=$configutil->splash_new($_GET["flag_is_godefault"]); //单品页传过来的标识
}
if($is_godefault==1&&$flag_is_godefault==0){ //进去单品页
	
	header("Location: ../common_shop/jiushop/detail_default.php?pid=".$pid."&customer_id=".$customer_id_en."&exp_user_id=".$exp_user_id);
	exit();
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
$now_time_str			 = time();
/*属性开始*/
$proLst = new ArrayList();

$propertyarr = explode("_",$propertyids);
$pcount = count($propertyarr);
for($i=0;$i<$pcount;$i++){
   $property_id = $propertyarr[$i];
   $proLst->Add($property_id);   
}
$default_pids = "";
$proHash = new HashTable();
/*属性结束*/

/*慈善公益*/
$is_charitable        = 0;//慈善开关
$charitable_propotion = 0;//慈善公益最低分配率
$query ="select is_charitable,charitable_propotion from charitable_set_t where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$is_charitable        = $row->is_charitable;
	$charitable_propotion = $row->charitable_propotion;
}
$charitable_price = 0;
if( 1 == $is_charitable and $donation_rate < $charitable_propotion ){
	$donation_rate = $charitable_propotion;
}
$charitable_price = $donation_rate * $p_now_price;
$charitable_price = round($charitable_price,2);
/*慈善公益*/


$query="select name,init_reward,issell_model,advisory_telephone,advisory_flag,pro_card_level,shop_card_id,isshowdiscount,is_identity,is_showdiscuss,isOpenSales from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('商品归属Query failed2: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$pro_card_level 	= $row->pro_card_level;//购买产品需要会员卡开关
	$shop_card_id   	= $row->shop_card_id;//分销会员卡
	$is_showdiscuss 	= $row->is_showdiscuss;
	$isOpenSales 		= $row->isOpenSales;
	$isshowdiscount 	= $row->isshowdiscount;
	$is_identity 		= $row->is_identity;
	$advisory_flag		= $row->advisory_flag;
	$brand_supply_name 	= $row->name;
	$brand_tel		 	= $row->advisory_telephone;
	$init_reward		= $row->init_reward;
	$issell_model 		= $row->issell_model;
}
$brand_logo		= "images/dianpu2.png";
$pro_discount = ($p_now_price/$p_orgin_price)*10;	//折扣率
$pro_discount = round($pro_discount ,1);
$total_sales  = $sell_count + $show_sell_count; //虚拟销售量+销售量


/*返现金额开始*/
require('../common/own_data.php');
$info = new my_data();//own_data.php my_data类
$showAndCashback = $info->showCashback($customer_id,$user_id,$cashback,$cashback_r,$p_now_price);
/*返现金额结束*/

/*判断产品是否为品牌供应商产品开始*/
$isbrand_supply 	= 0;//1:是品牌供应商，0不是
$pro_num	 		= 0;//品牌供应商店铺产品总数
$collect_num	 	= 0;//品牌供应商收藏总数
$comment_num	 	= 0;//品牌供应商评论总数
if( $sid > 0 ){
	$sql = "select isbrand_supply,shopName,advisory_telephone,advisory_flag from weixin_commonshop_applysupplys where isvalid=true and user_id=".$sid." limit 0,1";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());  
	while ($row = mysql_fetch_object($result)) {
		$isbrand_supply 	= $row->isbrand_supply;
		$brand_supply_name 	= $row->shopName;
		$brand_tel		 	= $row->advisory_telephone;
		$advisory_flag		= $row->advisory_flag;
	}	
	$brand_logo		= "images/dianpu2.png";
	if( $isbrand_supply ){		
		$sql = "select brand_logo,brand_supply_name,comment_num,collect_num,pro_num,fan_num from weixin_commonshop_brand_supplys where isvalid=true and brand_status=1 and user_id=".$sid." and customer_id=".$customer_id." limit 0,1";
		$result = mysql_query($sql) or die('Query failed: ' . mysql_error());  
		while ($row = mysql_fetch_object($result)) {
			$brand_logo 		= $row->brand_logo;
			$brand_supply_name 	= $row->brand_supply_name;
			$fan_num 			= $row->fan_num;
		//	$pro_num 			= $row->pro_num;
			$collect_num 		= $row->collect_num;
			$comment_num 		= $row->comment_num;
		}

		$all_query="select count(1) as pcount from weixin_commonshop_products where isvalid=true and isout=false and customer_id=".$customer_id." and is_supply_id=".$sid."";
		$all_result=mysql_query($all_query) or die ('all_query' .mysql_error());
		while($row=mysql_fetch_object($all_result)){
			$pro_num=$row->pcount;
		}
		
	}
}
if( $brand_logo == "" ){
	$brand_logo		= "images/dianpu2.png";
}
/*判断产品是否为品牌供应商产品结束*/
$visit = new CommonUtiliy();
$visit->user_visit_pro($pid,$user_id,$customer_id,1);//产品足迹方法

$Plevel 		= -1;
$is_promoters 	= -1;
$is_consume 	= -1;
if( $user_id > 0 ){
	$sql = "select commision_level,is_consume from promoters where isvalid=true and user_id=".$user_id." and status=1";
	$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$Plevel 	= $row->commision_level;
		$is_consume = $row->is_consume;
		$is_promoters = 1; 
	}
}
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
<script>
function Hashtable() {
    this._hash = {};
    this._count = 0;
    this.add = function (key, value) {
        if (this._hash.hasOwnProperty(key)) return false;
        else {
            this._hash[key] = value;
            this._count++;
            return true;
        }
    }
    this.remove = function (key) {
        delete this._hash[key];
        this._count--;
    }
    this.count = function () {
        return this._count;
    }
    this.items = function (key) {
        if (this.contains(key)) return this._hash[key];
    }
    this.contains = function (key) {
        return this._hash.hasOwnProperty(key);
    }
    this.clear = function () {
        this._hash = {};
        this._count = 0;
    }
}
	ppriceHash = new Hashtable();
	var propertyids = "<?php echo $propertyids; ?>";
	<?php
	$proids			= "";
	$orgin_price	= 0;
	$now_price		= 0;
	$storenum		= 0;
	$unit			= "";
	$need_score		= 0;
	$pro_weight		= 0;
	$query = "select proids,orgin_price,now_price,storenum,need_score,weight,unit from weixin_commonshop_product_prices where product_id=" . $pid;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$proids 		= $row->proids;
		$orgin_price 	= $row->orgin_price;
		$now_price 		= $row->now_price;
		$storenum 		= $row->storenum;
		$unit 			= $row->unit;
		$need_score 	= $row->need_score;
		$pro_weight 	= $row->weight;
	?>
	var proids = "<?php echo $proids; ?>";
	var pridsarr = proids.split("_");
	//pridsarr.sort();
	var mlen = pridsarr.length;
	var pidarr2 = new Array();
	for (m = 0; m < mlen; m++) {
		var temp_s = parseInt(pridsarr[m], 10);
		pidarr2[m] = temp_s;
	}
	// pidarr2.sort();
	pidarr2.sort(function (x, y) {
		return parseInt(x) - parseInt(y);
	});
	proids = "";
	for (m = 0; m < mlen; m++) {
		var temp_s = parseInt(pidarr2[m], 10);
		if (proids == "") {
			proids = temp_s;
		} else {
			proids = proids + "_" + temp_s;
		}
	}

	var orgin_price = <?php echo $orgin_price; ?>;
	var now_price = <?php echo $now_price; ?>;
	var storenum = <?php echo $storenum; ?>;
	var unit = "<?php echo $unit; ?>";
	var need_score = '<?php echo $need_score; ?>';

	var pro_weight = '<?php echo $pro_weight; ?>';
	ppriceHash.add(proids,orgin_price+"_"+now_price+"_"+storenum+"_"+need_score+"_"+unit+"_"+pro_weight);
	<?php
	}
	?>
	selproHash = new Hashtable();
	function compare(a, b) {
		return (a - b);
	}
</script> 
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
    	<!--titlebar -->
        <div class = "titlebar-container-div" >
        	<!-- 个人信息, 语言/频视 商品二维码 titlebar-->
            <div class = "titlebar-container-menu-div-top titlebar-container-menu-primary fix" >
            	<div class = "titlebar-container-menu-primary-first-item titlebar-container-menu-item">
					<a href="personal_center.php?customer_id=<?php echo $customer_id_en; ?>">
						<img class="am-header-icon-custom"   src ="./images/goods_image/20160051201.png">
						<span>个人中心</span>
					</a>
            	</div>
            	<div class = "titlebar-container-menu-primary-divider">            	
            		<img class = "title-divider-img"   src="./images/goods_image/line_nobg.png">
            	</div>
            	<div class = "titlebar-container-menu-primary-second-item titlebar-container-menu-item"  >
            		<img class="am-header-icon-custom"   src="./images/goods_image/20160051202.png" >
            		<span>语音/视频</span>
            	</div>
            	<div class = "titlebar-container-menu-primary-divider">            	
            		<img class = "title-divider-img"   src="./images/goods_image/line_nobg.png">
            	</div>
            	<div onclick="showcode();" class = "titlebar-container-menu-primary-third-item  titlebar-container-menu-item" >
            		<img class="am-header-icon-custom"   src="./images/goods_image/20160051203.png">
            		<span>二维码</span>
            	</div>
            </div>

            <!-- 个人信息, 语言/频视 商品二维码 titlebar-->
            
            <!-- 4-tab title --> 
            <div class = "titlebar-container-menu-div titlebar-container-menu-secondary fix" id = "titlebar-container-menu-secondary1">
            	<div id = "item1">
            		<div class = "menu-title"><span>详情</span></div>
            		<div class = "menu-underline"></div>
            	</div>
            	<div id = "item2">
            		<div class = "menu-title"><span style = "color:grey;">规格</span></div>
            		<div class = "menu-underline"></div>
            	</div>
            	<div id = "item3" >
            		<div class = "menu-title"><span style = "color:grey;">售后保障</span></div>
            		<div class = "menu-underline"></div>
            	</div>
            	<div id = "item4">
            		<div class = "menu-title"><span style = "color:grey;">评价</span><span style="color:#f4212b;">&nbsp;<?php echo $total_evaluate;?></span></div>
            		<div class = "menu-underline"></div>
            	</div>
            </div>
            <!-- 4-tab title -->
        </div>
        <!--titlebar -->
    </div>
    <div style="height:34px"></div> <!-- 占据上面框的高度 -->
    <!--content div-->
    <div class = "content">
    	<!-- 商品详情页1  -->
    	<div class = "shangpin-content shangpin-first">
	    	<div id="gallery-tab" class="gallery-tab-sty">
		        <div data-am-widget="slider" class="am-slider am-slider-a1" data-am-slider='{&quot;directionNav&quot;:false}' >
		            <ul id="scroll" class="am-slides">
						<?php
						$query = 'SELECT id,imgurl FROM weixin_commonshop_product_imgs where  isvalid=true and product_id='.$pid;
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());
						while ($row = mysql_fetch_object($result)) {
							$imgurl 	= $row->imgurl;
							$pp_imgurl 	= $new_baseurl.$imgurl;
						?>
		                <li><img src="<?php echo $pp_imgurl;?>"></li>
						<?php
						}
						?>
		            </ul>
		        </div>
		    </div>
			<?php
			if($issnapup){
			?>
	    	<div  class = "shangpin-first-remain">
	    		<span id="time_name">距开始仅剩:</span>
	    		<span class = "information margin-space-span" id = "day" >0</span>
	    		<span class = "unit">天</span>
	    		<span class = "information" id = "hour" >0</span>
	    		<span class = "unit" >时</span>
	    		<span class = "information" id = "minute" >0</span>
	    		<span class = "unit" >分</span>
	    		<span class = "information" id = "second" >0</span>
	    		<span class = "unit">秒</span>
	    	</div>
	    	<?php
			}
			?>
			
	    	<div class = "shangpin-neirong" >
				<?php 
				if( $isbrand_supply ){
				?>
				<span class = "shangpin-neirong-title">品牌</span>
				<?php 
				}
				?>
		    	<span class = "shangpin-neirong-content"><?php echo $p_name; ?></span>
	    	</div>
		
	    	<div class = "shangpin-price">
				<?php if($p_now_price == 0 && $p_orgin_price == 0){ ?>
				<span class = "curprice">积分<?php echo $p_need_score; ?></span>
				<?php }else{ ?>
				<span class = "curprice">￥<?php echo $p_now_price; ?></span>
		    	<span class = "primeprice">￥<?php echo $p_orgin_price; ?></span>
				<?php } ?>
				<div class = "div-float-right">
		    		<?php if( $isshowdiscount == 1 and 10 > $pro_discount and $pro_discount > 0 ){ ?><span class="am-badge am-badge-danger " id = "deprice"><?php echo $pro_discount; ?>折</span><?php } ?>
		    		<?php if( $isvp == 1 and $vp_score > 0 ){ ?><span class="am-badge am-badge-secondary " id = "vp">vp:<?php echo $vp_score; ?></span><?php } ?>
		    	</div>
	    	</div>
	    	<div class = "shangpin-addinfo" >
		    	<?php if($is_baoyou==1){ ?><span id = "baoyou">包邮</span><?php } ?>
		    	<?php if($is_invoice==1){ ?><span id = "fapiao">发票</span><?php } ?>
				<?php 
				if( $isOpenSales > 0 ){
				?>
		    	<div id = "yishou">
		    		<span>已售<?php echo $total_sales; ?></span>
		    	</div>
				<?php 
				}
				?>
	    	</div>
	    	<div class = "shangpin-line"></div>
 	    	 
	    	<?php
			if( $showAndCashback['display'] == 1 ){
			?>
		    <div class = "shangpin-addprice">
				<?php
				if( $is_promoters > 0 ){
				?>
				<img onclick="reckon_commision()" class="count" src="images/goods_image/count.png">
				<?php
				}
				?>
		    	<div class="span">
					<?php
					if( $showAndCashback['cashback_m'] > 0 ){
					?>
					<span class="fan" >返</span>
					<font id = "price1" ><?php echo $showAndCashback['cashback_m'];?></font>
					<?php 
					}
					if( $showAndCashback['cashback_m'] > 0 and $back_currency > 0 ){
					?>
					<font class  = "shangpin-addprice-font-size12">+</font>
					<?php
					}
					if( $back_currency > 0 ){
					?>
					<!-- <font id = "price2"><?php echo $back_currency;?></font><font id = "unit2">(<?php echo $custom;?>)</font> -->
					<span class="fan bi" >币</span><font id = "price2"><?php echo $back_currency;?></font>
					<?php 
					}
					?>
				</div>
		    </div>
		    <div class = "shangpin-line"></div>
 			<?php
			}
			?>
			<?php if( ( 1 == $is_charitable) and ( 0 < $charitable_price ) ){
			?> 
	    	
		    <div class = "shangpin-addprice">
		    	<span class="juan">捐</span>
		    	<span class="span"><font id = "charitable_price" style="color:#f01529">￥<?php echo $charitable_price;?></font>
		    	</span>
		    </div>
		    <div class = "shangpin-line"></div>
 			<?php
			}
			?> 
		    
		    <div class = "shangpin-panel3" >
		    	<div  class = "shangpin-panel3-item1">
					<img id = "url2" src="<?php echo $brand_logo; ?>" >
					<span id = "name"><?php echo $brand_supply_name; ?></span>
					<?php
					if( $advisory_flag ){
					?>
					<div class = "shangpin-panel3-item1-content">
						<a href="tel:<?php echo $brand_tel?>" ><img id = "phone" src="./images/goods_image/phone_1.png" width="25" height="25" ></a>
						<span></span>
					</div>
					<?php
					}
					?>
		    	</div>
			<?php 
			if( $sid > 0 ){
				if( $isbrand_supply ){
			?>
				<div class="s-line"></div>
		    	<div class = "shangpin-panel3-item2">
		    		<div class = "shangpin-panel3-item2-left1">
		    			<div class = "shangpin-panel3-item2-left1-item1" ><span id="collect_num"><?php echo $collect_num?$collect_num:0; ?></span></div>
		    			<div class = "shangpin-panel3-item2-left1-item1" ><span style = "color:#5f666b;" >收藏</span></div>
		    		</div>
		    		<div class = "shangpin-panel3-item2-left1">
		    			<div class = "shangpin-panel3-item2-left1-item1"><span><?php echo $pro_num?$pro_num:0; ?></span></div>
		    			<div class = "shangpin-panel3-item2-left1-item1"><span style = "color:#5f666b;">全部商品</span></div>
		    		</div>
		    		<div class = "shangpin-panel3-item2-left1">
		    			<div class = "shangpin-panel3-item2-left1-item1"><span><?php echo $comment_num?$comment_num:0; ?></span></div>
		    			<div class = "shangpin-panel3-item2-left1-item1"><span style = "color:#5f666b;">评价</span></div>
		    		</div>
		    	</div>
		    	<div class = "shangpin-panel3-item3">
		    		<div class = "shangpin-panel3-wrapper">
					<?php
						$scount = 0;	//是否已收藏：0否，1是
						$query_s = 'select count(1) as scount from weixin_user_collect where isvalid=true and collect_id='.$sid.' and customer_id='.$customer_id.' and collect_type=2 and user_id='.$user_id;
						$result_s = mysql_query($query_s) or die('query_s failed'.mysql_error());
						while($row_s = mysql_fetch_object($result_s)){
							$scount = $row_s->scount;
						}
					?>
		    			<button id = "bookmark" type="button" class="am-btn store-button  collect_shop_1" onclick="collect(<?php echo $sid;?>,2,'add')"    style="<?php if($scount>0){echo 'display:none;';}?>">收藏店铺</button>
		    			<button id = "bookmark_collected" class="am-btn store-button collect_shop_2" onclick="collect(<?php echo $sid;?>,2,'del')" style="<?php if(0 == $scount){echo 'display:none;';}?>">已收藏</button>
		    		</div>
		    		<div class = "shangpin-panel3-wrapper">
						<a href="my_store/my_store.php?supplier_id=<?php echo $sid?>&customer_id=<?php echo $customer_id_en; ?>">
							<button id = "entershop" type="button" class="am-btn store-button  " >进入店铺</button>
						</a>
		    		</div>
		    	</div>
			<?php 
				}
			}
			?>
		    </div>
		    <div class = "shangpin-line"></div>
			
			<?php 
			if( $is_guess_you_like ){
			?>
		    
		    <div class = "shangpin-panel4">
		    	<div id = "shangpin-panel4-item1"> 猜你喜欢</div>
		    	<div id = "shangpin-panel4-item2">
				<?php?>
			    </div>
			    <div id = "shangpin-panel4-item3">
		    		<div class = "shangpin-panel4-item3-wrapper">
		    			<button id = "changeset" type="button" class="am-btn ">换一组</button>
		    		</div>
		    		<div class = "shangpin-panel4-item3-wrapper">
						<a href="list.php?customer_id=<?php echo $customer_id_en; ?>&pid=<?php echo $pid;?>&op=morelike">
							<button id = "seemore" type="button" class="am-btn " >查看更多</button>
						</a>
		    		</div>
		    	</div>
		    </div> 
			<?php
			}
			?>
		    <div class = "shangpin-line shangpin-panel4_xian"></div>
    	</div>
    	<!-- 商品详情页1 -->
    	
    	<!-- 商品详情页2  -->
    	<!--  详情 -->
    	<div class = "titlebar-container-menu-div titlebar-container-menu-secondary" id = "secondmenu2">
        	<div id = "item1">
        		<div class = "menu-title"><span>详情</span></div>
        	</div>
        	<div id = "item2">
        		<div class = "menu-title"><span >规格</span></div>
        	</div>
        	<div id = "item3" >
        		<div class = "menu-title"><span >售后保障</span></div>
        	</div>
			<?php
			if( $is_showdiscuss > 0 ){
			?>
        	<div id = "item4">
        		<div class = "menu-title"><span >评价</span><span style="color:#f4212b;">&nbsp;<?php echo $good_level+$meu_level+$bad_level;?></span></div>
        	</div>
			<?php
			}
			?>
        </div>
    	<div class = "shangpin-content shangpin-second1 shangpin-content-second">
			<?php echo html_entity_decode($description);?>
    	</div>
    	<!--  详情-->
    	
    	<!-- 规格 -->
    	<div class = "shangpin-content shangpin-second2 shangpin-content-second">
	    	<?php echo $specifications;?>
    	</div>
    	<!-- 规格 -->
    	<div class = "shangpin-content shangpin-second3 shangpin-content-second">
	    	<?php echo $customer_service;?>
    	</div>
    	<!--评价-->
		<?php
		if( $is_showdiscuss > 0 ){
		?>
    	<div class = "shangpin-content shangpin-second4 shangpin-content-second">
			<div class = "item-top" >
				<span >商品好评率:</span>
				<span class = "shangpin-second4-item1-span"><?php if($total_evaluate!=0){echo round(100*($good_level/$total_evaluate),2)."%";}else{echo '0';}?></span>	
			</div> 
			<div class = "item" class = "div-float-left">
				<div class = "item2-cell1 colorchange" PL="0">
				  <div class = "shangpin-second4-item2-cell">全部</div>
				  <div class = "shangpin-second4-item2-cell">(<?php echo $total_evaluate;?>)</div>
				</div>
				<div class = "item2-cell2 colorchange" PL="1">
				  <div class = "shangpin-second4-item2-cell2">好评</div>
				  <div class = "shangpin-second4-item2-cell2">(<?php echo $good_level?>)</div>
				</div>
				<div class = "item2-cell3 colorchange" PL="2">
				  <div class = "shangpin-second4-item2-cell2">中评</div>
				  <div class = "shangpin-second4-item2-cell2">(<?php echo $meu_level?>)</div>
				</div>
				<div class = "item2-cell4 colorchange" PL="3">
				  <div class = "shangpin-second4-item2-cell2">差评</div>
				  <div class = "shangpin-second4-item2-cell2">(<?php echo $bad_level?>)</div>
				</div>
			</div>
			<div style="clear:both;"></div>		
			<div style="position: relative;">
				<div id="wrapper">
					<div id="scroller">
						<ul id="thelist">
						</ul>
				<p id="pinterestMore" style="display: none;">----- 正在加载 -----</p>
				<p id="pinterestDone" style="display:none;">----- 评论已加载完毕 -----</p>
					</div>
				</div>
			</div>
			
    		<!-- <div id = "pingjia-content">
				
			</div> -->
    	<!--评价-->
    	<!-- 商品详情页2  -->
		</div>
		<?php
		}
		?>
    <!--content div-->
	
    <!--悬浮按钮-->
	<?php  include_once('float.php');?>
	<!--悬浮按钮-->
    
    <!-- footer content -->
    <div class = "shangpin-footer">
	<?php
		//是否已收藏产品
		$rcount = 0;
		$query_c = "select count(1) as rcount from weixin_user_collect where isvalid=true and customer_id=".$customer_id." and user_id=".$user_id." and collect_id=".$pid." and collect_type=1";
		$result_c = mysql_query($query_c) or die('query_c failed'.mysql_error());
		while($row_c = mysql_fetch_object($result_c)){
			$rcount = $row_c->rcount;
		}
	?>
    	<div class = "item1 collect_1" id = "item1-cell1 collect_1" onclick="collect(<?php echo $pid.",1,'add'";?>)" style="<?php if($rcount>0){echo 'display:none;';}?>">
    		<div class = "shangpin-footer-item1-row1">
    			<img src = "./images/goods_image/2016042701.png" >
    		</div>
    		<div class = "shangpin-footer-item1-row2">
    			<span>收藏</span>
    		</div>
    	</div>
		<div class = "item1 collect_2" id = "item1-cell1 collect_2" onclick="collect(<?php echo $pid.",1,'del'";?>)" style="<?php if($rcount<=0){echo 'display:none;';}?>">
    		<div class = "shangpin-footer-item1-row1">
    			<img src = "./images/goods_image/collect.png">
    		</div>
    		<div class = "shangpin-footer-item1-row2">
    			<span style="color:#ff8430">已收藏</span>
    		</div>
    	</div>
    	<div class = "item1 button buttonclick">
			<a href="../common_shop/jiushop/index.php?customer_id=<?php echo $customer_id_en;?>">
				<div class = "shangpin-footer-item1-row1">
					<img src = "./images/goods_image/2016042702.png"  >
				</div>
				<div class = "shangpin-footer-item1-row2 pt1">
					<span>进店</span>
				</div>
			<a>
    	</div>
    	<div class = "item1  button buttonclick">
			<a href="order_cart.php?customer_id=<?php echo $customer_id_en;?>">
				<div class = "shangpin-footer-item1-row3">
					<img src = "./images/goods_image/2016042703.png" >
					 <span id = "badge-span" style =""></span> 
				</div>
				<div class = "shangpin-footer-item1-row2 pt1">
					<span>购物车</span>
				</div>
    		</a>
    	</div>
    	<div class = "item2">
    		<div class = "item2-panel">
    			<button id = "joinCar" onclick="showBuyDiv(1);" type="button" class="am-btn am-btn-warning item2-panel-button">加入购物车</button>
    		</div>
    		<div class = "item2-panel">
    			<button id = "buyDiv" onclick="showBuyDiv(2);" type="button" class="am-btn  am-btn-danger item2-panel-button">立即购买</button>
    		</div>
    	</div>
    </div>
    <!-- footer content -->
    
    
    <!-- dialog -->
    <div class="am-share shangpin-dialog" >
    <div class="content-base  row1"><div class="dlg-row1-cell0"><img class="am-img-thumbnail am-circle" onclick="closeDialog();" src="./images/goods_image/2016042704.png" ></div></div>
        <!-- 加入购买 -->
	  	<div class = "content-base   dialog-content">
	  	<!-- <img  class="am-img-thumbnail am-circle close-img" src = "./images/goods_image/2016042704.png" > -->
	  		<div class = "content-base content-row1">
	  			<div class = "dlg-content-row1-left">
	  				<img src = "<?php echo $default_imgurl; ?>">
	  			</div>
	  			<div class = "dlg-content-row1-right">
	  				<div class = "dlg-content-row1-right-top1"> 
	  					<span><?php echo $p_name; ?></span>
	  				</div>
	  				<div class = "dlg-content-row1-right-top2">
	  					<span>
							￥
							<span id="now_price">
								<?php echo number_format($p_now_price,2); ?> 
							</span>
						</span> 
						<span class="sign_add" style="color:#000;">+</span>
						<span class = "need_score_span dlg-content-row1-right-top2-span">
							<span id="need_score">
							<?php echo $p_need_score; ?>
							</span>
							积分
						</span>
	  				</div>
	  			</div>
	  		</div>
	  		<div class="ov-class">
			<?php
			 $query="select id ,name from weixin_commonshop_pros where parent_id=-1 and isvalid=true and customer_id=".$customer_id;
			 $result = mysql_query($query) or die('Query failed11: ' . mysql_error());
			 while ($row = mysql_fetch_object($result)) {
				$prname = $row->name;
				$prid = $row->id;
				$ishasSet_t =false;
			?>
	  		<div id="pro_<?php echo $prid;?>" class = "pro_div">
	  			<div class = "big_pro_name">
					<span><?php echo $prname;?>:&nbsp;&nbsp;</span>
				</div>
				<script>var subids = "";</script>
	  			<div pos_name="<?php echo $prname;?>" class = "small_pro_div">
					<?php
									 
					$query2="select id,name from weixin_commonshop_pros where isvalid=true and parent_id=".$prid;
					$result2 = mysql_query($query2) or die('Query failed12: ' . mysql_error());
					$i					= 1;
					$fir_subid			= -1;
					$pro_shownameLst 	= "";
					while ($row2 = mysql_fetch_object($result2)) {
						$subname	= $row2->name;
						$subid		= $row2->id;
						if( $i == 1 ){
							$fir_subid=$subid;
						}
						if($proLst->Contains($subid) and !empty($subname)){
							$ishasSet	= true;
							$ishasSet_t = true;
							if(empty($pro_shownameLst)){
								$pro_shownameLst=$subid;
							}else{
								$pro_shownameLst=$pro_shownameLst."_".$subid;
							}
					?>
		  			<div pos_id="<?php echo $subid;?>" class="pos_<?php echo $prid; ?> pos_div" ontouchstart="chooseDiv(<?php echo $prid; ?>,<?php echo $subid; ?>);" id = "pro_div_<?php echo $prid; ?>_<?php echo $subid; ?>" >
						<span class="span_pos_<?php echo $prid; ?>" ><?php echo $subname; ?></span>
					</div>
					<script>subids = subids+<?php echo $subid; ?>+
                                    "_";</script>
					<?php
						}
					}
					if(!$ishasSet_t){
						echo "<script>document.getElementById('pro_".$prid."').style.display='none';</script>";
					}else{

					?>
						<input type=hidden name="prvalues" id="invalue_<?php echo $prid; ?>" value="" />
					<?php } ?>
					<script>
                        if (subids != "") {
                            subids = subids.substring(0, subids.length - 1);
                        }
                        selproHash.add(<?php echo $prid; ?>, subids);
                    </script>
	  			</div>
	  		</div>
			<?php
				if ($ishasSet_t) {
					if (empty($showpname)) {
						$showpname = $showpname . $prname;
					} else {
						$showpname = $showpname . "," . $prname;
					}
					$proHash->insert($prname, $pro_shownameLst);
					//echo "snamelst=======".$prname."========".$pro_shownameLst;
				}
			}
			if ($default_pids != "") {
				$default_pids = rtrim($default_pids, "_");
			}
			?>
			</div>
	  		<div id="numDiv" class = "content-base content-row4">
	  			<span class = "dlg-content-row4-span">数量:&nbsp;&nbsp;</span>				
	  			<div class = "num_div">
	  				<div class = "minus button buttonclick" onclick="minusNum();" ><span>-</span></div>
	  				<div class = "count_div">
						<!-- <span id = "mount_count">3</span> -->
						<input onblur="modify();" type="text" value="1" id="mount_count" autocomplete="off" onkeyup="clearNoNum(this)" onafterpaste="clearNoNum(this)">
					</div>
	  				<div class = "add button buttonclick" onclick="addNum();"><span>+</span></div>
	  			</div>
				<div id="stock_div">
					库存:
					<span id="stock">
					<?php echo $p_storenum; ?>
					</span>
				</div>
	  		</div>
	  		<!-- 加入购买 -->
	  		
  			<div class = "div-clear"></div>
	  		<div class = "content-button" id = "div_buyNow">
	  			<button type="button" onclick="buyGood();" class="am-btn am-btn-danger">立即购买</button>
	  		</div>
	  		
	  		<div class = "content-button" id= "div_joinCar">
	  			<button type="button" onclick="addToCart();" class="am-btn am-btn-warning">加入购物车</button>
	  		</div>
	  	</div>
	  	<!-- dialog -->
	</div>
	<div id="qrcode_div">
		<div id="close_qr" onclick="close_code();">
			<img src="images/info_image/btn_close.png" width="30">
		</div>
		<div id="code_img">	
			<i class="wx_loading_icon"></i>
		</div>
		<div id="refresh_qr">
			<div class="refresh">
				<span>长按图片保存二维码</span>
				<img onclick="get_qrcode();" src="images/info_image/refresh.png" >
			</div>
		</div>
	</div>
	<div id="screen"></div>
	<input type="hidden" id="supply_id" value="<?php echo $sid; ?>"/>
	<input type="hidden" id="pid" value="<?php echo $pid; ?>"/>
</body>		
<!-- 页联系js -->
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
<script type="text/javascript">
var from_type			= "<?php echo $from_type; ?>";
var pid					= "<?php echo $pid; ?>";
var customer_id			= '<?php echo $customer_id_en; ?>';
var customer_id2		= '<?php echo $customer_id; ?>';
var user_id				= '<?php echo $user_id; ?>';
var owner_id			= '<?php echo $owner_id; ?>';
var supply_id			= '<?php echo $sid; ?>';

var sid			        = '<?php echo $sid; ?>';
var buystart_time		= '<?php echo strtotime($buystart_time);?>';
var buyend_time			= '<?php echo strtotime($countdown_time);?>';
var now_time			= '<?php echo time();?>';
var runtimes 			= 0;

var product_vedio		= '<?php echo $product_vedio;?>';
var product_voice		= '<?php echo $product_voice;?>';
var w_heigjt			= $(window).height();//屏幕高度
var w_width				= $(window).width();//屏幕宽度度
var default_pids 		= "<?php echo $default_pids; ?>";
var p_now_price 		= "<?php echo $p_now_price; ?>";//现价
var p_need_score 		= "<?php echo $p_need_score; ?>";//需要的积分
var p_storenum 			= "<?php echo $p_storenum; ?>";//库存
var isout	 			= "<?php echo $isout; ?>";//是否下架
var issnapup 			= "<?php echo $issnapup; ?>";//抢购产品
var is_stockOut 		= <?php echo $showAndCashback['display']; ?>;//库存不足下架开关
var pro_card_level_id  	= <?php echo $pro_card_level_id; ?>;	//购买产品需要会员卡的等级
var pro_card_level  	= <?php echo $pro_card_level; ?>;	//购买产品需要会员卡等级开关
var shop_card_id  		= <?php echo $shop_card_id; ?>;	//分销会员卡id
var pis_identity  		= <?php echo $pis_identity; ?>;	//产品是否需要身份证购买
var is_identity  		= <?php echo $is_identity; ?>;	//是否开启身份证验证
var is_QR  				= <?php echo $is_QR; ?>;	//是否 二维码产品 0:否 1:是
var is_virtual			= '<?php echo $is_virtual; ?>';	//是否为虚拟产品 0:非虚拟产品,1:虚拟产品
var is_showdiscuss		= '<?php echo $is_showdiscuss; ?>';	
var isvalid				= '<?php echo $isvalid; ?>';
var is_promoters		= '<?php echo $is_promoters; ?>';	//是否是推广员
var Plevel				= '<?php echo $Plevel; ?>';	//推广员等级
var issell_model		= '<?php echo $issell_model; ?>';	//复购开关
var init_reward			= '<?php echo $init_reward; ?>';	//商城分佣比例
var pro_reward			= '<?php echo $pro_reward; ?>';	//产品分佣比例
var is_consume			= '<?php echo $is_consume; ?>';	//是否满足消费无限级奖励/股东分红 0:普通推广员 1:代理 2:渠道 3:总代理 4:股东


debug					= false;
/* appId					= '<?php echo $signPackage["appId"];?>';
timestamp				= <?php echo $signPackage["timestamp"];?>;
nonceStr				= '<?php echo $signPackage["nonceStr"];?>';
signature				= '<?php echo $signPackage["signature"];?>'; */
share_url				= "<?php echo $linkurl; ?>"; //分享链接
title					= "<?php echo $p_name; ?>"; //标题
desc					= "<?php echo $introduce; ?>"; //分享内容
<?php
if(!empty($define_share_image)){
?>
imgUrl					= '<?php echo $new_baseurl.$define_share_image; ?>'; // 分享图标
<?php
}else{
?>
imgUrl					= '<?php echo $pp_imgurl; ?>'; // 分享图标
<?php
}
?>
share_type				= 1;//自定义类型
var eva_done=0; //评论是否加载完成
var dis_pagenum = 1;
var eva_lock=0;//评论是否上锁
var PL = 0;//评论类型
$(function(){
    $(".colorchange").eq(0).css("color","#F4212B");
    $(".colorchange").click(function(){//点击切换评论，重新加载。
		PL = $(this).attr("PL");
        $(this).css("color","#F4212B").siblings().css("color","#333333");
		$("#thelist").html("");
		dis_pagenum=1;
		eva_done=0;
		eva_lock = false;
		pullUpAction (PL,dis_pagenum);
    })
})

</script>
<?php require('../common/share.php'); ?>
<script src="js/goods/global.js?ver=<?php echo time(); ?>"></script>
<script src="js/goods/product_detail.js"></script>
</body>

<!-- <script src="js/mshop_share.js"></script> -->
</html>