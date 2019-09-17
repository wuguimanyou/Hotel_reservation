<?php

header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
/* require('../common/jssdk.php'); */
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
mysql_query("SET NAMES UTF8");
require('select_skin.php');
/* $jssdk = new JSSDK($customer_id);
$signPackage = $jssdk->GetSignPackage(); */
$new_baseurl = "http://".$http_host; //新商城图片显示

 
$user_id 		= -1;
$parent_id 		= -1;
require('../common/common_from.php'); 

$package_id  = $configutil->splash_new($_GET["package_id"]);//礼包id

if(!empty($_GET["package_parent_id"])){
	$package_parent_id	= $configutil->splash_new($_GET["package_parent_id"]); //推荐人id
	$parent_id			= $package_parent_id;
}
/*查找上级id开始*/
if( $parent_id < 0 ){
	$query="select parent_id from weixin_users where isvalid=true and id=".$user_id;
	$result = mysql_query($query) or die('Query failed5: ' . mysql_error());   
	while ($row = mysql_fetch_object($result)) {
		$parent_id = $row->parent_id;
	}
}
/*查找上级id结束*/

/*查找自己是否为推广员身份开始*/
$user_status = 0;
$query = "select status from promoters where isvalid=true and user_id=".$user_id;
$result = mysql_query($query) or die('Query failed5: ' . mysql_error());   
while ($row = mysql_fetch_object($result)) {
	$user_status = $row->status;
}
/*查找自己是否为推广员身份结束*/

/*查找上级名字开始*/
$exp_user_name = "无";
if($parent_id>0){
   
   $query="select name,weixin_name,weixin_headimgurl from weixin_users where isvalid=true and id=".$parent_id;
   $result = mysql_query($query) or die('Query failed5: ' . mysql_error());   
   while ($row = mysql_fetch_object($result)) {
        $wname         = $row->name;
		$wweixin_name  = $row->weixin_name;
		$exp_user_name = $wname."(".$wweixin_name.")";
   }
}
/*查找上级名字结束*/

/*查找默认收货地址开始*/
$name       = "";		//收货人名字
$phone      = "";	//收货人电话
$add_id     = -1;	//id
$address    = "";	//收货人地址
$location_p = "";	//省
$location_c = "";	//市	
$location_a = "";	//区

$query_other="select id,name,phone,address,location_p,location_c,location_a from weixin_commonshop_addresses where isvalid=true and is_default=1 and user_id=".$user_id;
$result_other = mysql_query($query_other) or die('W103 Query failed: ' . mysql_error());
while ($row_o = mysql_fetch_object($result_other)) {
	$name       = $row_o -> name;
	$phone      = $row_o -> phone;
	$add_id     = $row_o -> id;
	$address    = $row_o -> address;
	$is_default = $row_o -> is_default;
	$location_p = $row_o -> location_p;
	$location_c = $row_o -> location_c;
	$location_a = $row_o -> location_a;
}
/*查找默认收货地址结束*/

$pid               		= -1;	//名称
$price             		= 0;	//价钱
$stock             		= 0;	//库存
$nowtime				= "";	//当前时间
$introduce        		= "";	//简短介绍
$isPanicBuy        		= 0;	//1:开启抢购，0关闭
$cost_price        		= 0;	//市场价
$description       		= "";	//介绍
$package_type       	= 1;	//礼包类型
$package_name      		= "";	//名称
$default_imgurl    		= "";	//图片
$shareholder_level 		= 0;	//股东等级
$default_head_imgurl	= "";	//分享图片


$query = "SELECT 
		id,
		price,
		stock,
		endtime,
		begintime,
		introduce,
		cost_price,
		isPanicBuy,
		description,
		package_name,
		default_imgurl,
		shareholder_level,
		default_head_imgurl
		from package_list_t where isvalid=TRUE and id=".$package_id." and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed1: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$pid              		= $row -> id;
	$price             		= $row -> price;
	$stock             		= $row -> stock;
	$endtime           		= $row -> endtime;
	$begintime         		= $row -> begintime;
	$introduce         		= $row -> introduce;
	$cost_price        		= $row -> cost_price;
	$isPanicBuy				= $row -> isPanicBuy;
	$description			= $row -> description;
	$package_name     		= $row -> package_name;
	$package_type     		= $row -> package_type;
	$default_imgurl    		= $row -> default_imgurl;
	$shareholder_level	 	= $row -> shareholder_level;
	$default_head_imgurl	= $row -> default_head_imgurl;
	
	
	$endtime1     			= $endtime;
	$begintime    			= strtotime ($begintime); 
	$endtime      			= strtotime ($endtime); 
	$nowtime      			= time();
}

