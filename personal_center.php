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
//头文件----end
require('select_skin.php');

$host = $_SERVER["HTTP_HOST"];
$new_baseurl = "http://".$host;
 
$weixin_name 		= '';	//自己的名字
$weixin_fromuser 	= '';
$parent_name		= '';	//上级的名字
$parent_id 			= -1;
$account			= '';	//绑定的账号/手机
$balance			= 0 ;	//钱包余额
$total_money		= 0 ;	//总消费金额
$remain_score 		= 0 ;	//会员卡积分
$isAgent   			= -1;
$is_consume   		= -1;
$status   			= -1;
$a_name   			= '';
$b_name   			= '';
$c_name   			= '';
$d_name   			= '';
$exp_name   		= '';
$commision_level    = 1;
$has_change 		= 0;
//查微信名，上级名，绑定的手机，头像，二维码，钱包余额，总消费金额，股东等级，等

$query = "SELECT 
			weixin_name,
			parent_id,
			has_change,
			parent_id,
			weixin_headimgurl 			
	FROM weixin_users  
	WHERE isvalid=true AND id=".$user_id;
	$result = mysql_query($query) or die('Query failed63: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$weixin_fromuser 	= $row->weixin_fromuser;
		$weixin_name 		= mysql_real_escape_string($row->weixin_name);
		$parent_id 			= $row->parent_id;
		$has_change 		= $row->has_change;
		$weixin_headimgurl  = $row->weixin_headimgurl;
		if( $weixin_headimgurl == NULL || $weixin_headimgurl == ''){
            $weixin_headimgurl = './images/my_qrcode.png';
        }	
	}
//查询头像


//查询绑定账号-------------------start
$sql = "SELECT account FROM system_user_t WHERE isvalid=true AND customer_id=$customer_id AND user_id=$user_id LIMIT 1";
$res = mysql_query($sql) or die('Query failed74: ' . mysql_error());
while($row = mysql_fetch_object($res)){
	$account = $row->account;
}

//查询绑定账号-------------------end


//查询商家绑定的会员卡------star
$shop_card_id = -1;
$query = "SELECT shop_card_id,name,template_head_bg FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." limit 1";
$result= mysql_query($query);
while($row=mysql_fetch_object($result)){
	$shop_card_id = $row->shop_card_id;    //--------先查出商家现在绑定的是哪张会员卡
	$name = $row->name;
	$template_head_bg = $row->template_head_bg;
	$template_type_bg = $template_head_bg?1:0;
}
if($shop_card_id>0){
	$id = -1;
	$remain_score  = 0 ; //个人积分余额
	$query = "SELECT id FROM weixin_card_members WHERE isvalid=true AND user_id=".$user_id." AND card_id=".$shop_card_id." LIMIT 1";
	file_put_contents("aaa.txt",date("Y-m-d")."==query===".$query."\r\n",FILE_APPEND);
	$result= mysql_query($query);
	while($row=mysql_fetch_object($result)){
		$id = $row->id;						//----------根据商家绑定会员卡id跟user_id查出会员卡id
		if($id>0){
			$query = "SELECT remain_score FROM weixin_card_member_scores where isvalid=true AND card_member_id=".$id." LIMIT 1";
			$result= mysql_query($query);
			while($row2=mysql_fetch_object($result)){
				$remain_score = round($row2->remain_score,2);		//---------再拿会员卡id查出积分余额
			}
		}
	}
}
//查询商家绑定的会员卡------end


//查询推广员的等级，自定义名等
$promoter_id      = -1;
$exp_map_url      = '';
$name 		      = '微商城';
$exp_name 	      = '推广员自定义名称';
$modify_up        =  0;//是否开启修改上下级
$modify_type      =  0;//修改类型：0、顶级粉丝能修改一次关系；1、顶级用户能修改一次关系；2、所有用户能修改一次关系
$is_cashback      =  0;//是否开启消费返现
$isOpenreward	  = 0;	//是否开启累计佣金
$is_my_commission = 0;//是否开启我的佣金
$is_qr_code       = 0;//个人中心二维码海报开关，0关1开
$sql = "SELECT name,exp_name,is_cashback,modify_up,modify_type,is_my_commission,openbillboard,isOpenreward,is_qr_code FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." LIMIT 1";
$res = mysql_query($sql) or die('Query failed34: ' . mysql_error());
while( $row = mysql_fetch_object($res) ){
	$shop_name 		    = mysql_real_escape_string($row->name);			//微商城名称
	$exp_name 		    = $row->exp_name;		//推广员自定义名称
	$is_cashback 		= $row->is_cashback;	//是否开启消费返现
	$modify_up 	 		= $row->modify_up;		//是否开启修改上下级
	$modify_type 		= $row->modify_type;
	$isOpenreward		= $row->isOpenreward;	//是否开启累计佣金
	$is_my_commission	= $row->is_my_commission;//是否开启我的佣金
	$openbillboard 		= $row->openbillboard;	//龙虎榜
	$is_qr_code 		= $row->is_qr_code;	   //个人中心二维码海报开关，0关1开
}

$a_name   			= '';
$b_name   			= '';
$c_name   			= '';
$d_name   			= '';
$query = "SELECT a_name,b_name,c_name,d_name FROM weixin_commonshop_shareholder WHERE customer_id=$customer_id LIMIT 1";
$result= mysql_query($query) or die('Query failed 173: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
	$a_name   			= $row->a_name;
	$b_name   			= $row->b_name;
	$c_name   			= $row->c_name;
	$d_name   			= $row->d_name;
}

$query = "SELECT id,
				 isAgent,
				 is_consume,
				 status,
				 commision_level,
				 exp_map_url
		  FROM promoters p 
		  WHERE isvalid=TRUE AND isvalid=TRUE AND user_id=".$user_id." LIMIT 1";
