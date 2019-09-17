<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
/*require('../common/jssdk.php');
$jssdk = new JSSDK($customer_id);
$signPackage = $jssdk->GetSignPackage();*/
//头文件----start
require('../common/common_from.php');
require('select_skin.php');
//头文件----end
$pay_batchcode = '';	//支付订单号
$batchcode 	   = '';
if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
if(!empty($_GET['pay_batchcode'])){
	$pay_batchcode = $configutil->splash_new($_GET['pay_batchcode']);
}else if(!empty($_GET['batchcode'])){
	$batchcode = $configutil->splash_new($_GET['batchcode']);
}else{
	header('Location:errors.php?customer_id='.$customer_id_en.'&msg=缺少订单号&url=orderlist.php&currtype=1');
}
/*** 代付信息 start ***/
$payother_desc_id = -1;	//代付ID
$pay_user_id 	  = -1;	//支付人ID
$note			  = "";	//支付者留言
$pay_username 	  = "";	//支付者名字
/*** 代付信息 end ***/

if(!empty($batchcode)){

    /*
	$as_id 			 = -1;
	$as_pid 		 = -1;	//售后产品ID
	$supply_id 		 = -1;	//供应商ID
	$prvalues 		 = '';	//产品属性
	$rcount 		 = 0;	//产品数量
	$rtype 			 = 1;	//申请类型：1:退换货;2:售后
	$returntype 	 = -1;	//退货类型：1:退货;2换货;3:退款
	$account 		 = 0;	//申请退款金额
	$confirm_account = 0;	//最终退款金额
	$as_createtime 	 = '';	//申请售后时间
	$checktime		 = '';	//商家处理时间
	$status 		 = 1;	//申请状态：1:申请中;2:商家已同意;3:商家已拒绝;4:用户已发货;5:商家已收货;6:商家已退款;7:申请已处理完毕
    $query_as = "select id,pid,batchcode,supply_id,prvalues,rcount,rtype,returntype,account,confirm_account,createtime,checktime,status,remark from weixin_commonshop_order_aftersale where isvalid = true and batchcode = '".$batchcode."'";
	$result_as = mysql_query($query_as) or die('query_as failed'.mysql_error());
	while($row_as = mysql_fetch_object($result_as)){
		$as_id 			 = $row_as->id;
		$as_pid 		 = $row_as->pid;
		$supply_id 		 = $row_as->supply_id;
		$prvalues 		 = $row_as->prvalues;
		$rcount 		 = $row_as->rcount;
		$rtype 			 = $row_as->rtype;
		$returntype 	 = $row_as->returntype;
		$account 		 = $row_as->account;
		$confirm_account = $row_as->confirm_account;
		$as_createtime 	 = $row_as->createtime;
		$checktime 	 	 = $row_as->checktime;
		$status 		 = $row_as->status;
		$remark 		 = $row_as->remark;
	}
	*/
	//if($as_id<0){
		$query = "SELECT id,pay_batchcode,paystyle,sendstatus,status,sendstyle,remark,merchant_remark,createtime,paystatus
        ,express_id,is_discuss,confirm_sendtime,confirm_receivetime,supply_id,auto_receivetime,is_delay
        ,return_type,return_status,aftersale_state,aftersale_reason,paytime,expressnum,allipay_orderid,is_QR
         from weixin_commonshop_orders where isvalid=true and batchcode = '".$batchcode."' limit 0,1";

		$result = mysql_query($query) or die('Query OrderList failed: ' . mysql_error());

		$supply_id		  = -1;		//供应商编号
		$is_delay 		  = 0;		//是否申请延时
		$return_type 	  = -1;		//退货类型
		$return_status 	  = -1;		//退货状态
		$aftersale_state  = 0;		//售后状态
		$aftersale_reason = "";		//申请售后原因
		$expressnum 	  = "";		//快递单号
		$pay_batchcode2	  = "";		//支付订单号
		$paystatus		  = -1;		//支付状态
		$status		  	  = 0;		//订单状态
		$paystyle		  = '';		//支付方式
		$sendstatus		  = 0;		//发货状态
		
		while ($row = mysql_fetch_object($result)) {	
			$pay_batchcode2		 = $row->pay_batchcode;
			$order_id 			 = $row->id;
			$createtime 		 = $row->createtime;
			$paystyle			 = $row->paystyle;
			$paystatus 			 = $row->paystatus;
			$sendstyle			 = $row->sendstyle;
			$sendstatus 		 = $row->sendstatus;
			$status 			 = $row->status;
			$express_id 		 = $row->express_id;
			$supply_id 			 = $row->supply_id;				//供应商ID
			$is_discuss 		 = $row->is_discuss;  			//是否评论 0:无 1:评论 2:追加
			$confirm_receivetime = $row->confirm_receivetime;   //收货时间
			$auto_receivetime 	 = $row->auto_receivetime;
			$is_delay 			 = $row->is_delay;
			$return_type 		 = $row->return_type;
			$return_status 		 = $row->return_status;
			$aftersale_state 	 = $row->aftersale_state;
			$aftersale_reason 	 = $row->aftersale_reason;
			$paytime 			 = $row->paytime;				//支付时间
			$confirm_sendtime 	 = $row->confirm_sendtime;	//发货时间
			$allipay_orderid 	 = $row->allipay_orderid;		//支付宝支付单号
			$expressnum 	 	 = $row->expressnum;			//快递单号
			$remark 		 	 = $row->remark;				//订单备注
			$merchant_remark 	 = $row->merchant_remark;		//商家备注
			$date=0;
			$date=floor((strtotime($now)-strtotime($confirm_receivetime))/86400);    //计算收货时间与现在相差时间
            $is_QR = $row -> is_QR;
		}
		/*** 查询代付信息 start ***/
		$query_payother = "select id,pay_user_id,pay_username,note from weixin_commonshop_otherpay_descs where isvalid=true and user_id=".$user_id." and batchcode='".$pay_batchcode2."' limit 1";
		$result_payother = mysql_query($query_payother) or die('query_payother failed'.mysql_error());
		while($row_payother = mysql_fetch_object($result_payother)){
			$payother_desc_id = $row_payother->id;
			$pay_user_id 	  = $row_payother->pay_user_id;
			$pay_username 	  = $row_payother->pay_username;
			$note 	  		  = $row_payother->note;
		}
		if($payother_desc_id<0){
			$query_payother = "select id,pay_user_id,pay_username,note from weixin_commonshop_otherpay_descs where isvalid=true and user_id=".$user_id." and batchcode='".$batchcode."' limit 1";
			$result_payother = mysql_query($query_payother) or die('query_payother failed'.mysql_error());
			while($row_payother = mysql_fetch_object($result_payother)){
				$payother_desc_id = $row_payother->id;
				$pay_user_id 	  = $row_payother->pay_user_id;
				$pay_username 	  = $row_payother->pay_username;
				$note 	  		  = $row_payother->note;
			}
		}
		/*** 查询代付信息 end ***/
	/*}else{
		$query = "select sendstatus,aftersale_state from weixin_commonshop_orders where isvalid=true and batchcode='".$batchcode."' limit 0,1";
		$result = mysql_query($query) or die('$query failed:'.mysql_error());
		while($row = mysql_fetch_object($result)){
			$sendstatus 	 = $row->sendstatus;
			$aftersale_state = $row->aftersale_state;
		}
	}*/
	/*
	$shop_show_name  = ""; //显示的商城名
	$brand_supply_id = -1; //品牌供应商ID
	if($supply_id>0){
		$sql_supplyname = "select id,brand_supply_name from weixin_commonshop_brand_supplys where isvalid=true and user_id=".$supply_id;
		$result_supply = mysql_query($sql_supplyname) or die('query sql_supplyname failed3' . mysql_error());
		if ($row_supply = mysql_fetch_object($result_supply)) {
			$brand_supply_id = $row_supply->id;                
			$shop_show_name  = $row_supply->brand_supply_name;                //店铺名
		}
	}else{
		//查询商城名
		$sql_shopname = "select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
		$result_shop = mysql_query($sql_shopname) or die('query sql_shopname failed'.mysql_error());
		if($row_shop = mysql_fetch_object($result_shop)) {
			$shop_show_name = $row_shop->name;					//商家名
		}
	}
	*/
	//支付方式开关
	$is_alipay   	= false;				//支付宝支付开关
	$is_weipay   	= false;				//商城微信支付开关
	$is_tenpay   	= false;				//商城财付通开关	
	$is_allinpay 	= false;				//商城通联支付开关	
	$isdelivery  	= false;				//商城货到付款开关0关闭1开启
	$is_payChange   = false;				//零钱支付开关	
	$is_pay         = false;				//暂不支付开关	
	$iscard      	= false;				//商城会员卡支付开关
	$isshop      	= false;				//商城到店支付开关
	$is_payother 	= false;				//是否开启代付
	$is_paypal	 	= false;				//paypal支付
	$isOpenCurrency = false;				//购物币支付开关	
	$query = 'SELECT id,is_alipay,is_tenpay,is_payChange,is_pay,is_weipay,is_allinpay,isdelivery,iscard,isshop,is_payother,is_paypal FROM customers where isvalid=true and id='.$customer_id;
	$defaultpay = "";
	$result = mysql_query($query) or die('W75 Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$is_alipay    = $row->is_alipay;
		$is_tenpay    = $row->is_tenpay;
		$is_weipay    = $row->is_weipay;
		$is_pay       = $row->is_pay;
		$is_payChange = $row->is_payChange;
		$is_allinpay  = $row->is_allinpay;
		$iscard       = $row->iscard;
		$isdelivery   = $row->isdelivery;
		$isshop       = $row->isshop;
		$is_payother  = $row->is_payother;
		$is_paypal    = $row->is_paypal;
		break;
	}
	
	//当前用户的购物币数量
    $user_curr = 0;
    $sql_user = "select id,currency from weixin_commonshop_user_currency where isvalid = true and user_id = ".$user_id;
    $res_user = mysql_query($sql_user) or die("sql_user query error : ".mysql_error());
    if($row_user = mysql_fetch_object($res_user)){
		$user_curr = $row_user -> currency;
    }
	$user_curr = round($user_curr,2);
    //是否开启使用购物币
	$custom = '购物币';	//自定义购物币名称
    $sql_cur  = "SELECT isOpen,custom FROM weixin_commonshop_currency WHERE isvalid = true and customer_id=".$customer_id;
    $res_cur = mysql_query($sql_cur) or die("sql_cur failed:".mysql_error());
    if ($row_cur = mysql_fetch_object($res_cur) ){
		$isOpenCurrency = $row_cur->isOpen;
		$custom 		= $row_cur->custom;
	}
	
	/*** 下单时是否使用了代金券、购物币和会员卡折扣 start***/
	$pay_currency = 0;	//购物币
	$coupon		  = 0;	//代金券金额
	$query_cac = "select currency,coupon from order_currencyandcoupon_t where user_id=".$user_id." and customer_id=".$customer_id." and pay_batchcode='".$pay_batchcode2."'";
	$result_cac = mysql_query($query_cac) or die('query_cac failed:'.mysql_error());
	while($row_cac = mysql_fetch_object($result_cac)){
		$pay_currency = $row_cac->currency;
		$coupon		  = $row_cac->coupon;
	}
	// $coupon_count = 0;
	// $query_coupon = "select count(1) as coupon_count from weixin_commonshop_order_coupons where isvalid=true and batchcode='".$pay_batchcode2."'";
	// $result_coupon = mysql_query($query_coupon) or die('query_coupon failed:'.mysql_error());
	// while($row_coupon = mysql_fetch_object($result_coupon)){
		// $coupon_count = $row_coupon->coupon_count;
	// }
	$cardDiscount = 0;	//会员卡优惠  0:没有使用，1有使用
	$query_discount = "select cardDiscount from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."'";
	$result_discount = mysql_query($query_discount) or die('query_discount failed:'.mysql_error());
	while($row_discount = mysql_fetch_object($result_discount)){
		$cardDiscount = $row_discount->cardDiscount;
	}
	/*** 下单时是否使用了代金券、购物币和会员卡折扣 end***/
	
}else{
	$batchcode 		 = '';
	$paystatus 		 = -1;
	$paystyle  		 = '';
	$paytime   		 = '';
	$status    		 = 0;
	$sendstatus 	 = 0;
	$aftersale_state = 0;
	$is_QR 			 = 0;
	$query_batchcode = "select status,batchcode,paystatus,paystyle,paytime,sendstatus,aftersale_state,is_QR from weixin_commonshop_orders where isvalid=true and pay_batchcode = '".$pay_batchcode."' limit 0,1";
	$result_batchcode = mysql_query($query_batchcode) or die('query_batchcode failed'.mysql_error());
	while($row_batchcode = mysql_fetch_object($result_batchcode)){
		$batchcode 		 = $row_batchcode->batchcode;
		$paystatus 		 = $row_batchcode->paystatus;
		$paystyle  		 = $row_batchcode->paystyle;
		$paytime   		 = $row_batchcode->paytime;
		$status    		 = $row_batchcode->status;
		$sendstatus 	 = $row_batchcode->sendstatus;
		$aftersale_state = $row_batchcode->aftersale_state;
		$is_QR 			 = $row_batchcode->is_QR;
	}
	$query_payother = "select id,pay_user_id,pay_username,note from weixin_commonshop_otherpay_descs where isvalid=true and user_id=".$user_id." and batchcode='".$pay_batchcode."'";
	$result_payother = mysql_query($query_payother) or die('query_payother failed'.mysql_error());
	while($row_payother = mysql_fetch_object($result_payother)){
		$payother_desc_id = $row_payother->id;
		$pay_user_id 	  = $row_payother->pay_user_id;
		$pay_username 	  = $row_payother->pay_username;
		$note 	  		  = $row_payother->note;
	}
}
$currtime 	   = time();	//当前时间
$recovery_time = '';		//支付失效时间
$query_time = "select recovery_time from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."' limit 1";
$result_time = mysql_query($query_time) or die('Query_time failed:'.mysql_error());
while($row_time = mysql_fetch_object($result_time)){
	$recovery_time = $row_time->recovery_time;
}

	//查询商家绑定的会员卡------star
	$shop_card_id = -1;
	$query = "SELECT shop_card_id FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." limit 1";
	$result= mysql_query($query);
	while($row=mysql_fetch_object($result)){
		$shop_card_id = $row->shop_card_id;    //--------先查出商家现在绑定的是哪张会员卡
	}
	if($shop_card_id>0){
		$card_member_id = -1;
		$remain_score  = 0 ; //个人积分余额
		$query = "SELECT id FROM weixin_card_members WHERE isvalid=true AND user_id=".$user_id." AND card_id=".$shop_card_id." LIMIT 1";
		$result= mysql_query($query);
		while($row=mysql_fetch_object($result)){
			$card_member_id = $row->id;						//----------根据商家绑定会员卡id跟user_id查出会员卡id
			if($card_member_id>0){
				$query = "SELECT remain_score FROM weixin_card_member_scores where isvalid=true AND card_member_id=".$card_member_id." LIMIT 1";
				$result= mysql_query($query);
				while($row2=mysql_fetch_object($result)){
					$remain_score = round($row2->remain_score,2);		//---------再拿会员卡id查出积分余额
				}
			}
		}
	}
	
	//查询商家绑定的会员卡------end
	//查询订单需要积分-----start
	$needScore = 0;
	$sql_score = "select needScore from weixin_commonshop_order_prices where isvalid=true and batchcode='" . $batchcode . "'";
	$result_score = mysql_query($sql_score) or die('sql_score failed:'.mysql_error());
	while($row_score = mysql_fetch_object($result_score)){
		$needScore = round($row_score->needScore,2);
	}
	//查询订单需要积分-----end
