<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../common/utility.php');
require('../common/utility_fun.php');
//头文件----start
require('../common/common_from.php');

$host = $_SERVER["HTTP_HOST"];
$new_baseurl = "http://".$host;

//echo $user_id;

//商城（大礼包），渠道开关
$is_packages_count = 0;
$is_packages       = 0;
$query="select count(1) as is_packages_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='升级大礼包' and c.id=cf.column_id";
$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
   $is_packages_count = $row->is_packages_count;
}
if($is_packages_count>0){
   $is_packages = 1;
}
//城市商圈（订餐），渠道开关 End

//城市商圈（订餐），渠道开关
$is_caterer_count = 0;
$is_caterer       = 0;
$query="select count(1) as is_caterer_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商圈-美食' and c.id=cf.column_id";
$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
   $is_caterer_count = $row->is_caterer_count;
}
if($is_caterer_count>0){
   $is_caterer = 1;
}
//城市商圈（订餐），渠道开关 End

//城市商圈（酒店），渠道开关
$is_hotel_count    = 0;
$is_hotel          = 0;
$query="select count(1) as is_hotel_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商圈-酒店' and c.id=cf.column_id";
$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
   $is_hotel_count = $row->is_hotel_count;
}
if($is_hotel_count>0){
   $is_hotel = 1;
}
//城市商圈（酒店），渠道开关 End

//城市商圈（KTV），渠道开关
$is_ktv_count    = 0;
$is_ktv          = 0;
$query="select count(1) as is_ktv_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商圈-ktv' and c.id=cf.column_id";
$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
   $is_ktv_count = $row->is_ktv_count;
}
if($is_ktv_count>0){
   $is_ktv = 1;
}
//城市商圈（KTV），渠道开关 End

//城市商圈（线下商城），渠道开关
$is_shop_off_count    = 0;
$is_shop_off          = 0;
$query="select count(1) as is_shop_off_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商圈-线下商城' and c.id=cf.column_id";
$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
   $is_shop_off_count = $row->is_shop_off_count;
}
if($is_shop_off_count>0){
   $is_shop_off = 1;
}
//城市商圈（线下商城），渠道开关 End

//查询订单开关
$setting_id = -1;
$query = "SELECT id,choose FROM weixin_commonshop_order_setting_cus where customer_id=".$customer_id." LIMIT 1";
$result= mysql_query($query);
while($row=mysql_fetch_object($result)){
	$setting_id = $row->id;
	$setting_choose = $row->choose;
}
//如果没有记录 则插一条默认 商城订单开启的记录---star
if($setting_id<0){
	$query = "select * from weixin_commonshop_order_setting where isvalid=true  order by sys_num asc  ";
	//echo $query;
	$result = mysql_query($query) or die('L15 Query failed: ' . mysql_error());
	$sys_num=-1;
	$choose_str = '';
	while ($row = mysql_fetch_object($result)) {
		$keyid 			= $row -> id;
		$sys_num 		= $row -> sys_num;
		
		$sys_c = '_0';
		if($sys_num==1){			//商城默认选择
			$sys_c = '_1';
		}
		$choose_str.= $sys_num.$sys_c.'|*|';
	}
	$query2 = "insert into weixin_commonshop_order_setting_cus(choose,customer_id,isvalid,createtime)values('".$choose_str."',".$customer_id.",1,now())";
    mysql_query($query2)or die('Query failed196'.mysql_error());
}else{
	$choose_arr = explode('|*|',$setting_choose);
	$choose_exp_arr = array();
	foreach($choose_arr as $value){
		//echo $value.'<br>';
		$temp = explode('_',$value);
		array_push($choose_exp_arr,$temp);
	 
	}
	//var_dump($choose_exp_arr);
}//如果没有记录 则插一条默认 商城订单开启的记录---end

?>

<!DOCTYPE html>
<html>
<head>
    <title>我的订单列表</title>
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
    
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
    <style type="text/css">
        #productContainerDiv{position: relative;}
        .header-mark{position:absolute;top:0px;width: 100%;}
        .header-mark div{font-weight: bold;padding:7px 0px 7px 10px;}
        .header-mark div img{width:16px;height:16px;}
        .header-mark div span{margin-left:5px;vertical-align:middle;}
        .productDiv{position:absolute;top: 43px;width:100%;overflow-y:auto; }
        i{display: block; width: 30px;height: 30px;margin:0 auto;background-image: url(./images/vic/icon_sprite.png);background-size: cover;}
		.typeFrame img{display: block;width: 30px;height: 30px;margin: 0 auto;background-size: cover;}
        .cat_shop{background-position: 0 0;}
        .cat_packages{background-position: 0px -270px;}
        .cat_cater_p{background-position: 0px -120px;}
        .cat_cater_t{background-position: 0px -210px;}
        .cat_cater_h{background-position: 0px -240px;}
        .cat_ktv_p{background-position: 0px -180px;}
        .cat_ktv_t{background-position: 0px -180px;}
        .cat_hotel_p{background-position: 0px -120px;}
        .cat_hotel_d{background-position: 0px -150px;}
        .cat_hotel_t{background-position: 0px -60px;}
        .cat_shop_off_p{background-position: 0px -120px;}
        .cat_shop_off_t{background-position: 0px 0px;}
        .cat_shop_off_d{background-position: 0px -30px;}

//ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }       


    </style>
</head>
<!-- Loading Screen -->
<link type="text/css" rel="stylesheet" href="./css/vic.css" />