$result = mysql_query($query) or die('Query failed89: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
	$promoter_id 		= $row->id;
	$isAgent   			= $row->isAgent;
	$is_consume   		= $row->is_consume;
	$status   			= $row->status;
	$exp_map_url		= $row->exp_map_url;	//推广二维码
	$commision_level 	= $row->commision_level;


	switch ($is_consume) {
			case '0':
				$pro_name = $exp_name;
			break;

			case '1':
				$pro_name = $d_name;
			break;

			case '2':
				$pro_name = $c_name;
			break;

			case '3':
				$pro_name = $b_name;
			break;

			case '4':
				$pro_name = $a_name;
			break;
	}

	//区域代理自定义 ----------start
	$query_team="select is_showcustomer,p_customer,c_customer,a_customer,diy_customer,is_diy_area from weixin_commonshop_team where isvalid=true and customer_id=".$customer_id;
	$is_showcustomer  = 1;				//开启自定义名称:0关，1开
	$is_diy_area 	  = 0;				//开启自定义区域:0关，1开
	$p_customer		  ="省级代理/";		//省代自定义名称
	$c_customer		  ="市级代理/";		//市代自定义名称
	$a_customer		  ="区级代理/";		//区代自定义名称
	$diy_customer	  ="自定义区域代理/";	//自定义级别自定义名称
	$result_team = mysql_query($query_team) or die('query_team failed'.mysql_error());  
	while($row = mysql_fetch_object($result_team)){
		$is_showcustomer 	  = $row->is_showcustomer;	
		$p_name_customer      = $row->p_customer;
		$c_name_customer      = $row->c_customer;
		$a_name_customer      = $row->a_customer;
		$diy_name_customer 	  = $row->diy_customer;
		$is_diy_area 	  	  = $row->is_diy_area;
	}
	if(true==$is_showcustomer){
		$p_customer = $p_name_customer;
		$c_customer = $c_name_customer;
		$a_customer = $a_name_customer;
		if(1 == $is_diy_area){
			$diy_customer = $diy_name_customer;
		}
	}
	//区域代理自定义 ----------end
	switch ($isAgent) {
		case '0':
			$Agent_name = "普通推广员";
			$AgentNum   = 4;
			break;
		case '1':
			$Agent_name = "代理商";
			$AgentNum   = 5;
			break;
		case '2':
			$Agent_name = "顶级推广员";
			$AgentNum   = 4;
			break;
		case '3':
			$Agent_name = "供应商";
			$AgentNum   = 3;
			break;
		case '4':
			$Agent_name = "技师";
			$AgentNum   = 4;
			break;
		case '5':
			$Agent_name = $a_customer;
			$AgentNum   = 1;
			break;
		case '6':
			$Agent_name = $c_customer;
			$AgentNum   = 1;
			break;
		case '7':
			$Agent_name = $p_customer;
			$AgentNum   = 1;
			break;
		case '8':
			$Agent_name = $diy_customer;
			$AgentNum   = 1;
			break;
			
		default:
			# code...
			break;
	}

	$c_exp_name = '';
	$query = "SELECT exp_name FROM weixin_commonshop_commisions WHERE customer_id=".$customer_id." AND level=".$commision_level." LIMIT 1";
	$res   = mysql_query($query);
	while($row=mysql_fetch_object($res)){
		$c_exp_name = $row->exp_name;//推广员等级；
	}
	if(!empty($c_exp_name)){
		$exp_name = $c_exp_name;
	}
}

//修改上下级
$is_modify_up = 0;
if( $modify_up ){
	switch($modify_type){
		case 0:
			if( $status < 1 and $parent_id < 0 ){
				$is_modify_up = 1;
			}
			break;
		case 1:
			if( $parent_id < 0 ){
				$is_modify_up = 1;
			}
			break;
		case 2:
			$is_modify_up = 1;
			break;
	}
} 


//查询自定义功能----------star
if($status>0){
	$query_custom	 ="select id,subscribe_id,need_score,imgurl from weixin_commonshop_subscribes where isvalid=true  and customer_id=".$customer_id." ORDER BY id desc limit 4";
}else{
	$query_custom	 ="select id,subscribe_id,need_score,imgurl from weixin_commonshop_subscribes where isvalid=true and is_needmember=0 and customer_id=".$customer_id." ORDER BY id desc limit 4";
}
$result_custom = mysql_query($query_custom) or die('Query failed167: ' . mysql_error());
$count   = mysql_num_rows($result_custom);
//查询自定义功能----------end

//判断用户是否商圈-外卖的配送员
$courier_id = -1;
$query = "select id from weixin_cityarea_takeaway_courier where isvalid=true and user_id=".$user_id." and customer_id=".$customer_id." limit 0,1";
$result = mysql_query($query) or die('L756 '.mysql_error());
while ($row = mysql_fetch_object($result)) {
   $courier_id = $row->id;
}
//判断用户是否商圈-外卖的配送员 End

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
	//print_r($choose_exp_arr);
}//如果没有记录 则插一条默认 商城订单开启的记录---end