?>
<!DOCTYPE html>
<html>
<head>
    <title>订单详情</title>
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
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
    <script type="text/javascript" src="./js/jquery.zclip.min.js"></script>    
    <link type="text/css" rel="stylesheet" href="./assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" />   
	<link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />   
</head>

<link rel="stylesheet" href="./css/order_css/style.css" type="text/css" media="all">
<link type="text/css" rel="stylesheet" href="./css/order_css/dingdan.css" />
<link type="text/css" rel="stylesheet" href="./css/order_css/dingdan_detail.css"/>
<style>
.recovery_time{
	color: #fff;
}
.wait_tip{
	float: left;
}
.left_time_top{
    font-size: 16px;
	margin-top: 15px;
    margin-left: 25px;
}
.left_time_bottom{
	font-size: 13px;
    margin-left: 25px;
    margin-bottom: 15px;
}
.order_close{
	float: left;
    height: 90px;
    line-height: 90px;
	margin-left: 40px;
	font-size: 16px;
}
.recovery_time_img{
	float: right;
    margin-right: 30px;
	height: 90px;
	line-height: 90px;
}
.recovery_time_img img{
	vertical-align: middle;
}

.sk-fading-circle {
	  width: 40px;
	  height: 40px;
	  position: fixed; 
	  top:0;
	  left:50%;
	 top:50%;
	 margin-left:-20px;
	 margin-top:-20px
  }

//ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }

</style>
<body class="mainBody" data-ctrl=true>
<!-- 	<header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav" onclick="history.go(-1)">
			<img class="am-header-icon-custom icon_back" src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="am-header-title topTitle">订单详情</h1>
	</header>
    <div class="topDiv"></div> --><!-- 暂时隐藏头部导航栏 -->
	
	<!-- 基本地区-开始 -->
	<div class="mainArea">
		<div class="entry-content">
			
			<!-- 订单状态 未支付订单不显示订单状态-->
			<?php if($status>=0 and $paystatus == 0 and $paystyle!="货到付款" and $sendstatus==0 and $aftersale_state==0){?>
			<div class="divOrderState" style="background-color: #fd7d23;padding:0;">
				<div class="recovery_time">
					<?php if(strtotime($recovery_time)>$currtime){?>
					<div class="wait_tip">
						<div class="left_time_top">等待买家付款</div>
						<div class="left_time_bottom"><span class="times"></span>后支付失效</div>
					</div>
					<div class="order_close" style="display:none;">订单已失效</div>
					<div class="recovery_time_img">
						<img class="left_time_img" src=".\images\order_image\recovery_time_pay.png">
						<img class="order_close_img" src=".\images\order_image\recovery_time_close.png" style="display:none;">
					</div>
					<?php }else{?>
					<div class="order_close">订单已失效</div>
					<div class="recovery_time_img">
						<img class="order_close_img" src=".\images\order_image\recovery_time_close.png">
					</div>
					<?php }?>
				</div>
				<div style="clear:both;height:0;"></div> 
			<?php }else{?>
			<div class="divOrderState">
				<div class="orderState">订单状态</div>
				<div class="line_gray"></div>
				<div id="middle-tab">
					<div class="area-one comment-mark sel ">
						<img class="btn_round_status" src="./<?php echo $images_skin?>/order_image/icon_check_orange.png"> 
						<div>已提交</div>
					</div>
					<?php
							$check = 1;
                            $status_str = '';
                            if($status == -1){
                                $status_str = '待付款';
                            }

							if($status>=0 and ($paystatus == 0 and $paystyle!="货到付款") and $sendstatus==0 and $aftersale_state==0){
								$check = 1;
								$status_str = '待付款';
							}else if(($paystatus==1 or $paystyle=="货到付款") && $status>=0 && $sendstatus==0 && $aftersale_state==0){
								$check = 1;
								$status_str = '待发货';
							}else if($paystatus==1 && $status >= 0 && $sendstatus == 1 && $aftersale_state==0){
								$check = 1;
								$status_str = '待收货';
							}else if($status >= 0 && $sendstatus == 2 && $aftersale_state==0){
								$check = 1;
								$status_str = '已收货';
							}else if($aftersale_state > 0 ){
                                $check = 1;
                                $status_str = '售后中';
                            }else if($sendstatus >=3 and $sendstatus<5){
                                $check = 1;
                                $status_str = '退货中';
                            }else if($sendstatus>=5){
								$check = 1;
                                $status_str = '退款中';
							}

						
						if(!empty($pay_batchcode)){
							if($paystatus==1 or $paystyle=="货到付款"){
								$check = 1;
								$status_str = '待发货';
								if($sendstatus==2){
									$status_str = '已收货';
								}
							}else{
								$check = 1;
								$status_str = '待付款';
							}
						}
					?>
					<div class="area-one comment-mark <?php if(1==$check){echo 'sel';}?>">
						<div class="lineGray"></div>
						<?php if(1==$check){?>
						<img class="btn_round_status" src="./<?php echo $images_skin?>/order_image/icon_check_orange.png"> 
						<?php }else{?>
						<img class="btn_round_status" src="./images/order_image/icon_time_gray.png"> 
						<?php }?>
						<div><?php echo $status_str;?></div>
					</div>
					<?php
						//if(1 == $check && (($as_id>0 && $status==7) || ($as_id<0 && $status==1 && $sendstatus==2))){
                        if($check == 1 && (($sendstatus==2 && $status==1) || $sendstatus==4 || $sendstatus==6 || $aftersale_state==4 || $return_status==4)){
					?>
					<div class="area-one comment-mark sel">
						<div class="lineGray"></div>
						<img class="btn_round_status" src="./<?php echo $images_skin?>/order_image/icon_check_orange.png"> 
						<div>已完成</div>
					</div>
					<?php
						}else if($status == -1){
					?>
					<div class="area-one comment-mark sel">
						<div class="lineGray"></div>
						<img class="btn_round_status" src="./<?php echo $images_skin?>/order_image/icon_check_orange.png"> 
						<div>已取消</div>
					</div>
					<?php
						}else{
					?>
					<div class="area-one comment-mark">
						<div class="lineGray"></div>
						<img class="btn_round_status" src="./images/order_image/icon_time_gray.png"> 
						<div>待完成</div>
					</div>
					<?php
						}
					?>
				</div>
				<?php }?>
			</div>
			<?php
				$name = '佚名';
				$query2 = "select address,name,phone,location_p,location_c,location_a from weixin_commonshop_order_addresses where batchcode='".$batchcode."'";
				// echo $query2;
				$result2 = mysql_query($query2) or die('query failed2'.mysql_error());
				while($row2 = mysql_fetch_object($result2)){
					$address 	= $row2->address;		//详细地址
					$name 		= $row2->name;			//收货人姓名
					$phone 		= $row2->phone;			//收货人联系电话
					$location_p = $row2->location_p;	//省份
					$location_c = $row2->location_c;	//市区
					$location_a = $row2->location_a;	//街道/镇区
				}
			?>
			<!-- 收货人信息 -->
			<div class="div_receiver">
				<div class="div_pos">
					<img src="./images/order_image/icon_position.png">    
				</div>
				<div class="div_right">
					<div class="frame_top">
						<span class="name">收货人&nbsp;:&nbsp;</span>
						<span class="name" style="width:32%;text-overflow:ellipsis; white-space:nowrap;"><?php echo $name;?></span>
						<span class="phone_right"><?php echo $phone;?></span>
					</div>
					<div class="frame_bottom">
						<span>地址&nbsp;:&nbsp;</span><span><?php echo $location_p.$location_c.$location_a.$address;?></span>
					</div>
				</div>
				<div style="clear:both;"></div>
			</div>
			<?php if(!empty($pay_batchcode)){?>
			<div style="height:10px;background-color:#eee;"></div>
			<?php
			}
			if(!empty($pay_batchcode)){
				$query_batchcode = "select batchcode,supply_id,createtime from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and pay_batchcode='".$pay_batchcode."' group by batchcode";
			}else{
				$query_batchcode = "select batchcode,supply_id,createtime from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and batchcode='".$batchcode."' group by batchcode";
			}

				$supply_id	= -1;	//供应商ID
				$createtime	= '';	//创建时间
				$result_batchcode = mysql_query($query_batchcode) or die('query_batchcode faile：'.mysql_error());
				while($row_batchcode = mysql_fetch_object($result_batchcode)){

					$supply_id  = $row_batchcode->supply_id;
					$createtime = $row_batchcode->createtime;
					$batchcode  = $row_batchcode->batchcode;
					
					$shop_show_name  = ""; //显示的商城名
					$brand_supply_id = -1; //品牌供应商ID
					if($supply_id>0){
                        $sql_supplyname = "select id,brand_supply_name from weixin_commonshop_brand_supplys where isvalid=true and user_id=".$supply_id;
						$result_supply = mysql_query($sql_supplyname) or die('query sql_supplyname failed3' . mysql_error());
						if ($row_supply = mysql_fetch_object($result_supply)) {
							$brand_supply_id = $row_supply->id;                
							$shop_show_name  = $row_supply->brand_supply_name;                //店铺名
						}else{
							$sql_supplyname = "select shopName from weixin_commonshop_applysupplys where isvalid = true and user_id=" . $supply_id;
							$result_supply = mysql_query($sql_supplyname) or die('query sql_supplyname failed3' . mysql_error());
							if ($row_supply = mysql_fetch_object($result_supply)) {
								$shop_show_name = $row_supply->shopName;                //店铺名
							}
						}
					}else{
						//查询商城名
						$sql_shopname = "select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
						$result_shop = mysql_query($sql_shopname) or die('query sql_shopname failed'.mysql_error());
						if($row_shop = mysql_fetch_object($result_shop)) {
							$shop_show_name = $row_shop->name;					//商家名
						}
					}
			?>
			<!-- 订单的商品目录信息 -->
			<ul class="ui_order_goods" style="<?php if(!empty($pay_batchcode)){echo 'margin:0;border-top:0;';}?>">
				<div class="shopHead">
					<ul class="am-navbar-nav am-cf am-avg-sm-1" style="z-index:111;">
						<li class="tab_right_top" style="margin:0px;">
							<img class="itemPhotoCheck shopall shopCheck" src="./images/order_image/icon_shop.png">
							<span onclick="<?php if($brand_supply_id>0){echo "gotoShop(".$supply_id.")";}else{echo "gotoIndex()";}?>" class="am-navbar-label"><span class="shopName"><?php echo $shop_show_name;?></span></span>
							<img class="img_shop_right" onclick="<?php if($brand_supply_id>0){echo "gotoShop(".$supply_id.")";}else{echo "gotoIndex()";}?>" src="./images/order_image/btn_right.png">
						</li>
					 </ul>
				</div>
				<?php	
						$query3 = "select pid,rcount,prvalues,totalprice from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and batchcode='".$batchcode."'";
						/*if($as_id>0){
							$query3 .= " and pid=".$as_pid;
						}*/
						$result3 = mysql_query($query3) or die('query failed3'.mysql_error());
						while($row3 = mysql_fetch_object($result3)){
							$pid 			= $row3->pid;				//商品ID
							$rcounts 		= $row3->rcount;			//商品数量
							$prvalues 		= $row3->prvalues;			//商品属性
							$pro_totalprice = $row3->totalprice;
							
							$prvstr = "";
							if(!empty($prvalues)){
								$prvarr= explode("_",$prvalues);						
								for($i=0;$i<count($prvarr);$i++){
									$prvid = $prvarr[$i];
									if($prvid>0){
										$parent_id = -1;
										$prname    = '';
										$query4 = "select name,parent_id from weixin_commonshop_pros where  id=".$prvid;
										$result4 = mysql_query($query4) or die('query failed4'.mysql_error());
										while($row4 = mysql_fetch_object($result4)){
											$parent_id = $row4->parent_id;	//是否子属性
										    $prname    = $row4->name;		//属性名
										}
										$p_prname = '';
										$query5 = "select name from weixin_commonshop_pros where  id=".$parent_id;
										$result5 = mysql_query($query5) or die('query failed5'.mysql_error());
										while($row5 = mysql_fetch_object($result5)){
											$p_prname = $row5->name;		//属性名
											$prvstr   = $prvstr.$p_prname.":".$prname."  ";
										}
									}
								}
							}
							
							$query6 = "select id,name,is_virtual,default_imgurl from weixin_commonshop_products where customer_id=".$customer_id." and id=";
							/*if($as_id>0){
								$query6 .= $as_pid;
							}else{
								$query6 .= $pid;
							}*/

                             $query6 .= $pid;

							$result6 = mysql_query($query6) or die('query failed6'.mysql_error());
							while($row6 = mysql_fetch_object($result6)){
								$product_id 			= $row6->id;				//商品ID
								$product_name 			= $row6->name;				//商品名
								$product_is_virtual 	= $row6->is_virtual;		//是否虚拟产品
								$product_default_imgurl = $row6->default_imgurl;	//商品封面图
							}
				?>
				<!-- 第一个商品 -->
				<li class="itemWrapper item_goods button buttonclick" onclick="gotoProductDetail('<?php echo $pid;?>')">
					<div class="itemMainDiv">
							<img class="itemPhoto" src="<?php echo $product_default_imgurl;?>">      
							<div class="contentLiDiv">            
								<div class="itemProName">
									<span class="goodsName"><?php echo $product_name;?></span>
									<span class="goodsPrice">￥<?php echo number_format($pro_totalprice/$rcounts, 2, '.', '');?></span>
								</div>            
								<span class="itemProContent goodsContent"></span>
								<div class="itemProContent goodsSize"><?php echo $prvstr;?><span>x <?php /*if($as_id>0){echo $rcount;}else{echo $rcounts;}*/ echo $rcounts;?></span></div>
								<?php	
										$as_tip = '';
                                        if( $aftersale_state == 1){
                                            $as_tip = "已申请售后，等待商家确认...";
                                        }else if($aftersale_state == 2){
                                            $as_tip = "商家已同意售后申请，正在处理中...";
                                        }else if($aftersale_state == 3){
                                            $as_tip = "商家已驳回售后申请，原因:".$aftersale_reason;
                                        }else if($aftersale_state == 4){
                                            $as_tip = "售后已处理完成";
                                        }
                                        if($return_status == 2){
                                            if($return_type == 0){
                                                $as_tip = "商家已同意申请，等待退款中...";
                                            }else if($return_type == 1){
                                                $as_tip = "已同意退货申请";
                                            }else if($return_type == 2){
                                                $as_tip = "已同意换货申请";
                                            }

                                        }else if($sendstatus == 3 && $return_status == 0){
                                            if($return_type == 0){
                                                $as_tip = "已申请退货(仅退款),等待商家确认中...";
                                            }else if($return_type == 1){
                                                $as_tip = "已申请退货,等待商家确认中...";
                                            }else if($return_type == 2){
                                                $as_tip = "已申请退货(换货),等待商家确认中...";
                                            }
                                        }else if($return_status == 5){
                                            $as_tip = "已退货，等待商家收货";
                                        }else if($return_status == 6){
                                            $as_tip = "商家已收货";
                                        }else if($return_status == 4){
                                            $as_tip = "已确认退货";
                                        }

                                    if($sendstatus==6){
                                        $as_tip = "已退款完成";
                                    }
                                    if($sendstatus==4){
                                        $as_tip = "已退货完成";
                                    }
										if(!empty($as_tip)){
                                ?>
                                <div class="goodsRedRect" style="float:right;">
								<?php echo $as_tip;?>
                                </div>
								<?php	}?>
							</div>
					</div>
				</li>
				<?php }?>
				<div class="line_white"></div>
				<?php if($sendstatus == 1 && empty($pay_batchcode)){  //已发货?>
				<div style="height:40px;text-align:center;">
				<?php 	if($is_delay == 0){?>
					<span onclick="order_delay('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite3">延时收货</span>
				<?php 	}	?>
					<span onclick="toAftersale('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite3">申请退货</span>
				</div>
				<?php	}
					/*if($as_id>0){
				?>
				<!-- 费用信息 -->
				<div class="itemWrapper itemOrderInfo">
					<span class="text_left_13">退货数量</span>
					<span class="text_right_13">x<?php echo $rcount;?></span>
				</div>							
				<div class="itemOrderMoney">
					<span class="itemLeft">退款金额</span>
					<span class="itemRight">￥<?php if($confirm_account>0){echo $confirm_account;}else{echo $account;}?></span>
				</div>	
				<?php
					}else{*/
						$totalprice   = 0;
						$ExpressPrice = 0;
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
								$ExpressPrice  = $row_price->ExpressPrice;	//运费
							}
						}
				?>				
				<div class="horizLineGray"></div>
				<div class="itemWrapper itemOrderInfo">
					<span class="text_left_13">运费</span>
					<span class="text_right_13">￥<?php if($ExpressPrice>0){echo $ExpressPrice;}else{echo '0';}?></span>
				</div>							
				<div class="itemOrderMoney">
					<span class="itemLeft">实付款</span>
					<span class="itemRight">￥<?php echo $totalprice;?></span>
				</div>							
				<div class="horizLineGray"></div>
				<?php }?>
			</ul>
			<?php	//	}
					if($status == 0 and $paystatus == 0 and $paystyle != "货到付款" and $sendstatus == 0 and empty($pay_batchcode) and $isOpenCurrency == 1 and strtotime($recovery_time)>$currtime){
			?>
			<!--购物币-->
			<div id="currency_div" class="itembutton" style="margin-top: -24px;margin-bottom: 30px;">
			  <div class="top">
				<span>使用<?php echo $custom;?></span> (可用：<span style="color:red;display: inline;padding: 0px;margin: 0px"><?php echo $user_curr;?></span></span>) 
				<input type="checkbox" id="checkbox_c1" class="chk_3" >
				<label for="checkbox_c1" open_val="0" class="open_curr">
					  <div class="slide_body"></div>
					  <div class="slide_block"></div>          
				</label>
			  </div>
			  <div class="currency" style="display:none">
				  <div class="line"></div>
				  <div class="bottom">  
					<input class="user_currency" type="number" max="<?php echo $user_curr;?>" placeholder="请输入抵用<?php echo $custom;?>数量">
				  </div>
			  </div>
			</div>
			<!--购物币-->
			<?php	}?>
			<!-- 订单编号，各种时间信息 -->
			<div class="infoWrapper" style="padding-bottom:10px;<?php if(!empty($pay_batchcode)){echo 'border-bottom:0;margin-top:0;';}?>">
				<span class="text_gray_13"><?php if(!empty($pay_batchcode)){echo '支付';}?>订单编号：<span id="batchcode"><?php if(!empty($pay_batchcode)){echo $pay_batchcode;}else{echo $batchcode;}?></span></span>
					<div id="copy_btn" class="button buttonclick" data-clipboard-action="copy" data-clipboard-target="#batchcode">复制</div>
					<?php if(!empty($allipay_orderid)){?>
				<span class="content-line">支付宝交易号：<?php echo $allipay_orderid;?></span>
					<?php 
						}
						if(strtotime($as_createtime)>0){
					?>
				<span class="content-line">申请售后时间：<?php echo $as_createtime;?></span>
					<?php
						}else if(strtotime($createtime)>0){
					?>
				<span class="content-line">创建时间：<?php echo $createtime;?></span>
				<?php	} 
					if($status == 0 and $paystatus == 0 and $paystyle != "货到付款" and $sendstatus == 0){
				?>
				<span class="content-line">支付失效时间：<?php echo $recovery_time;?></span>
				<?php
					}
					if(strtotime($paytime) > 0){
				?>
				<span class="content-line">付款时间：<?php echo $paytime;?></span>
				<?php 
					}
					if(strtotime($confirm_sendtime) > 0){
				?>
				<span class="content-line">发货时间：<?php echo $confirm_sendtime;?></span>
				<?php 
					}
					if(strtotime($confirm_receivetime) > 0){
				?>
				<span class="content-line">成交时间：<?php echo $confirm_receivetime;?></span>
				<?php 
					}
					if(strtotime($checktime) > 0){
				?>
				<span class="content-line">商家处理时间：<?php echo $checktime;?></span>
				<?php 
					}
                    if($sendstatus == 1){
                        ?>
                        <span class="content-line">自动收货时间：<?php echo $auto_receivetime;?></span>
                <?php
                    }
				?>
				<?php 
                    if( $pay_user_id > 0 ){
                        ?>
                        <span class="content-line">代付人：<?php echo $pay_username;?></span>
                        <span class="content-line">代付留言：<?php echo $note;?></span>
                <?php
                    }
				?>
			</div>
            <!-- 二维码核销 -->
            <?php if($is_QR && $paystatus == 1){
                $qr_img = "";
                $encrypcode = "";
                $query_qr = "select qr,encrypcode from weixin_commonshop_order_qr where batchcode = '".$batchcode."'";
                $result_qr = mysql_query($query_qr) or die("query_qr Query_qr error : ".mysql_error());
                $qr_img = mysql_result($result_qr,0,0);
                $encrypcode = mysql_result($result_qr,0,1);
            ?>
            <a href="../common_shop/jiushop/qr_deliver.php?customer_id=<?php echo $customer_id_en; ?>&batchcode=<?php echo $batchcode;?>&user_id=<?php echo passport_encrypt($user_id);?>&type=1">
                <img class="tpl-stuff-img" style="margin:0 auto;display:block;" src="<?php echo $new_baseurl."../".$qr_img; ?>"></a>
            <div class="detail">
                <div class="mall-order-detail-stuff-list-item-con-name black-font blod-font tpl-stuff-name" style="padding-top: 12px;"><?php if($status==0){echo '<a style="color:#20941e;">(未兑换)</a>';}else if($status==1){echo '<a style="color:#f30022;">(已兑换)</a>';}else{echo '<a style="color:#5a5a5a;">(已取消)</a>';} ?>兑换码:<?php echo $encrypcode; ?></div>
            </div>
            <?php }?>
			<!-- 留言，回复信息 -->
			<?php
				if(!empty($remark) && empty($pay_batchcode)){
			?>
			<div class="comment-frame">
				<span class="content-line2" style="color:red;">买家留言:</span>
				<span class="content-line2"><?php echo $remark;?></span>
			</div>
			<?php
					if(!empty($merchant_remark)){
			?>
			<div class="comment-frame">
				<span class="content-line2" style="color:red;">商家回复:</span>
				<span class="content-line2"><?php echo $merchant_remark;?></span>
			</div>
			<?php
					}
				}
			?>
		</div>
	</div>
	<!-- 基本地区-终结 -->
	<div class="copy_tip">
		<span>已复制</span>
	</div>
	<!-- 下面的按钮地区 - 开始 -->
	<?php if(empty($pay_batchcode)){?>
	<div class="white-list">
		<div style="width:100%;">
			<ul class="am-navbar-nav am-cf am-avg-sm-1">
				<li class="tab_right_top" style="margin:0px;">
					<?php
					$hour=floor(($currtime-strtotime($paytime))/3600);
                    ?>
                    <?php
                    if($return_status == 2 && ($return_type == 1 || $return_type == 2)) { //同意退货后

                        ?>
                        <span onclick="order_return('<?php echo $batchcode;?>')"
                              class="am-navbar-label btnWhite4" style="width:auto;">填写退货单</span>
                    <?php
                    }
					//
					if($status == 0 && ($paystatus == 1 || $paystyle=="货到付款") && $sendstatus == 0 && $hour >=12){ //未确认，已支付||货到付款，未发货
						 //离支付时间已超过12小时则可以提醒发货
						?>
						<span onclick="orderRemind('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite4" style="width:auto;">提醒发货</span>
					<?php
					}
                    if($status == 0 && $paystatus == 1 && $sendstatus == 0){ ?>
                        <span onclick="toAftersale('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite4" style="width:auto;">申请退款</span>
                     <?php
                    }
					if(($paystatus == 1 or $paystyle=="货到付款") && $sendstatus > 0 && $is_QR == 0){ //已支付||货到付款,不在未发货状态
						?>
						<span onclick="check_express('<?php echo $expressnum;?>')" class="am-navbar-label btnWhite4" style="width:auto;">查看物流</span>
						<?php
						if($sendstatus == 1){  //已发货
						?>
							<span onclick="order_confirm('<?php echo $batchcode;?>','<?php echo $totalprice;?>')" class="am-navbar-label btnWhite2" style="width:auto;">确认收货</span>
                        <?php
						}
					}
					?>
					<?php
					if($sendstatus == 2){ //发货状态为 ： 已收货
                        if($aftersale_state == 0 ) {
                            ?>
                            <span onclick="toAftersale('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite2" style="width:auto;">申请售后</span>
                        <?php
                        }
                            if($is_discuss == 0 && $aftersale_state == 0){  //未评价
							?>
							<span onclick="toEvaluation('<?php echo $batchcode;?>');" class="am-navbar-label btnWhite2" style="width:76px;text-align:center;">评价</span>
						<?php }else if($is_discuss == 1 && $aftersale_state == 0 ){ //已评 ?>
							<span onclick="toEvaluation('<?php echo $batchcode;?>');" class="am-navbar-label btnWhite2" style="width:auto;">追加评价</span>
						<?php }?>
					<?php
					}
					?>
					<?php
					if($status == 0 and $paystatus == 0 and $sendstatus == 0){ //未确认，未付款状态
						?>
						<span onclick="order_cancel('<?php echo $batchcode;?>');" class="am-navbar-label btnWhite4" style="width:auto;">取消订单</span>
						<?php
							
							if($paystyle != "货到付款" and strtotime($recovery_time)>$currtime){ //货到付款的不需要支付按钮
                            // 测试
                             // if($paystyle != "货到付款"){ //货到付款的不需要支付按钮
								if($is_payother && $pay_currency==0 && $coupon==0 && $cardDiscount==0 && $remain_score>=$needScore){	//是否开启代付
						?>
							<span id="payother_on" onclick="payother('<?php echo $batchcode;?>','<?php echo $payother_desc_id;?>')" class="am-navbar-label btnWhite2" style="width:auto;">找人代付</span>
							<span id="payother_off" class="am-navbar-label btnWhite2" style="display:none;color:grey;border:1px solid grey;width:auto;">找人代付</span>
						<?php }?>

							<span id="topay" name="topay" onclick="order_pay('<?php echo $batchcode;?>','<?php echo $totalprice;?>')" class="am-navbar-label btnWhite2">付款</span>
						<?php
						}
					}
					?>
				</li>
			 </ul>
		</div>
    </div>
	<div class="pay_desc" style="display:none;">
		<span style="font-size:16px;display:block;margin-top:10px;">对你的好友说：</span>
		<textarea class="pay_desc_text" rows="6" cols="32" style="margin-top:10px;resize:none;" placeholder="蛋蛋的忧伤，钱不够了，你能不能帮我先垫付下"></textarea>
		<div class="pay_desc_btn">确定</div>
	</div>
	<div class="shadow" style="display:none;"></div>
	<!-- 支付方式 begin -->
    <div class=" popup-memu" id = "zhifuPannel" style="display: none">
        <div class="list-one popup-menu-title">
            <span class="sub">选择支付方式</span>
        </div>
		<?php if($from_type == 1){
				if( $is_weipay ) {		//目前支持微信端的微信支付，app微信支付暂不支持?>
        <div class="line"></div>
        <div class = "popup-menu-row" data-value="微信支付">
            <img src="images/np-1.png">
            <span class="font">微信支付</span>
        </div>
		<?php 	}
			}else{
				if( $is_alipay ) {?>
        <div class="line"></div>
        <div class = "popup-menu-row" data-value="支付宝支付">
            <img src="images/np-4.png">
            <span class="font">支付宝支付</span>
        </div>
		<?php 	}
			}
		?>		
		<?php if( $iscard && $card_member_id>0 ) {?>
        <!--<div class="line"></div>
        <div class = "popup-menu-row" data-value="会员卡余额支付">
            <img src="images/np-2.png">
            <span class="font">会员卡余额支付</span>
        </div>-->
		<?php }?>
		<?php if($is_payChange){ ?>
        <!--<div class="line"></div>
        <div class = "popup-menu-row" data-value="零钱支付">
            <img src="images/np-3.png">
            <span class="font">钱包零钱支付</span>
        </div>-->
		<?php } ?>
        <!--<?php if( $is_allinpay ) {?> 
		<div class="line"></div>
        <div class = "popup-menu-row" data-value="通联支付">
           <img src="images/np-8.png">
           <span class="font">通联支付</span>
       </div>
	   <?php };?>	
	   <?php if( $isshop ) {?> 
	   <div class="line"></div>
       <div class = "popup-menu-row" data-value="货到付款">
            <img src="images/np-5.png">
           <span class="font">货到付款</span>
       </div>
	   <?php };?>	
	   <?php if( $is_alipay ) {?>
       <div class="line"></div>
       <div class = "popup-menu-row" data-value="支付宝支付">
            <img src="images/np-4.png">
           <span class="font">支付宝支付</span>
       </div>
	   <?php };?>
      <div class="line"></div>
       <div class = "popup-menu-row" data-value="找人代付">
            <img src="images/np-6.png">
           <span class="font">找人代付<span class = "font-small">(指定一位好友帮忙支付)</span></span>
       </div>
	   <?php if( $is_paypal ) {?>
       <div class="line"></div>
        <div class = "popup-menu-row" data-value="PayPal支付">
            <img src="images/np-7.png">
           <span class="font">PayPal</span>
       </div>
	   <?php };?>-->
    </div>
    <!-- 支付方式 end -->
	<div class="am-dimmer am-active" data-am-dimmer="" style="display: none;"></div>
	<?php }?>
	<!-- 下面的按钮地区 - 终结 -->
	<?php if(empty($pay_batchcode)){?>
	<div style="height:60px;"></div><!--底部高度-->
	<?php }?>