<body data-ctrl=true class="white-back">
	<!--
	<header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav"  onclick="history.go(-1);">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="am-header-title">订单</h1>
	</header>
    <div class="topDiv"></div>
	-->
    
    <div id="productContainerDiv">
        <div class="header-mark light_gray_back">            
            <div>
            	<img src="./images/vic/icon_dingdan.png" width="20" height="20"/><span class="gray_text text_level3">订单</span>
            </div>            
        </div>
    	<div class="productDiv" id="productDiv">
				<?php if(in_array(array(2,1), $choose_exp_arr)){?>
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("shop");'>
					<div class='typeFrame'>
					<i class="cat_shop"></i>
					<div class='type_title'>商城</div>
					</div>
				</div>	
				<?php }
					if($is_packages && in_array(array(18,1), $choose_exp_arr)){ ?>
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("packages");'>
					<div class='typeFrame'>
					<i class="cat_packages"></i>
					<div class='type_title'>大礼包</div>
					</div>
				</div>
				<?php }
					  if(in_array(array(22,1), $choose_exp_arr)){ ?>
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("pay");'>
					<div class='typeFrame'>
					<i class="cat_cater_p"></i>
					<div class='type_title'>到店付</div>
					</div>
				</div>
				<?php }
					if($is_caterer && in_array(array(6,1), $choose_exp_arr)){
				?>					
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("cater_t");'>
					<div class='typeFrame'>
					<i class="cat_cater_t"></i>
					<div class='type_title'>外卖</div>
					</div>
				</div>
				<?php }
					if($is_caterer && in_array(array(4,1), $choose_exp_arr)){
				?>
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("cater_h");'>
					<div class='typeFrame'>
					<i class="cat_cater_h"></i>
					<div class='type_title'>订餐</div>
					</div>
				</div>
				<?php } 
					if($is_ktv && in_array(array(8,1), $choose_exp_arr)){ 
				?>					
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("ktv_t");'>
					<div class='typeFrame'>
					<i class="cat_ktv_t"></i>
					<div class='type_title'>KTV预约</div>
					</div>
				</div>
				<?php }
                      if($is_hotel && in_array(array(10,1), $choose_exp_arr)){
				?>	
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("hotel_d");'>
					<div class='typeFrame'>
					<i class="cat_hotel_d"></i>
					<div class='type_title'>酒店</div>
					</div>
				</div>
				<?php }?>				
				<?php if(in_array(array(14,1), $choose_exp_arr)){?>	
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("shop_off_t");'>
					<div class='typeFrame'>
					<i class="cat_shop_off_t"></i>
					<div class='type_title'>线下商城-自提</div>
					</div>
				</div>
				<?php }
					if(in_array(array(16,1), $choose_exp_arr)){
				?>					
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("shop_off_d");'>
					<div class='typeFrame'>
					<i class="cat_shop_off_d"></i>
					<div class='type_title'>线下商城-配送</div>
					</div>
				</div>
				<?php } 
					if(in_array(array(20,1), $choose_exp_arr)){
				?>	
				<div class='item3 button buttonclick'  onclick='gotoTypeDetail("nowpayOrder");'>
					<div class='typeFrame'>
					<img  src="./images/vic/offline_cashier.png"/>
					<div class='type_title'>线下收银</div>
					</div>
				</div>
				<?php }?>			
			
    </div>

<script type="text/javascript">
    var winWidth       = $(window).width();
    var winheight      = $(window).height();
	var customer_id_en = '<?php echo $customer_id_en; ?>';

	$(function() {        
        $("#productDiv").height(winheight-92);
	});
    
    $(window).resize(function() {
        winWidth = $(window).width();
        winheight = $(window).height();
        
        adjustSize();
    });
    
    function adjustSize(){
        $("#productDiv").height(winheight-92);
    }
       
    //跳转种类详细
    function gotoTypeDetail(type){
		// console.log(type);
		switch(type){			
			case "shop": //商城
			location.href="./orderlist.php?customer_id="+customer_id_en+"&currtype=1";      
			break;				
			case "packages": //大礼包
			location.href="./order_packages_list.php?customer_id="+customer_id_en;		 
			break;			
			case "cater_t": //餐饮-外卖
			// console.log(type);
            location.href="../common_shop/jiushop/order_list_cityarea.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=5&cityarea_type=1";      
			// location.href="./cityarea/orderlist_caterer_package.php?customer_id="+customer_id_en+"&user_id=<php echo passport_encrypt((string)$user_id) ?>&cityarea_type=1";
			break;
			case "cater_h": //餐饮-订餐
			// console.log(type);
            location.href="../common_shop/jiushop/order_list_cityarea.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=5&cityarea_type=2";      
			// location.href="./cityarea/orderlist_caterer_package.php?customer_id="+customer_id_en+"&user_id=<php echo passport_encrypt((string)$user_id) ?>&cityarea_type=2";
			break;	
			case "pay":    //到店付
			// console.log(type);
			location.href="./cityarea/orderlist_pay.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;		
			case "ktv_t": //KTV-团购
			// console.log(type);
			location.href="./cityarea/orderlist_ktv_package.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;	
			case "hotel_d": //酒店-全日房
			// console.log(type);
			location.href="./cityarea/orderlist_hotel_package.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;	
			case "hotel_t": //酒店-钟点房
			// console.log(type);
			location.href="./cityarea/orderlist_hotel_package.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;		
			case "shop_off_t": //线下商城-自提
			// console.log(type);
			location.href="../city_area/shop/order_list.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;
			case "shop_off_d": //线下商城-配送
			// console.log(type);
			location.href="../city_area/shop/order_list_take.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;
			case "nowpayOrder":
			location.href="../back_nowpaySystem/cashplatform/cust_olist.php?customer_id="+customer_id_en+"&user_id=<?php echo $user_id;?>&ord_status=-10";
			break;			
		}
    }
           
</script>
<?php require('./NoShare.php');?>

<!--引入侧边栏 start-->
<?php  include_once('float.php');?>
<!--引入侧边栏 end-->
</body>
</html>