$exp_id	= -1; //快递模板id
$query = 'select exp_id from weixin_commonshop_pay_pack where isvalid=true and customer_id='.$customer_id;
$result = mysql_query($query) or die('W21 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$exp_id	= $row -> exp_id;	
}
$is_stock = 1;//是否开启库存
$sql="SELECT is_stock from weixin_commonshop_pay_pack where isvalid=TRUE and customer_id=".$customer_id; 
$res = mysql_query($sql);
while ($row1 = mysql_fetch_object($res)) {
	$is_stock = $row1->is_stock;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>礼品详情</title>
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
    <link type="text/css" rel="stylesheet" href="css/package/package_detail.css" />
    <link type="text/css" rel="stylesheet" href="css/order_css/global.css?ver=<?php echo time(); ?>" />
    <!-- global css-->
    <link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />  
    <script type="text/javascript" src="assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="assets/js/amazeui.js"></script>
	<script type="text/javascript" src="js/global.js"></script>
	<script type="text/javascript" src="../common/utility.js" charset="utf-8"></script>

</head>
<body data-ctrl=true style="background:#f3f3f3;">
<!--悬浮按钮-->
<?php  include_once('float.php');?>
<!--悬浮按钮-->
	<!-- Loading Screen -->
	<!-- <div id='loading' class='loadingPop'style="display: none; ">
		<div class = "h20-alt"></div>
		<img src='images/loading.gif'/>
	<p>数据加载中</p>
	</div> -->
	<!-- Loading Screen -->
	<!-- header部门-->
	<!-- <header data-am-widget="header" class="am-header am-header-default header-wrapper">
		<div class="am-header-left am-header-nav header-btn">
			<img class="am-header-icon-custom"  src="images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">礼品详情</h1>
	    <div class="am-header-right am-header-nav">
		</div>
	</header> --><!-- 暂时隐藏头部导航栏 -->
	<!-- header部门-->
	<!-- <div class = "header-alt"></div> --><!-- 占位div -->
    <div class = "lipinxiangqing">
    	<img src = "<?php echo $default_head_imgurl?>" style = "width:100%;">
    	<div class = "h1-alt"></div>
		<?php
		if( $isPanicBuy ){
		?>
    	<div  class = "time_main">
			<p class="num-3 surplus_time">
				<span id="RemainNmae">距结束仅剩:</span>
				<span id="RemainD" class="Remain">0</span>天
				<span id="RemainH" class="Remain">0</span>时
				<span id="RemainM" class="Remain">0</span>分
				<span id="RemainS" class="Remain">0</span>秒
			</p>
	    </div>
		<?php
			}
		?>
	    <div class = "lp_list">
	    	<ul class = "lp_list_wrappper">
	    		<li>
	    			<div class = "lp_item_main">
	    				<img class = "lp_img" src = "<?php echo $default_imgurl;?>"/>
	    				<div class="lp_item_center">
	    					<div class = "h5-alt"></div>		
							<div class="lp_shop_name g-text-hidden">	
								<?php echo $package_name; ?>
							</div>
							<div class = "h10-alt"></div>
							<div class = "lp_price">
								￥<?php echo $price; ?>
								<del class = "lp_old_price">
									￥ <?php echo $cost_price; ?>
								</del>
							</div>
							<?php if($is_stock==1) {?><div class="stock">剩余：<?php echo $stock;?></div> <?php } ?>
						</div>
						<div class = "lp_item_right">
							<img class = "lp_select select-on" src = "<?php echo $images_skin?>/extends_image/checkbox_on-orange.png"/>
						</div>	
						
	    			</div>
	    		</li>
	    	</ul>
	    </div>
    	<div class = "h20-alt"></div>
    	<div class = "lp_content_main">
    		<div class = "lp_content_main_title">
    			活动说明
    		</div>
    		<div class = "lp_content_main_nerong">
    			<?php echo $introduce; ?>
    		</div>
			<div id="detail_div" onclick="showDetail();">
			查看详情
			</div>		
    	</div>
		
		<div id="close_detail" onclick="close_detail();">
			<!--<img src="images/info_image/btn_close.png" width="30">-->
		</div>
		
		<div id="detail">
			<?php echo $description?>
		</div>
    	<div class = "info_title" style="font-size:17px;">
    		请填写店主资料
    	</div>
    	
    	<div class = "info_main">
    		<div class = "info_main_left">
    			推荐人
    		</div>
    		<div class = "info_main_right">
    			<!-- <input type = "text" class = "info_main_text" name="exp_user_name" value="" /> -->
				<p>
					<?php echo $exp_user_name; ?>
				</p>
    		</div>
    	</div>
		<div class = "info_main">
    		<div class = "info_main_left">
    			姓名
    		</div>
    		<div class = "info_main_right">
    			<input type = "text" class = "info_main_text" name="user_name" id="user_name" value="<?php echo $name; ?>"/>
    		</div>
    	</div>
    	<div class = "info_main">
    		<div class = "info_main_left">
    			手机号
    		</div>
    		<div class = "info_main_right">
    			<input type = "text" class = "info_main_text" name="user_phone" id="user_phone" value="<?php echo $phone; ?>"/>
    		</div>
    	</div>	
    	<div class = "g_clear_div"></div>
    	<div class = "info_title" style="font-size:17px;">
    		请填写礼包收货地址
    	</div>
    	<div class = "info_main">
    		<div class = "info_main_left">
    			所在省
    		</div>
    		<div class = "info_main_right1">
    			 <select name="location_p" id="location_p"></select>
    		</div>
    	</div>
		<div class = "info_main">
    		<div class = "info_main_left">
    			所在市
    		</div>
    		<div class = "info_main_right1">
    			 <select name="location_c" id="location_c"></select>
    		</div>
    	</div>
	    <div class = "info_main">
    		<div class = "info_main_left">
    			所在县/区
    		</div>
    		<div class = "info_main_right1">
    			<select name="location_a" id="location_a"></select>
    		</div>
    	</div>	
		<script type="text/javascript" src="../common/region_select.js"></script>
		<script type="text/javascript">
		$(function(){
			 new PCAS('location_p', 'location_c', 'location_a', '<?php echo $location_p?>', '<?php echo $location_c?>', '<?php echo $location_a?>',1);
			 // $(".frame_image").on("change",":file",function(){
	   //          fileSelect_banner(this);
	   //      });
		});
		//new PCAS('location_p', 'location_c', 'location_a', '<?php echo $location_p; ?>', '<?php echo $location_c; ?>', '<?php echo $location_a; ?>',1);
		</script>
    	<div class = "info_main">
    		<div class = "info_main_left">
    			详细地址
    		</div>
    		<div class = "info_main_right">
    			<input type = "text" class = "info_main_text" name="address" id="address" value="<?php echo $address?>" placeholder="请填写详细地址"/>
    		</div>
    	</div>
    	<div class = "g_clear_div"></div>
    	<div class = "h20-alt"></div>
    	<div class = "info_main1">
    		
			<?php
			$express_id	= 0;
			$express_ids = '';
			$express_id_arr = '';
			$e_id	= -1;
			$e_name	= '';
			$e_price	= "没有匹配到合适快递。";
			if( $exp_id > 0){
				//if( $location_p != "" ){
					$query = "select express_id from express_relation_t where isvalid=true and customer_id=".$customer_id." and tem_id=".$exp_id."";
					//echo $query;
					$result=mysql_query($query)or die('L445 Query failed'.mysql_error());
					while($row=mysql_fetch_object($result)){
						$express_id = $row->express_id;
						$express_ids .= $express_id.',';
					}
					$express_id_arr = substr($express_ids,0,-1);
					
					$query = "select id,name,price from weixin_expresses where isvalid=true and customer_id=".$customer_id." and ((is_include=0 and region like '%".$location_p."%' ) or (is_include=1 and region not like '%".$location_p."%') or region='') and id in(".$express_id_arr.") group by price asc limit 1";			
					$result = mysql_query($query) or die('快递  Query failed: ' . mysql_error());
					$rcount_kd = mysql_num_rows($result);
					if( $rcount_kd < 1 ){
						$e_price	= "没有匹配到合适快递";
					}
					while ($row = mysql_fetch_object($result)) {	
						$e_id		= $row->id;
						$e_name		= $row->name;
						$e_price	= $row->price;	
						if( $e_price == 0 ){
						$e_price	= "免邮";
					}
					}
					
				//}
			}
						
			?>
			<div class = "info_main_left" style="width:auto;">
    			快递费：<span id="exp_money"><?php echo $e_price; ?></span>
    		</div>
    	</div>
    	<div class = "g_clear_div"></div>
		
    	<!-- <div  class = "btn_wrapper" >
    		<div  class = "btn" onclick="submitV();" >
    			提交支付
    		</div>
    	</div> -->
		<div  class = "btn_wrapper" >
    		<button id="submit_id" class="r-button-3" onclick="commitbtn();">提交支付</button>
    	</div>
		<div class="am-dimmer am-active" data-am-dimmer="" style="display: none;" onclick="close_detail()"></div>
		<div id = "pay_div">
			<div class="list-one popup-menu-title">
				<span class="sub">选择支付方式</span>
			</div>
			<?php  
			$query = "select is_alipay,is_weipay from customers where isvalid=true and id=".$customer_id;
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			$is_alipay   =1; //支付宝开关
			$is_weipay   =1; //微信支付开关
			while ($row = mysql_fetch_object($result)) {
				$is_alipay   = $row->is_alipay;
				$is_weipay   = $row->is_weipay;
			}
			if($from_type == 1 and $is_weipay == 1 ){			
			//目前支持微信端的微信支付，app微信支付暂不支持
			?>	
			<div class="line"></div>
			<div class = "popup-menu-row" onclick="submitV('微信支付')" >
				<img src="images/np-1.png">
				<span class="font">微信支付</span>
			</div>
			<?php } 
			if( $is_alipay == 1 and $from_type != 1){
			?>
			<div class="line"></div>
			<div class = "popup-menu-row" onclick="submitV('支付宝支付')">
				<img src="images/np-4.png">
				<span class="font">支付宝支付</span>
			</div>
			<?php
			}
			?>
		</div>
	</div>
	<input type="hidden" name="e_id" id="e_id" value="<?php echo passport_encrypt($e_id);?>">
	<input type="hidden" name="e_name" id="e_name" value="<?php echo $e_name;?>">
	<input type="hidden" name="add_id" id="add_id" value="<?php echo passport_encrypt($add_id);?>">
	<input type="hidden" name="user_status" id="user_status" value="<?php echo $user_status;?>">
	<input type="hidden" name="package_type" id="package_type" value="<?php echo $package_type;?>">
	<input type="hidden" name="user_id" id="user_id" value="<?php echo $user_id;?>">
	<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id;?>">
	<input type="hidden" name="package_id" id="package_id" value="<?php echo $package_id;?>">
<!-- <script src="js/mshop_share.js"></script> -->
<script>
var exp_id			= '<?php echo $exp_id;?>';
var express_ids		= '<?php echo $express_ids;?>';
var begintime		= '<?php echo $begintime;?>';
var nowtime			= <?php echo $nowtime;?>;
var endtime			= '<?php echo $endtime;?>';
var endtime1		= '<?php echo $endtime1;?>';
var isPanicBuy		= '<?php echo $isPanicBuy;?>';
var customer_id		= '<?php echo $customer_id_en;?>';
var customer_id2	= '<?php echo $customer_id;?>';
var user_status		= '<?php echo $user_status;?>';

</script>	
<script src="js/package/package_detail.js?ver=<?php echo time(); ?>"></script>
<?php require('../common/share.php'); ?>
</body>

</html>