</body>		
<script type="text/javascript">
var customer_id_en = '<?php echo $customer_id_en;?>';
var user_id		   = '<?php echo $user_id;?>';
var user_id_en	   = '<?php echo passport_encrypt($user_id);?>';
var batchcode	   = '<?php echo $batchcode;?>';
var check		   = true;
var custom		   = '<?php echo $custom;?>';
var recovery_time  = '<?php echo strtotime($recovery_time);?>';
var currtime	   = '<?php echo $currtime;?>';
var paystatus	   = '<?php echo $paystatus;?>';
var paystyle	   = '<?php echo $paystyle;?>';
var needScore	   = '<?php echo $needScore;?>';
var remain_score   = '<?php echo $remain_score;?>';
	//退货填写退货单
    function order_return(batchcode){
        location.href='orderlist_return.php?customer_id='+customer_id_en+"&batchcode="+batchcode+"&user_id="+user_id_en;
    }
	//付款
	function order_pay(batchcode,totalprice){
		if(parseFloat(needScore) > parseFloat(remain_score)){
			showAlertMsg("操作提示","您的积分不够！","知道了");
			return;
		}
		if(check){
			check = false;
			var iptCurrency = $(".user_currency").val();
			var open_curr = $(".open_curr").attr('open_val');
			if(iptCurrency != "" && open_curr == 1){
				if(parseFloat(iptCurrency) > parseFloat(totalprice)){
					showAlertMsg("操作提示","最多只能使用"+totalprice+"个"+custom,"知道了");
					check = true;
					return;
				}
				if(parseFloat(iptCurrency) == parseFloat(totalprice)){ //全部使用购物币支付
					showConfirmMsg("操作提示","是否确定全部使用"+custom+"支付？","支付","取消",function(){
						check = false;
						$.ajax({
							type: "get",
							url: "orderlist_operation.php",
							dataType: 'json',
							data: "op=pay_currency&batchcode="+batchcode+"&customer_id="+customer_id_en,
							success: function(data){
								showAlertMsg("操作提示",data.msg,"知道了",function(){
									if(data.result == 1) {
										location.href="orderlist.php?customer_id="+customer_id_en+"&currtype=3&user_id="+user_id_en;
									}else{
										//location.href="orderlist.php?customer_id="+customer_id_en+"&currtype=2&user_id="+user_id_en;
									}
								});
							}
						});
						return;
					});
					check = true;
					return;
				}
			}
			check = true;
			togglePan();
		}
    }
	
	$('.popup-menu-row').click(function(){
		var pay_status = true;
		var pay_type = $(this).data("value");
		var currency = $(".user_currency").val();
		var open_curr = $(".open_curr").attr('open_val');
		/*混合支付生成购物币订单*/
		if( pay_type != '找人代付' && currency > 0 && open_curr == 1 ){
			pay_status = false;
			$.ajax({
				type: "get",
				url: "orderlist_operation.php",
				async: false,
				dataType: 'json',
				data: "op=order_currency&batchcode="+batchcode+"&currency="+currency+"&customer_id="+customer_id_en,
				success: function(data){					
					if( data.status > 1 ){
						showAlertMsg("操作提示",data.msg,"知道了");
						return;
					}
					pay_status = true;
				}
			});
		}
		if(pay_status){
			if( pay_type =='微信支付'){
				var	url="./WeChatPay/weipay_single.php?order_id="+batchcode;
			}else if(pay_type == '找人代付'){
			 // var payother_desc_id = result.payother_desc_id;
			 // var url = "payother.php?payother_desc_id="+payother_desc_id+"&customer_id="+customer_id_en;
			}else if(pay_type == '支付宝支付'){
				var	url="./alipaytest/alipayapi.php?order_id="+batchcode+'&customer_id=<?php echo $customer_id;?>';
			}
			location.href=url;
		}
	});
	
	//跳转到供应商页面
	function gotoShop(shopID){
		window.location.href = "my_store/my_store.php?supplier_id="+shopID+"&customer_id="+customer_id_en;
	}
	
	//跳转到首页
	function gotoIndex(){
		window.location.href = "../common_shop/jiushop/index.php?customer_id="+customer_id_en;
	}
	
	//跳转到产品详情页
	function gotoProductDetail(pid){
		window.location.href = "product_detail.php?pid="+pid+"&customer_id="+customer_id_en;
	}
	
	//收回选择支付div和蒙版
	function togglePan(){
		$(".am-dimmer").toggle();
		$("#zhifuPannel").slideToggle();
	}
	$('.am-dimmer.am-active').click(function(){togglePan();});

	//点击【提醒发货】
	function orderRemind(batchcode){
		if(check){
			check = false;
			$.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"remind",user_id:user_id_en,customer_id:customer_id_en},function(data){
				showAlertMsg ("提示：",data.msg,"知道了");
				check = true;
			});
		}
    }
	
	//申请延时收货
    function order_delay(batchcode){
        showConfirmMsg("操作提示","只能延迟一次，是否确定申请延迟收货？","申请","取消",function(){
            $.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"delay"},function(data){
                showAlertMsg ("提示：",data.msg,"知道了",function(){
					location.reload();
				});
            });
        });
    }
	
	//链接到评价页
	function toEvaluation(batchcode){
		window.location.href = "orderlist_evaluation.php?batchcode="+batchcode+"&customer_id="+customer_id_en+"&user_id="+user_id_en;
	}
	
	//申请售后
	function toAftersale(batchcode){
      //location.href='orderlist_aftersale.php?batchcode='+batchcode+"&pid="+pid+"&customer_id="+customer_id_en+"&prvalues="+prvalues;
		location.href='orderlist_aftersale.php?batchcode='+batchcode+"&customer_id="+customer_id_en+"&user_id="+user_id_en;
    }
	
	//取消订单
    function order_cancel(batchcode){
		if(check){
			showConfirmMsg("提示：","取消后不可恢复，是否确认取消订单？","取消","不取消",function(){
				$.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"cancel"},function(data){
					showAlertMsg ("提示：",data.msg,"知道了",function(){
						location.reload();
					});
				});
			});
		}
    }
    //确认收货
    function order_confirm(batchcode,totalprice){
		showConfirmMsg("提示：","警：确认完成后，订单将进行结算，订单不再受理退货，退款，如若确定商品无误，请点击确认，否则取消。","确认","取消",function(){
			$.getJSON("orderlist_operation.php",{batchcode:batchcode,totalprice:totalprice,op:"confirm"},function(data){
                showAlertMsg ("提示：",data.msg,"知道了",function(){
					location.reload();
				});
            });
		});
    }
    //点击【查看物流】
    function check_express(expressNum){
        //window.location.href = "http://m.kuaidi100.com/index_all.html?type="+expressNum+"&postid="+expressNum+"#result";
        window.location.href = " http://m.kuaidi100.com/result.jsp?nu="+expressNum;
    }
	
	//找人代付
	function payother(batchcode,payother_desc_id){
		if(check){
			var open_curr = $(".open_curr").attr('open_val');
			if(open_curr == 1){
				showAlertMsg ("提示：","找人代付不能使用"+custom,"知道了");
				return;
			}
			if(payother_desc_id>0){
				window.location.href = "payother.php?customer_id="+customer_id_en+"&payother_desc_id="+payother_desc_id;
			}else{
				$('.pay_desc').show();
				$('.shadow').show();
				$('.pay_desc_btn').click(function(){
					if(check){
						check = false;
						var pay_desc = $('.pay_desc_text').val();
						if(pay_desc == '' || (/^\s+$/g).test(pay_desc)){
							pay_desc = '蛋蛋的忧伤，钱不够了，你能不能帮我先垫付下';
						}
						$.ajax({
							url: 'save_order_payother.php?customer_id='+customer_id_en,
							data:{
								batchcode:batchcode,
								user_id:user_id,
								pay_desc:pay_desc
							},
							dataType: 'json',
							type: 'post',
							success:function(res){
								window.location.href = "payother.php?customer_id="+customer_id_en+"&payother_desc_id="+res+"&batchcode="+batchcode;
								check = true;
							},
							error:function(er){
								check = true;
							}
						});
					}
				})
			}
		}
	}
	
	$('.shadow').click(function(){
		$('.pay_desc').hide();
		$('.shadow').hide();
	})
	
		//打开按钮
	function btn_on(obj){
		//console.log('btn_on');
		//console.log(obj);
		var slide_body = obj.find('.slide_body');
		var slide_block = obj.find('.slide_block');
		
		slide_block.css({
                left:20+"px",
                boxshadow:"0 1px 2px rgba(0,0,0,0.05), inset 0px 1px 3px rgba(0,0,0,0.1)"
        });
		slide_body.css({
                background:"#fd832f",
                boxShadow:"0 0 1px #fd832f"
            });
		obj.attr('open_val',1);
		$('#payother_on').hide();
		$('#payother_off').show();
	}
	
	//点击购物币事件

		$(".open_curr").click(function(){	
			var open_val = $(this).attr('open_val');
			if(open_val ==0 ){
				btn_on($(this));
				$(this).parent().siblings('.currency').show();
			}else{
				btn_off($(this));
				$(this).parent().siblings('.currency').hide();
			}
		});
	
	//关闭按钮
	function btn_off(obj){
		//console.log('btn_off');
		//console.log(obj);
		
		var slide_body = obj.find('.slide_body');
		var slide_block = obj.find('.slide_block');
		slide_block.css({
                left:0,
                boxshadow:"none"
        });
        slide_body.css({
                background:"none",
                boxShadow:"inset 0 0 0 0 #eee, 0 0 1px rgba(0,0,0,0.4)"
        });
		obj.attr('open_val',0);
		$('#payother_on').show();
		$('#payother_off').hide();
	}
	