//查询是否开启了品牌供应商店铺以及个人是否拥有店铺
$isOpenBrandSupply=0;//是否开启品牌供应商
$isbrand_supply=0;//是否成为品牌供应商
$brand_status=0;//作为页面判断能否进入店铺
$brand_tips="商家尚未开启品牌供应商";
$brandopen_query="select isOpenBrandSupply from weixin_commonshops where isvalid=true and customer_id=".$customer_id." limit 0,1";
$brandopen_result=mysql_query($brandopen_query) or die ("brandopen_query faild" .mysql_error());
while($row=mysql_fetch_object($brandopen_result)){
	$isOpenBrandSupply=$row->isOpenBrandSupply;
}
if($isOpenBrandSupply>0 && $user_id>0){
	$brandsupply_query="select isbrand_supply from weixin_commonshop_applysupplys where isvalid=true  and user_id=".$user_id." limit 0,1";
	$brandsupply_result=mysql_query($brandsupply_query) or die ("brandsupply_query faild" .mysql_error());
	while($row=mysql_fetch_object($brandsupply_result)){
		$isbrand_supply=$row->isbrand_supply;
	}
	if($isbrand_supply){
		$brand_status=1;//商家开通品牌供应商以及用户成为品牌供应商
	}else{
		$brand_tips="您尚未成为品牌供应商";
	}
}

//查询商家是否开启微店
$is_microshop = -1;//微店开关
$microshop_query="select is_microshop from weixin_commonshop_customer_microshop where customer_id=".$customer_id." limit 0,1";
$microshop_result=mysql_query($microshop_query) or die ("brandopen_query faild" .mysql_error());
while($row=mysql_fetch_object($microshop_result)){
	$is_microshop=$row->is_microshop;
}
$microshop_tips = "";
if($promoter_id<0 && $is_microshop>0){
	$microshop_tips = "你尚未成为推广员";
}



$query = "SELECT total_money FROM my_total_money WHERE isvalid=true AND user_id=$user_id LIMIT 1";
$result = mysql_query($query) or die('Query failed23: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
	$totalprice = cut_num($row->total_money,2);
}
if($totalprice==''){
	$totalprice = 0.00;
}
//查钱包余额--------start
$balance = 0.00;
$query = "SELECT balance FROM moneybag_t where isvalid=true AND user_id=".$user_id." LIMIT 1";
$result= mysql_query($query) or die('Query failed32: ' . mysql_error());
while($row=mysql_fetch_object($result)){
	$balance = cut_num($row->balance,2);
}
if($parent_id>0){
	$parent_name = '';
	$query = "SELECT weixin_name FROM weixin_users WHERE isvalid=true AND id=".$parent_id." LIMIT 1";
	$result= mysql_query($query) or die('Query failed39: ' . mysql_error());
	while( $row = mysql_fetch_object($result) ){
		$parent_name = $row->weixin_name;

	}
}