$(function(){
	if(paystatus==0 && paystyle!='货到付款'){
		if(recovery_time>currtime){
			times(recovery_time);
			timeid = setInterval('times('+recovery_time+')',1000);
		}
	}
})
//支付失效倒计时
function times(recovery_time){	
	timestamp = recovery_time-currtime;	//时间差
	var day = 24 * 60 * 60;
	var hour = 60 * 60;
	var minute = 60;
	var second = 1;
	var days = 0;
	var hours = 0;
	var minutes = 0;
	var seconds = 0;

	if(timestamp >= day){
		days = parseInt(timestamp/day);
		timestamp = timestamp - day * days;
	}
	if(timestamp >= hour){
		hours = parseInt(timestamp/hour);
		timestamp = timestamp - hour * hours;
	}
	if(timestamp >= minute){
		minutes = parseInt(timestamp/minute);
		timestamp = timestamp - minute * minutes;
	}
	if(timestamp >= second){
		seconds = parseInt(timestamp/second);
		timestamp = timestamp - second * seconds;
	}
	var html = '';
	if(days==0 && hours==0 && minutes==0){
		html = seconds+'秒';
	}else if(days==0 && hours==0){
		html = minutes+'分'+seconds+'秒';
	}else if(days==0){
		html = hours+'小时'+minutes+'分'+seconds+'秒';
	}else{
		html = days+'天'+hours+'小时'+minutes+'分'+seconds+'秒';
	}
	$(".times").html(html);		//刷新时间
	if(days==0 && hours==0 && minutes==0 && seconds==0){
		$('.wait_tip').hide();
		$('.left_time_img').hide();
		$('.order_close').show();
		$('.order_close_img').show();
		$('#payother_on').hide();
		$('#payother_off').hide();
		$('#topay').hide();
		$('#currency_div').hide();
		clearInterval(timeid);
		window.location.reload();
	}
	currtime++;
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
<script src="./js/clipboard.min.js"></script>
<script>
//复制(手机微信复制不了)
  
var clipboard = new Clipboard('#copy_btn');

clipboard.on('success', function(e) {
	e.clearSelection();
	 $(".copy_tip").fadeIn(200);
	 setTimeout(function(){ $(".copy_tip").fadeOut(200); },1500);
});

clipboard.on('error', function(e) {
	showAlertMsg ("提示：","长按订单号进行复制","知道了");
});
 


</script>
<!--引入侧边栏 start-->
<?php  include_once('float.php');?>
<!--引入侧边栏 end-->
</body>
</html>