?>
<!DOCTYPE html>
<html>
<head>
    <title>个人中心</title>
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


    <!-- <link type="text/css" rel="stylesheet" href="./css/goods_css/dialog.css" /> -->
    <link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />
    <link type="text/css" rel="stylesheet" href="./css/personal.css" />
    <link  rel="stylesheet"  type="text/css"  href="../common_shop/common/tc_erweima/css/tc_erweima.css">
    <style type="text/css">
		.am-modal-dialog{border-radius:5px;border:none;}
		.am-modal-dialog img{width:100%;height:100%;border-radius:5px;}
		.code-box{width: 80%;margin-left: 0;left: 10%;top: 0;}
		.white-list ul{width: 100%;padding-left: 0;margin-bottom: 0;}
		.white-list ul li{width:50%;height:70px;float: left;border-top:1px solid #eee;border-right:1px solid #eee; list-style-type:none;background-color: #fff;}
		.white-list ul li:nth-child(even){border-right: none;}
		.white-list ul li img{width:35px;margin-top: 15px;margin-left: 15%;float: left;position: center;}
		.white-list ul li span{margin-left: 10px;margin-top: 30px;line-height: 5;font-size: 14px;color:#494949;}
		body .white-list .list-one .center-content {vertical-align:sub}
		.area-one img{width:20px;height:20px;}
		.white-list.dd i{display: block; width: 30px;height: 30px;margin:0 auto;background-image: url(./images/vic/icon_sprite.png);background-size: cover;}
        .cat_shop{background-position: 0 0;}
        .cat_packages{background-position: 0 -270px;}
        .cat_pay{background-position: 0 -120px;}
        .cat_cater_t{background-position: 0 -210px;}
        .cat_cater_h{background-position: 0 -240px;}
        .cat_ktv_p{background-position: 0 -180px;}
        .cat_ktv_t{background-position: 0 -180px;}
        .cat_hotel_d{background-position: 0 -150px;}
        .cat_hotel_t{background-position: 0 -60px;}
        .cat_shop_off_t{background-position: 0 0;}
        .cat_shop_off_d{background-position: 0 -30px;}
        .white-list i{display: block; width: 50px;height: 50px;margin:0 auto;background-image: url(./images/info_image/wode_sprite.png);background-size: cover;}
        .wodeqianbao{background-position: 0 -200px;}
        .wodetequan{background-position: 0 -300px;}
        .wodetuandui{background-position: 0 -400px;}
        .shouhuodizhi{background-position: 0 0;}
        .erweima{background-position: 0 -250px;}
        .wodeweidian{background-position: 0 -450px;}
        .wodedianpu{background-position: 0 -50px;}
        .leijishouyi{background-position: 0 -350px;}

        //ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }
        .buttonclick_editBanner:hover{
        	box-shadow:  0 0 10px 0 rgba(0,0,0,0.24);
        


        


<?php if($template_type_bg==1){?>
	#myInfoDiv{
		background: url(<?php echo $template_head_bg?>);
		background-size: 100% 100%;
	}
<?php }?>
    </style>
    
</head>

<!-- Loading Screen -->
<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif' style="width:40px;"/><p class=""></p></div>


<link type="text/css" rel="stylesheet" href="./css/basic.css" />

<body data-ctrl=true style="background:#f3f3f3;">
	<!-- <header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav" onclick="goBack();">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png" style="vertical-align:middle;"/><span style="margin-left:5px;">返回</span>
		</div>
	    <h1 class="am-header-title" style="font-size:18px;">个人中心</h1>
	</header>
	<div class="topDiv"></div> --><!-- 暂时屏蔽头部 -->
	<div class="am-modal am-modal-confirm code-box" tabindex="-1" id="my-confirm">
	  <img src="./images/btn_close.png"  style="width:10%;margin-left:70%;" onclick="CloseCode();"/>
	  <div class="am-modal-dialog" >
	      <img src="<?php echo "../pic/qr/".$customer_id."/exp_".$user_id.".jpg?ver=".time();?>" alt="" >
	  </div>
	  <div style="width: 100%; background: transparent none repeat scroll 0% 0%; height: 50px; line-height: 50px; text-align: center; float: left;">
	  	<div style="width: 71%; height: 50px; margin-left: 18%;">
	  		<span style="float:left;color:white;">长按图片保存二维码</span><img src="./images/refresh.png"  style="width:18px;" onclick="refresh();"/>
	  	</div>
	  </div>
	</div>
	
	
	
    
    <div id="myInfoDiv" style="position:relative;">
            <div class="info-one" style="width:100%;text-align: center;padding-top:15px;padding-bottom:10px;">
                    <img class="am-img-thumbnail am-circle" src="<?php echo $weixin_headimgurl?>">
            </div>
            <div class="my_info" style="font-size:16px;font-weight:bold;">
            	<span><?php echo $weixin_name?></span>
            	<img src="./images/info_image/iconfont-gerendengji<?php echo $commision_level;?>.png" style="width:18px;height:16px;">
            </div>
			
            	<div class="my_info"style="font-size:13px;"><span><?php echo $account;?></span></div>
			<?php if($parent_id>0){?>
            <div class="my_info" style="font-size:13px;">
            	<a  onclick="my_parent();" id="my_parent" style="text-decoration:underline;color:white;">推荐人：<?php echo $parent_name;?></a>
            </div>
            <?php }?>
            <div id="wode_member">
			<?php if( $status>0 && $is_consume > 0 ){?>
            	<div class="mem" ><img src="./images/info_image/wode_icon1.png"></img><span><?php echo $pro_name;?></span></div>
            <?php }?>
            <?php if( $status > 0 ){?>
            	<div class="mem" ><img src="./images/info_image/wode_icon4.png"></img><span><?php echo $exp_name;?></span></div>
            <?php }else{?>
				<div class="mem" ><img src="./images/info_image/wode_icon4.png"></img><span>您还没成为<?php echo $exp_name;?></span></div>
            <?php }?>
            <?php if( $status > 0 && $isAgent > 0 ){?>
            	<div class="mem" ><img src="./images/info_image/wode_icon<?php echo $AgentNum; ?>.png" style="width:16px;height:16px;"></img><span ><?php echo $Agent_name;?></span></div>
			 <?php }?>
            </div>
            <img id="editBanner" class="button buttonclick_editBanner" src="./images/vic/edit_banner.png" onclick="viewMyInfo();"/>
    </div>
    
    <div style="background-color: white;border-bottom: 1px solid #eee;border-top: 1px solid #eee;">
        <div style="clear: both;"></div>
        <div id="detail-count" style="padding:5px 0px 10px;">
            <div class="area-one buttonclick"  onclick="viewOthers('moneybag');">
                <div class="left" id="moneybag">
                	<?php echo $balance;?>
					<!-- <img src="./images/loading.gif" alt=""> -->
                </div>
                <div class="right">零钱</div>
            </div>
            <div class="area-line"></div>
            <div class="area-one buttonclick" onclick="viewOthers('score');">
                <div class="left" ><?php echo $remain_score;?></div>
                <div class="right">积分</div>
            </div>
            <div class="area-line"></div>
            <div class="area-one buttonclick"  onclick="viewOthers('consumer');">
                <div class="left" id="my_total_money">
                	<?php echo $totalprice;?>
					<!-- <img src="./images/loading.gif" alt=""> -->
                </div>
                <div class="right">消费总额</div>
            </div>
        </div>
    </div>

    <?php if(in_array(array(1,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one button buttonclick" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('shop',1);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">商城订单</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('shop',2);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('shop',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待发货</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('shop',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待收货</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('shop',5);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('shop',7);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>

	<?php if(in_array(array(3,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('meishi',5);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">订餐</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('meishi',1);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('meishi',2);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待确认</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('meishi',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待使用</div>
	            </div>
	            
				
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('meishi',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">已完成</div>
	            </div>
				
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('meishi',5);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>

    <?php if(in_array(array(5,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('waimai',5);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">外卖</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('waimai',1);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('waimai',2);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待发货</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('waimai',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待收货</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('waimai',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">已完成</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('waimai',5);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>

    <?php if(in_array(array(7,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('ktv',1);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">KTV</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('ktv',2);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('ktv',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待使用</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('ktv',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('ktv',5);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">已完成</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('ktv',6);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>

    <?php if(in_array(array(9,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('hotel',1);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">酒店</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('hotel',2);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('hotel',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待使用</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('hotel',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('hotel',5);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">已完成</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('hotel',6);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>


	<?php if(in_array(array(13,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('cityshop',1);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">线下商城-自提</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('cityshop',2);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('cityshop',3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待确认</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('cityshop',4);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待消费</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('cityshop',5);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('cityshop',6);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>

	<?php if(in_array(array(15,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('cityshop_take',11);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">线下商城-配送</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('cityshop',12);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('cityshop',13);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待发货</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('cityshop',14);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待收货</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('cityshop',15);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('cityshop',16);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>
	<?php if(in_array(array(19,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('nowpayOrder',-10);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">线下收银</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('nowpayOrder',-10);" class="area-one button buttonclick" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">全部</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('nowpayOrder',1);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">已支付</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('nowpayOrder',-1);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">已退款</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one button buttonclick" onclick="viewOrder('nowpayOrder',-3);"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one button buttonclick" onclick="viewOrder('nowpayOrder',2);"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">已结算</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>
	<?php if(in_array(array(21,1), $choose_exp_arr)){?>
    <div class="white-list" style="">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewOrder('pay',1);">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">到店付</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;width:91%;margin:0 auto">
	            <div id="dai_fukuan" onclick="viewOrder('pay',1);" class="area-one button buttonclick" style="width:32%!important;">
	                <img src="./images/order_image/icon_dingdan_quanbu.png" alt="">
	                <div class="wenzi_color">全部</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one button buttonclick" onclick="viewOrder('pay',2);"   style="width:32%!important;">
	                <img src="./images/info_image/dai_pingjia.png" alt="">
	                <div class="wenzi_color">已完成</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one button buttonclick" onclick="viewOrder('pay',4);"   style="width:32%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <?php }?>
    <?php 
    	$arr = array();
    	$arr = array(9,10,11,12,13,14,15,16,17,18);
    	$len = count($arr);
    	$function = 0;
    	for($i=0;$i<=$len;$i++){
    		if($choose_exp_arr[$arr[$i]][1]!=0){
    			$function = 1;
    		}
    	}
    	if($function == 1){
    	?>
    <div class="white-list dd">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:60%;"><span style="color:#494949;">订单</span></div>
            <div class="right-action button buttonclick"  onclick="viewOrders('other');">
            	<span>更多</span>
            	<img src="./images/vic/right_arrow.png" width="8" height="14" style="width: 8px;height: 14px;margin-right:15px;" alt="">
            </div>
        </div>
        <div class="line" style="margin-right: 10px;"></div>
            <div id="middle-tab" style="background-color: white;width:100%;margin:0 auto;font-size:1.12em;">

            	<?php if(in_array(array(18,1), $choose_exp_arr)){?>
	            <div  id="o_jiudian" class="area-one warp_list button buttonclick" onclick="viewOthers('packages');"  style="width:18%!important;">
	                <i class="cat_packages"></i>
	                <div class="wenzi_color">大礼包</div>
	            </div>
	            <?php }?>

				<?php if(in_array(array(2,1), $choose_exp_arr)){?>
            	<div  id="o_jiudian" class="area-one warp_list button buttonclick" onclick="viewOrder('shop',1);"  style="width:18%!important;">
	                <i class="cat_shop"></i>
	                <div class="wenzi_color">商城</div>
	            </div>
				<?php }?>
            	<?php if(in_array(array(4,1), $choose_exp_arr)){?>
	            <div id="o_shangcheng" onclick="viewOrders('cater_h');" class="area-one warp_list button buttonclick" style="width:19%!important;">
	                <i class="cat_cater_h"></i>
	                <div class="wenzi_color">订餐</div>
	            </div>
	            <?php }?>
	            
	            <?php if(in_array(array(6,1), $choose_exp_arr)){?>
	            <div id="o_daodianfu" class="area-one warp_list button buttonclick" onclick="viewOrders('cater_t');"   style="width:18%!important;">
	                <i class="cat_cater_t"></i>
	                <div class="wenzi_color" style="font-size: 0.8em;">外卖</div>
	            </div>
	            <?php }?>

	            <?php if(in_array(array(8,1), $choose_exp_arr)){?>
	            <div  id="o_waimai"  class="area-one warp_list button buttonclick" onclick="viewOrders('ktv');"  style="width:18%!important;">
	                <i class="cat_ktv_p"></i>
	                <div class="wenzi_color" style="font-size: 0.8em;">KTV</div>
	            </div>
	            <?php }?>

	            <?php if(in_array(array(10,1), $choose_exp_arr)){?>
	            <div  id="o_meishi"  class="area-one warp_list button buttonclick" onclick="viewOrders('hotel_d');"  style="width:18%!important;">
	                <i class="cat_hotel_d"></i>
	                <div class="wenzi_color" style="font-size: 0.8em;">酒店</div>
	            </div>
	            <?php }?>

	            <?php /* if(in_array(array(12,1), $choose_exp_arr)){?>
	            <div  id="o_jiudian" class="area-one warp_list" onclick="viewOrders('hotel_t');"  style="width:18%!important;">
	                <i class="cat_hotel_t"></i>
	                <div class="wenzi_color">钟点房</div>
	            </div>
	            <?php } */?>

	            <?php if(in_array(array(14,1), $choose_exp_arr)){?>
	            <div  id="o_jiudian" class="area-one warp_list button buttonclick" onclick="viewOrders('shopgetself');"  style="width:18%!important;">
	                <i class="cat_shop_off_t"></i>
	                <div class="wenzi_color">线下自提</div>
	            </div>
	            <?php }?>

	            <?php if(in_array(array(16,1), $choose_exp_arr)){?>
	            <div  id="o_jiudian" class="area-one warp_list button buttonclick" onclick="viewOrders('shopsend');"  style="width:18%!important;">
	                <i class="cat_shop_off_d"></i>
	                <div class="wenzi_color">线下配送</div>
	            </div>
	            <?php }?>
				<?php if(in_array(array(22,1), $choose_exp_arr)){?>
	            <div id="o_pay" class="area-one warp_list button buttonclick" onclick="viewOrders('pay');"   style="width:18%!important;">
	                <i class="cat_pay"></i>
	                <div class="wenzi_color">到店付</div>
	            </div>
	            <?php }?>
	            
	        </div>
        
    </div>
    <?php }?>

    <div class="white-list">
            <div id="middle-tab" style="background-color: white;padding:5px;">
	            <div id="wode_qianbao" onclick="viewOthers('moneybag');" class="area-one gongneng button buttonclick" >
	                <i class="wodeqianbao"></i>
	                <div class="wenzi_color">我的钱包</div>
	            </div>
	            
	            <div id="wode_tequan" class="area-one gongneng button buttonclick" onclick="viewOthers('myprivilege');"   >
	                <i class="wodetequan"></i>
	                <div class="wenzi_color">我的特权</div>
	            </div>
	            <?php if( $status > 0 ){?>
	           <div  id="wode_tuandui"  class="area-one gongneng button buttonclick" onclick="viewOthers('myteam');" >
	                <i class="wodetuandui"></i>
	                <div class="wenzi_color" >我的团队</div>
	            </div>
	            <?php if($isOpenreward){?>
	            <div id="wode_shouyi"  class="area-one gongneng button buttonclick" onclick="viewOthers('myprofit');">
	                <i class="leijishouyi"></i>
	                <div class="wenzi_color" >累积收益</div>
	            </div>
	            <?php }?>
	            <?php }?>
	            <div  id="wode_fahuodizhi" class="area-one gongneng button buttonclick" onclick="viewOthers('my_address');">
	                <i class="shouhuodizhi"></i>
	                <div class="wenzi_color">收货地址</div>
	            </div>
				
				<?php if( $is_qr_code==1 &&  $status > 0 ){?>
	            <div  id="wode_qrcode" class="area-one gongneng button buttonclick" onclick="viewOthers('qrcode');" >
	                <i class="erweima"></i>
	                <div class="wenzi_color" >二维码</div>
	            </div>
				<?php } ?>
				<?php if( $status > 0 && $is_microshop==1){?><!--推广员才显示--> 
	            <div  id="wode_weidian" class="area-one gongneng button buttonclick" onclick="viewOthers('mymicroshop');" >
	                <i class="wodeweidian"></i>
	                <div class="wenzi_color" >我的微店</div>
	            </div>
				<?php }?>
				<?php if( $isAgent == 3 ){?><!--供应商才显示-->
	            <div  id="wode_dianfu" class="area-one gongneng button buttonclick" onclick="viewOthers('mystore');" >
	                <i class="wodedianpu"></i>
	                <div class="wenzi_color" >我的店铺</div>
	            </div>
				<?php }?>
	        </div>
        
    </div>
    
    <div class="white-list" id="my_function">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;">
            <div class="left-title" style="max-width:35px"><img src="./images/info_image/gongneng.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:60%;"><span style="color:#494949;">功能</span></div>
            <div class="right-action button buttonclick"  style="" onclick="viewOthers('function_all');">
            	<span>更多</span>
            	<img src="./images/vic/right_arrow.png" class="right_arrow" alt="">
            </div>
        </div>
	    <div class="other">
			<ul class="function">
				<?php 
					if($courier_id>0){
				?>			
				<li class="button buttonclick" onclick='goPage("../common_shop/jiushop/order_list_takeaway_courier.php?user_id=<?php echo passport_encrypt((string)$user_id) ?>&customer_id=<?php echo $customer_id_en; ?>&currtype=1")'>
					<img src="./images/o2o_courier.png" alt="O2O配送员">
					<span>外卖配送员</span>
				</li>			
				<?php 
					}
					if($is_cashback==1){
				?>
				<li class="button buttonclick" onclick='goPage("../common_shop/jiushop/cashback.php?user_id=<?php echo passport_encrypt((string)$user_id) ?>&customer_id=<?php echo $customer_id_en; ?>")'>
					<img src="./images/info_image/wode_fanxian.png" alt="我的返现">
					<span>我的返现</span>
				</li>
				<?php }
					  if( $is_my_commission == 1 && $promoter_id > 0){ ?>
<!-- 				<li onclick='goPage("../common_shop/jiushop/my_reward.php?user_id=<?php echo passport_encrypt((string)$user_id) ?>&customer_id=<?php echo $customer_id_en; ?>; ?>")'>		
					<img src="./images/info_image/commission.png" alt="我的佣金">
					<span>我的佣金</span>
				</li> -->	
				<?php }?>



				<?php if( $openbillboard == 1 && $promoter_id > 0){ ?>
				<li class="button buttonclick" onclick='goPage("./longhuban.php?customer_id=<?php echo $customer_id_en; ?>")'>		
					<img src="./images/info_image/dragon.png" alt="店铺龙虎榜">
					<span>店铺龙虎榜</span>
				</li>	
				<?php }?>


				<?php 	
					$is_charitable        = 0;//慈善开关
					$query ="select is_charitable from charitable_set_t where isvalid=true and customer_id=".$customer_id;
					$result = mysql_query($query) or die('Query failed: ' . mysql_error());
					while ($row = mysql_fetch_object($result)) {
						$is_charitable        = $row->is_charitable;
					}

					if( $is_charitable==1 ){
				?>
				<li class="button buttonclick" onclick='goPage("../common_shop/jiushop/charitable.php?customer_id=<?php echo $customer_id_en; ?>&user_id=<?php echo passport_encrypt((string)$user_id) ?>")'>		
					<img src="./images/info_image/charity.png" alt="我的慈善">
					<span>我的慈善</span>
				</li>
				<?php }?>
				<!--修改上下级-->
				<?php if( $is_modify_up ==1 and $has_change == 0 and $promoter_id > 0){ ?>
				<li class="button buttonclick" onclick='goPage("../common_shop/jiushop/change_relation_user.php?customer_id=<?php echo $customer_id_en; ?>&user_id=<?php echo passport_encrypt((string)$user_id) ?>")'>		
					<img src="./images/info_image/superior.png" alt="修改上级">
					<span>修改上级</span>
				</li>	
				<?php }?>

				<?php 
						$imgurl ='';
					while ($row_c = mysql_fetch_object($result_custom)) {
						$cs_id = $row_c->id;
						$subscribe_id = $row_c->subscribe_id;
						$need_score = $row_c->need_score;
						$imgurl =$row_c->imgurl;						
						if($imgurl == '' ){
							$imgurl='/weixinpl/mshop/images/info_image/function.png';
						}						
						$imgurl = $new_baseurl.$imgurl;
						$query = "SELECT id,title,website_url FROM weixin_subscribes where  id=".$subscribe_id;
						$result = mysql_query($query) or die('Query failed: ' . mysql_error());
						$website_url="";
						$title="";
						while ($row = mysql_fetch_object($result)) {
							$website_url = $row->website_url;
							$title = $row->title;
						}
						$pos = strpos($website_url,"?"); 
						if($pos>0){
							$website_url = $website_url."&C_id=".$customer_id."&fromuser=".$weixin_fromuser;
						}else{
							$website_url = $website_url."?C_id=".$customer_id."&fromuser=".$weixin_fromuser;
						}
						$mppos= strstr($title,"{weixin_title}");
						if(!empty($mppos)){
							$title = str_replace("{weixin_title}",$weixin_name,$title);
						}
							$mppos= strstr($title,"{weixin_parent_title}");
						if(!empty($mppos) and $parent_id>0){
							$query="select weixin_name from weixin_users where  isvalid=true and id=".$parent_id." limit 0,1";
							$result = mysql_query($query) or die('Query failed: ' . mysql_error());
							$parent_weixin_name="";
							while ($row = mysql_fetch_object($result)) {
								$parent_weixin_name = $row->weixin_name;
							}
								$title = str_replace("{weixin_parent_title}",$parent_weixin_name,$title);
						}
				?>
				<li onclick='goPage("<?php echo $website_url?>")'>
					<img src="<?php echo $imgurl?>" alt="<?php echo $title;?>">
					<span><?php echo $title;?></span>
				</li>
				<?php }?>
			</ul>
		</div>




        </div>
     
    </div>
    
</body>		
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
<script type="text/javascript" src="./assets/js/amazeui.js"></script>
<script type="text/javascript" src="./js/global.js"></script>
<script type="text/javascript" src="./js/loading.js"></script>
<script src="./js/jquery.ellipsis.js"></script>
<script src="./js/jquery.ellipsis.unobtrusive.js"></script>
<script type="text/javascript">

$(function(){

	var my_function = $(".function li").length;
	//alert(my_function);
	if(my_function==0){
		$("#my_function").hide();
	}

	$(".warp_list").slice(5).hide();
	$(".other ul li").slice(4).hide();
	var exp_map_url = "<?php echo $exp_map_url;?>";
	if(exp_map_url==''){
		refresh();
	}
})

   var winWidth       = $(window).width();
   var winheight      = $(window).height();
   var qrcode_width   = winWidth*2/3;
   var user_id        = <?php echo $user_id; ?>;
   var user_id_en     = '<?php echo passport_encrypt((string)$user_id) ?>';
   var customer_id    = <?php echo $customer_id; ?>;
   var customer_id_en = '<?php echo $customer_id_en; ?>';
   var tequan_type    = 0; //0:股东，1：区域代理商，2：推广员
   var brand_status   = <?php echo $brand_status;?>;
   var is_microshop   = <?php echo $is_microshop;?>;
   var brand_tips     = "<?php echo $brand_tips;?>";
   var microshop_tips = "<?php echo $microshop_tips;?>";
   
   
   function viewOthers(type){
   	 if(type=='my_address'){
   	 	window.location.href = "my_address.php?customer_id="+customer_id_en+'&a_type=-1';    //有收货地址的情况下	
   	 	//window.location.href = "tianxiedizhi.html"; //空的情况下
   	 }else if(type=='mystore')//跳转我的店铺
   	 {	
		if(brand_status==0){
			showAlertMsg ("提示：",brand_tips,"知道了");
		}else{
			window.location.href = "my_store/my_store.php?customer_id="+customer_id_en+"&supplier_id="+user_id;	
		} 	 	
   	 
   	 }else if(type=='mymicroshop')//跳转我的微店
   	 {
		if(microshop_tips!=""){
			showAlertMsg ("提示：",microshop_tips,"知道了");
		}else if(is_microshop==0){
			showAlertMsg ("提示：","商家未开通该功能","知道了");
		}else{
			window.location.href = "my_microshop/my_microshop.php?customer_id="+customer_id_en;
		}
   	 }else if(type=='qrcode')// 显示我的二维码
   	 {
   	 	myUpload(callbackConfirm);
   	 	var mh=$(window).height()*0.8;
   	 	$('.am-modal-dialog').find('img').css('maxHeight',mh);
        //showShopQrCode();
   	 }else if(type=='myprofit')//累计收益
   	 {
   	 	window.location.href = "my_profit.php?customer_id="+customer_id_en;
   	 }else if(type=='moneybag')
     {
        window.location.href = "my_moneybag.php?customer_id="+customer_id_en;
     }else if(type=='myteam')
     {
        window.location.href = "myteam.php?customer_id="+customer_id_en;
     }else if(type=='myprivilege')
     {
            window.location.href = "my_privilege.php?customer_id="+customer_id_en;
     }else if(type=='consumer')
     {
     		window.location.href ="my_consumer_money.php?customer_id="+customer_id_en;
     }else if(type=='score')
     {
     		window.location.href ="my_card_score_log.php?customer_id="+customer_id_en;
     }else if(type=='function_all')
     {	
     		window.location.href = "my_function.php?customer_id="+customer_id_en;
     }else if(type=='packages')
     {
   			window.location.href = "order_packages_list.php?customer_id="+customer_id_en;		
     }
   }


	function viewMyInfo()
	{
        //showAlertMsg("提示：","您好，您的退货信息已经发送给商家。请耐心等待商家审核！","知道了");
        
		window.location.href="my_data.php?customer_id="+customer_id_en;
	}

	function goPage(url){
		if(url!==''){
			window.location.href=url;
		}
	}

	function myUpload(callbackfunc) {
        $('#my-confirm').modal({
	        relatedTarget: this,
	        onConfirm: function(options) {
                callbackfunc("ok");
	        },
	        onCancel: function() {
	          	callbackfunc('cancel');
	        }
		});
		$('#my-confirm').css('marginTop',0);
	}

	function callbackConfirm_notice(retVal) {
    	alert(retVal);
 	}
	function callbackConfirm(retVal) {
    	alert(retVal);
 	}
	function close(){
		CloseCode();
	}

	function CloseCode(){
		$('.am-dimmer').click();
	}

	function refresh(){
		$(".am-modal-dialog").html('<i class="wx_loading_icon"></i>');
		var exp_map_url 		= "<?php echo $exp_map_url;?>";
		var customer_id 		= "<?php echo $customer_id;?>";
		var user_id 			= <?php echo $user_id;?>;
		var shop_name 			= '<?php echo $shop_name;?>';
		var own_status 			= <?php echo $status;?>;
		var weixin_name 		= '<?php echo $weixin_name;?>';
		var weixin_headimgurl 	= "<?php echo $weixin_headimgurl;?>";
		var fromuser 			= "<?php echo $weixin_fromuser;?>";
		var promoter_id 		= <?php echo $promoter_id;?>;
		var commision_level 	= "<?php echo $commision_level;?>";	//1-8级级别
		var isAgent 	        = "<?php echo $isAgent;?>";			//0:普通推广员；1：代理；2：顶级推广员(现在没用了)；3：供应商；4：技师；5：区代；6：市代；7：省代;8:自定义区域
		var is_consume 	        = "<?php echo $is_consume;?>";		//是否满足消费无限级奖励/股东分红 0:普通推广员 1:代理 2:渠道 3:总代理 4:股东

		$.ajax({
			
	        type: "post",
	        url: "../common_shop/common/tc_erweima/tc_erweima.php",
			dataType:'json',
	        data: { 
				customer_id:customer_id,
				user_id:user_id,
				shop_name:shop_name,
				exp_map_url:exp_map_url,
				own_status:own_status,
				weixin_name:weixin_name,
				weixin_headimgurl:weixin_headimgurl,
				fromuser:fromuser,
				promoter_id:promoter_id,
				commision_level:commision_level,
				isAgent:isAgent,
				is_consume:is_consume,
				op:"reflesh"
				
			},
	        success: function (result) {
				switch(result.type){
					case 1:
						$(".am-modal-dialog").html('<img src="'+result.exp_map_url+'">');
						var mh=$(window).height()*0.8;
   	 					$('.am-modal-dialog').find('img').css('maxHeight',mh);
						$('.wx_loading_icon').hide();
					break; 
					default:

					break; 
				} 
				
				
	        }    
    
		});
	}


	//表单提交去查询推荐人信息
	function my_parent(){
		var persion_id = "<?php echo $parent_id;?>";
		var customer_id = "<?php echo $customer_id_en;?>";
		var objform = document.createElement('form');
		document.body.appendChild(objform);
		var obj_p = document.createElement("input");
		var obj_c = document.createElement("input");
		obj_p.type = "hidden";
		obj_c.type = "hidden";
		objform.appendChild(obj_p);
		objform.appendChild(obj_c);
		obj_p.value = persion_id;
		obj_p.name = 'persion_id';
		obj_c.value = customer_id;
		obj_c.name = 'customer_id';
		objform.action = 'team_person.php?customer_id'+customer_id_en;
		objform.method = "POST";
		objform.submit();

	}

	
	function viewOrders(type){
		console.log(type);
		switch(type){
			case "other": //更多
			location.href="orderlist_list.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>";
			break;			
			case "cater_t": //外卖
			location.href="../common_shop/jiushop/order_list_cityarea.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=5&cityarea_type=1";
			break;
			case "cater_h": //美食
			location.href="../common_shop/jiushop/order_list_cityarea.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=5&cityarea_type=2";
			break;	
            case "shopgetself": //线下商城-自提订单
            location.href="../city_area/shop/order_list.php?customer_id="+customer_id_en+"&currtype=1";
            break;
            case "shopsend": //线下商城-配送订单
			location.href="../city_area/shop/order_list_take.php?customer_id="+customer_id_en+"&currtype=11";
			break;	
            case "hotel_d": //酒店
			location.href="./cityarea/orderlist_hotel_package.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=1";
			break;
            case "ktv":      //KTV
			location.href="./cityarea/orderlist_ktv_package.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=1";
			break;			
			case "pay":      //到店付	
			location.href="./cityarea/orderlist_pay.php?customer_id="+customer_id_en+"&user_id=<?php echo passport_encrypt((string)$user_id) ?>&currtype=1";	
			break;			
			case "nowpaySystem":   //线下收银
			var url="../back_nowpaySystem/cashplatform/cust_olist.php?user_id=<?php echo $user_id;?>&ord_status=-10";		
			location.href=url;
			break;			
		}
	}


	    
</script>
<?php require('../common/share.php'); ?>
<!--引入侧边栏 start-->
<?php  include_once('float.php');?>
<!--引入侧边栏 end-->
<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif' style="width:40px;"/><p class=""></p></div>

</html>