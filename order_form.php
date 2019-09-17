<?php
/****文件说明:
@确认订单有选择门店(自提)，发票，留言，必填信息，购物币，代金卷，会员卡折扣，送货时间，自定义区域
@使用session：立即购买或者购物车数据，送货时间，自定义区域
@使用本地存储：选择门店(自提)，发票，留言，必填信息，购物币，代金卷，会员卡折扣
@使用过session的数据要在保存订单清除session
@使用过本地存储的数据，要在确认订单，购物车，产品详情js清除（order_from.js,order_cart.js,product_detail.js）

*****/
header("Content-type: text/html; charset=utf-8");
// header("Cache-control: private, must-revalidate");
session_cache_limiter( "private, must-revalidate" );
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('order_Form Could not select database');
//头文件  调试请关闭此文件----start
require('../common/common_from.php');
require('select_skin.php');
//头文件  调试请关闭此文件----end

/*测试数据*/
/* $customer_id = 3243 ;
$user_id = 194515; */
/*测试数据*/

require('../common/utility.php');
require('../common/utility_shop.php');
require('../proxy_info.php'); 

//----确认订单方法
require('order_newform_function.php');
//----确认订单方法

//----假如没有post数据和session数据，则跳转到购物车 调试请屏蔽 start
if($_POST['pid'] == '' && $_POST['pro_arr'] =='' && $_SESSION['bug_post_data_'.$user_id]==''){
	echo "<script>location.href='order_cart.php?customer_id=".$customer_id_en."';</script>";
	return ;
}
//----假如没有post数据和session数据，则跳转到购物车 调试请屏蔽 end
$_SESSION['a_type_'.$user_id] = '';		//清空选择地址页面的session

//收货地址ID--start
$aid = -1;
if(!empty($_GET["aid"])){               
  $aid = $configutil->splash_new($_GET["aid"]);      //产品ID
  $_SESSION['aid_'.$user_id] = $aid;							 //存入session，方便下次跳转获取数据
}else{
	$aid = $_SESSION['aid_'.$user_id];				
}
//收货地址ID--end

//单个产品--start
$pid = 0;
if(!empty($_POST["pid"])){               
  $pid = $configutil->splash_new($_POST["pid"]);      			//产品ID
}
$prvalues = '';
if(!empty($_POST["sel_pros"])){               
  $prvalues = $configutil->splash_new($_POST["sel_pros"]);      //所选的属性ID
}
$rcount = 0;
if(!empty($_POST["rcount"])){               
  $rcount = $configutil->splash_new($_POST["rcount"]);     		//数量
}
$supply_id = -1;
if(!empty($_POST["supply_id"])){               
  $supply_id = $configutil->splash_new($_POST["supply_id"]);    //供应商ID
}	

//单个产品--end

//购物车数据-----start

$pro_arr 	= '';
$clean_cart = '';//记录购物车传来的数据，用于清除购物车记录
file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."===>".var_export($_POST['pro_arr'],true)."\r\n",FILE_APPEND);
if(!empty($_POST["pro_arr"])){	
    $pro_arr  	= $_POST["pro_arr"]; 	//不使用防注入     
	$clean_cart	= $pro_arr;
}
$pro_arr = json_decode($pro_arr);


//购物车数据-----start

$fromtype = -1; //1:立即购买 2:购物车购买

if(!empty($_POST["fromtype"])){
    $fromtype      = $configutil->splash_new($_POST["fromtype"]);      
    $_SESSION['fromtype'] = $fromtype;		//第一次存入session，下次直接读session
}else{
	$fromtype = $_SESSION['fromtype'] ;
}


/***************************会员卡折扣可以选择模式与代金券 暂不用*********************************/
/*
//----选择会员卡后返回的card_id和信息（余额、积分、折扣,名称）
$select_card_id = '-1';
$rtn_card_array = '';
if(!empty($_POST["select_card_id"])){	
    $select_card_id   = $configutil->splash_new($_POST["select_card_id"]);      
    $rtn_card_array   = $configutil->splash_new($_POST["rtn_card_array"]);   
	$rtn_card_array   =  explode(',',$rtn_card_array);
	//设置session方便以后跳转读取
	$_SESSION['select_card_id_'.$user_id] = $select_card_id;
	$_SESSION['rtn_card_array_'.$user_id] = $rtn_card_array;
	
   //注销代金券的session
   unset($_SESSION['select_coupon_id_'.$user_id]);
   unset($_SESSION['rtn_coupon_array_'.$user_id]);
}else{
	$select_card_id   		  = $_SESSION['select_card_id_'.$user_id];      
    $rtn_card_array   		  = $_SESSION['rtn_card_array_'.$user_id]; 
}
//var_dump($rtn_card_array);
//----选择会员卡后返回的card_id和信息（余额、积分、折扣

//----选择代金券后返回的id和信息（使用金额限制、优惠金额）
$select_coupon_id = '-1';
$rtn_coupon_array = '';
if(!empty($_POST["select_coupon_id"])){	
    $select_coupon_id   = $configutil->splash_new($_POST["select_coupon_id"]);      
    $rtn_coupon_array   = $configutil->splash_new($_POST["rtn_coupon_array"]);   
	$rtn_coupon_array   =  explode(',',$rtn_coupon_array);
	//设置session方便以后跳转读取
	$_SESSION['select_coupon_id_'.$user_id] = $select_coupon_id;
	$_SESSION['rtn_coupon_array_'.$user_id] = $rtn_coupon_array;
	//var_dump($rtn_coupon_array);
	
	//注销会员卡的的session
    unset($_SESSION['select_card_id_'.$user_id]);
    unset($_SESSION['rtn_card_array_'.$user_id]);
	$select_card_id   		      = $_SESSION['select_card_id_'.$user_id];  		//重新赋值，解决选择代金卷后也会现在会员卡	    
    $rtn_card_array   		  	  = $_SESSION['rtn_card_array_'.$user_id];			//重新赋值，解决选择代金卷后也会现在会员卡	
}else{
	$select_coupon_id   		  = $_SESSION['select_coupon_id_'.$user_id];      
    $rtn_coupon_array   		  = $_SESSION['rtn_coupon_array_'.$user_id]; 
	 
}
//----选择代金券后返回的id和信息（使用金额限制、优惠金额）
*/
/***************************会员卡折扣可以选择模式与代金券 暂不用*********************************/


//----选择送货时间返回的id和信息（id、送货时间详情）
$sendtime_id = '-1';
$rtn_sendtime_array = '';
if(!empty($_POST["sendtime_id"])){	
    $sendtime_id   		  = $configutil->splash_new($_POST["sendtime_id"]);      
    $rtn_sendtime_array   = $configutil->splash_new($_POST["rtn_sendtime_array"]);   
	$rtn_sendtime_array   =  explode(',',$rtn_sendtime_array);
	//设置session方便以后跳转读取，会在保存订单页面清除
	$_SESSION['sendtime_id_'.$user_id] = $sendtime_id;
	$_SESSION['rtn_sendtime_array_'.$user_id] = $rtn_sendtime_array;
	//var_dump($rtn_sendtime_array);
   
}else{
	$sendtime_id   		  = $_SESSION['sendtime_id_'.$user_id];      
    $rtn_sendtime_array   = $_SESSION['rtn_sendtime_array_'.$user_id]; 
}
//----选择送货时间返回的id和信息（id、送货时间详情）


//----选择自定义区域返回的id和信息（id、送货时间详情）
$diy_area_id = -1;
$rtn_diy_area_array = '';
if(!empty($_POST["diy_area_id"])){	
    $diy_area_id   		  = $configutil->splash_new($_POST["diy_area_id"]);      
    $rtn_diy_area_array   = $configutil->splash_new($_POST["rtn_diy_area_array"]);   
	$rtn_diy_area_array   =  explode(',',$rtn_diy_area_array);
	//设置session方便以后跳转读取，会在保存订单页面清除
	$_SESSION['diy_area_id_'.$user_id] = $diy_area_id;
	$_SESSION['rtn_diy_area_array_'.$user_id] = $rtn_diy_area_array;
	//var_dump($rtn_diy_area_array);
   
}else{
	$diy_area_id   		  = $_SESSION['diy_area_id_'.$user_id];      
    $rtn_diy_area_array   = $_SESSION['rtn_diy_area_array_'.$user_id]; 
}
//----选择自定义区域返回的id和信息（id、送货时间详情）


/***************************重组数据 start******************************/
/*
POST过来的数据结构说明：
例子：
购物车
{
	"-1":[["1510","1253_1413","10"],
	"191155":[["1510","1253_1413","10"],
	"191566":[["1510","1253_1413","10"],
	"-1":[["1510","1253_1413","10"]
]}
供应商ID1:[产品ID,所选属性,数量]
供应商ID2:[产品ID,所选属性,数量]
供应商ID3:[产品ID,所选属性,数量]

单品购买
["1510","1253_1413","10","124","198119"]--[产品ID,所选属性,fromtype,数量,供应商ID]

*/


 if($fromtype ==1 ){		//立即购买
	 $buy_now = array($supply_id, array($pid,$prvalues,$rcount));//转成购物车数组结构，方便操作--  供应商ID:[产品ID,所选属性,数量]	
	 $bug_post_data = $buy_now;								//需要添加邮费的数据
		//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==1==>".var_export($buy_now,true)."\r\n",FILE_APPEND);
	//-----------当post数据没有就读取session
	if($_POST['pid']==''){
			$bug_post_data = $_SESSION['bug_post_data_'.$user_id];
	}
	//-----------当post数据没有就读取session
	$_SESSION['bug_post_data_'.$user_id] = $bug_post_data;	//立即购买和购物车数据加入session
	 $buy_array = make_arr(1,$bug_post_data,1);				//立即购买（带自定义键名）
	 //file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==1==buy_array==>".var_export($buy_array,true)."\r\n",FILE_APPEND);
	$buy_array_add_express = make_arr(1,$bug_post_data,2);	//立即购买和购物车的原始数据进行二次添加邮费，组合成新的数据
	//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==1==buy_array==>".var_export($buy_array,true)."\r\n",FILE_APPEND);
	
 }elseif($fromtype == 2){	//购物车	
 //file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==>".var_export($pro_arr,true)."\r\n",FILE_APPEND);
	 $bug_post_data = $pro_arr;								//需要添加邮费的数据				
	 //-----------当post数据没有就读取session
	if($_POST['pro_arr']==''){
			$bug_post_data = $_SESSION['bug_post_data_'.$user_id];
			$clean_cart	   = json_encode( $bug_post_data );
	}
	//-----------当post数据没有就读取session
	//file_put_contents ( "log0802.txt", "postStr====".var_export ( $bug_post_data, true ) . "\r\n", FILE_APPEND );
	$_SESSION['bug_post_data_'.$user_id] = $bug_post_data;	//立即购买和购物车数据加入session
	$buy_array = make_arr(2,$bug_post_data,1);				//购物车数据处理（带自定义键名）
	 //file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==buy_array==>".var_export($buy_array,true)."\r\n",FILE_APPEND);
	$buy_array_add_express = make_arr(2,$bug_post_data,2);	//立即购买和购物车的原始数据进行二次添加邮费，组合成新的数据
	 //file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==buy_array_add_express==>".var_export($buy_array_add_express,true)."\r\n",FILE_APPEND);
	
 }


/*------------使用购物车测试数据--------------*/	
/* $a =  array('-1', array(1510,'1253_1413',2));
$a2 =  array('-1',array(1510,'1253_1412',1));
$a3 =  array('191099',array(766,'',3));
$a4 =  array('191099',array(1285,'1088_1137',4));
$a5 =  array('195461',array(913,'',5));	
 
 $_A = [$a ,$a2 ,$a3 ,$a4,$a5 ];
 $_A2 = [$a ,$a2 ,$a ,$a ];


 $bug_post_data = $_A;
 $buy_array = make_arr(2,$_A,1);				//购物车数据处理成带键名数组
 $buy_array_add_express = make_arr(2,$bug_post_data,2);	//购物车数据处理成以供应商ID分类的数组 */
/*------------使用购物车测试数据--------------*/


/***************************重组数据 end******************************/


//-------商城基本设置  start----------//
//支付方式开关
$is_alipay    = false;				//支付宝支付开关
$is_weipay    = false;				//商城微信支付开关
$is_tenpay    = false;				//商城财付通开关	
$is_allinpay  = false;				//商城通联支付开关	
$is_payChange = false;				//零钱支付开关	
$is_pay       = false;				//暂不支付开关	
$isdelivery   = false;				//商城代付开关0关闭1开启
$iscard       = false;				//商城会员卡支付开关
$isshop       = false;				//商城到店支付开关
$is_payother  = false;				//是否开启代付
$is_paypal	  = false;				//paypal支付
$isOpenCurrency = false;			//购物币支付开关	
$is_yeepay	  = false;				//paypal支付
$query = 'SELECT id,is_alipay,is_tenpay,is_weipay,is_pay,is_payChange,is_allinpay,isdelivery,iscard,isshop,is_payother,is_paypal,is_yeepay FROM customers where isvalid=true and id='.$customer_id;
$defaultpay = "";
$result = mysql_query($query) or die('W75 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
    $is_alipay    = $row->is_alipay;
	$is_tenpay    = $row->is_tenpay;
	$is_weipay    = $row->is_weipay;
	$is_payChange = $row->is_payChange;
	$is_pay       = $row->is_pay;
	$is_allinpay  = $row->is_allinpay;
	$iscard       = $row->iscard;
	$isdelivery   = $row->isdelivery;
	$isshop       = $row->isshop;
	$is_payother  = $row->is_payother;
	$is_paypal    = $row->is_paypal;
	$is_yeepay    = $row->is_yeepay;
	break;
}


$member_template_type =1;	//申请推广员模式
$nopostage_money      =0;   //订单多少可以免邮
$exp_name 			  = "推广员";
$is_identity 		  = 0;	//是否开启身份证验证
$is_coupon 			  = 0;	
$sendstyle_express    = 1;
$sendstyle_pickup     = 0;
$shop_card_id         = -1;
$is_uploadidentity	  = 0;	//是否上传身份证附件
$query="select member_template_type,nopostage_money,exp_name,is_identity,is_coupon,sendstyle_express,sendstyle_pickup,shop_card_id,is_uploadidentity from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('w93 Query failed: ' . mysql_error());   
while ($row = mysql_fetch_object($result)) {
   $member_template_type = $row->member_template_type;
   $nopostage_money 	 = $row->nopostage_money;
   $exp_name 			 = $row->exp_name;           //推广员名称
   $is_identity			 = $row->is_identity;        //身份证限制
   $is_uploadidentity    = $row->is_uploadidentity;	 //是否上传身份证附件
   $is_coupon			 = $row->is_coupon;          //是否开启代金券 1:开启 0:关闭
   $sendstyle_express	 = $row->sendstyle_express;  //是否开启配送方式快递 1:开启 0:关闭
   $sendstyle_pickup	 = $row->sendstyle_pickup;   //是否开启配送方式自提 1:开启 0:关闭
   $shop_card_id	     = $row->shop_card_id;       //商城所用到的会员卡
}
$total_is_Pinformation = 0;//必填信息总开关开关
$query="select is_Pinformation from weixin_commonshops_extend where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('w93 Query failed: ' . mysql_error());   
while ($row = mysql_fetch_object($result)) {
   $total_is_Pinformation = $row->is_Pinformation;
}


$query = "SELECT id,name,phone,address,type from weixin_users where isvalid=true and  id=".$user_id;
$result = mysql_query($query) or die('w126 Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$username    = $row->name;
	$userphone   = $row->phone;
	$useraddress = $row->address;
	$user_type   = $row->type;
	break;
}

//查询是否开启购物币支付
$sel_curr_sql = "SELECT isOpen,custom FROM weixin_commonshop_currency WHERE customer_id=".$customer_id;
$sel_curr_res = mysql_query($sel_curr_sql);
while( $info = mysql_fetch_object($sel_curr_res) ){
	$isOpenCurrency = $info->isOpen;
	$custom 		= $info->custom;

}



//---------商城基本设置  end ------------//

/*-------查找个人购物币-------*/
$user_currency = 0;
$query_curr_user = "select currency from weixin_commonshop_user_currency where isvalid=true and customer_id=".$customer_id." and user_id=".$user_id." order by id asc limit 1";
$result_curr_user=mysql_query($query_curr_user)or die('Query failed'.mysql_error());
while($row_curr_user=mysql_fetch_object($result_curr_user)){
	$user_currency = $row_curr_user->currency;
}
$user_currency	= round($user_currency,2);
/*-------查找个人购物币-------*/


/*-------收货信息-------*/


			$add_keyid   = -1;		
			$add_name    = "";	//收货人名字
			$add_phone   = "";	//收货人电话
			$identity    = "";	//收货人身份证
			$identityimgt="";
			$identityimgf="";
			$address     = "";	//收货人地址
			$location_p  = "";	//省
			$location_c  = "";	//市	
			$location_a  = "";	//区
			$address_str = "";	//详细地址
			$is_default  = 0;	//是否为默认地址 0不是 1是
			if($aid>0){
				$query="select id,name,phone,address,location_p,location_c,location_a,is_default,identity,identityimgt,identityimgf from weixin_commonshop_addresses where isvalid=true and user_id=".$user_id."  and id =".$aid;
			}else{
				$query="select id,name,phone,address,location_p,location_c,location_a,is_default,identity,identityimgt,identityimgf from weixin_commonshop_addresses where isvalid=true and user_id=".$user_id." and is_default = 1";
			}
			//echo $query;
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			while ($row = mysql_fetch_object($result)) {
				$add_keyid   = $row->id;
				$add_name    = $row->name;
				$add_name = htmlspecialchars($add_name);
				$add_phone   = $row->phone;
				$address     = $row->address;
				$address = htmlspecialchars($address);
				$identity    = $row->identity;	//身份证
				$identityimgt= $row->identityimgt;
				$identityimgf= $row->identityimgf;
				$location_p  = $row->location_p;
				$location_c  = $row->location_c;
				$location_a  = $row->location_a;
				$address_str = $location_p." ".$location_c." ".$location_a." ".$address;	
			}
			$sum_ID_tf = 0;
			if($add_keyid>0){
				$sum_ID_tf = ((empty($identityimgt)) ? 0:1)	+  ((empty($identityimgf) ? 0:1));	//记录用户的地址信息是否已经上传了身份证证正反面
			}

/*-------收货信息-------*/


/*-------自定义区域-------*/

		$is_diy_area  = 0;
		$Query_isArea = "SELECT is_diy_area,diy_customer from weixin_commonshop_team WHERE isvalid = true AND customer_id = ".$customer_id." limit 0,1";
		$Result_isArea = mysql_query($Query_isArea) or die (" w860 : Query_isArea failed : ".mysql_error());
		if($row_isArea = mysql_fetch_object($Result_isArea)){
			$is_diy_area  =  $row_isArea->is_diy_area;		
		}				
		if($is_diy_area && !empty($location_p)){
			$areaArray = array();
			$default_diy_area_id = -2;
			$default_diy_areaname = '';			
			$Query_diyArea = "SELECT id,areaname from weixin_commonshop_team_area WHERE isvalid = true and customer_id = ".$customer_id." and grade = 3 and all_areaname like '" . $location_p.$location_c.$location_a. "%' limit 1";
			$Result_diyArea = mysql_query($Query_diyArea) or die (" w867 : Query_diyArea failed : ".mysql_error());
			while ($row_diyArea = mysql_fetch_object($Result_diyArea)) {	
				
				$default_diy_area_id = $row_diyArea->id;
				$default_diy_areaname = $row_diyArea->areaname;
			}	
		}	
		
/*-------自定义区域-------*/



/*-------查询是否有运费模板-------*/

$tmp_cout = 0;
$query = "select count(1) as tmp_cout from express_template_t where isvalid=true and customer_id=".$customer_id."";
$result=mysql_query($query)or die('Query failed'.mysql_error());
while($row=mysql_fetch_object($result)){
	$tmp_cout = $row->tmp_cout;
}
/*-------查询是否有运费模板-------*/

 /*------------立即购买和购物车重组数据--------------*/
$supply_express 			= '';
$buy_all_data 				= regroup_data_array($buy_array);			//POST数组重组对应的产品信息	
$buy_all_data_json 			= json_encode($buy_all_data);				//用于显示产品详情的数据
$buy_array_add_express_json = json_encode($buy_array_add_express);		//用于保存订单的数据
$supply_express_json 		= json_encode($supply_express);				//8.0.0.1 版本快递数组
//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==buy_all_data==>".var_export($buy_all_data,true)."\r\n",FILE_APPEND);
//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==buy_all_data_json==>".var_export($buy_all_data_json,true)."\r\n",FILE_APPEND);
//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==buy_array_add_express_json==>".var_export($buy_array_add_express_json,true)."\r\n",FILE_APPEND);
//file_put_contents("order_form.txt","desc===".date("Y-m-d H:i:s")."==2==supply_express_json==>".var_export($supply_express_json,true)."\r\n",FILE_APPEND);
//提交表单的数据	
/*------------立即购买和购物车重组数据--------------*/	
//file_put_contents ( "log0809.txt", "postStr====".var_export ( $buy_all_data, true ) . "\r\n", FILE_APPEND );
?>
	
<!DOCTYPE html>
<html>
<head>
    <title>确认订单</title>
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
    <link type="text/css" rel="stylesheet" href="./css/goods/global.css" />    
    <link type="text/css" rel="stylesheet" href="./css/goods/querendingdan.css" />    
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" />  
	<link rel="stylesheet" type="text/css" href="../common/layer/need/layer.css">  
	<script type="text/javascript" src="../common/layer/layer.js"></script>	
    <link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />       
	<link rel="stylesheet" href="./css/cui.css" />
	<script type="text/javascript" src="./js/global.js"></script>
	<script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
	<script type="text/javascript" src="./assets/js/amazeui.js"></script>
	<script type="text/javascript" src="./js/loading.js"></script>
	<script src="./js/jquery.ellipsis.js"></script>
	<script src="./js/jquery.ellipsis.unobtrusive.js"></script>
</head>
<style>
/*新加的样式*/
*{
    font-family:'微软雅黑';
}
.content-header{
    padding-bottom:10px;
    background:url(./images/color_ribbon.png) no-repeat bottom;
    background-size:100% 3px;
    background-color:#FFF;
    height:65px;
}
.content-list{
    margin-top:0px
}
.kong{
    height:10px;
}
.am-navbar{
    height:65px;
}
.content-header{
    overflow:hidden;
}
.white-list .line{
    background-color:#eee;
}
.slide_block{
    position: absolute;
    background: white;
    top: 1px;
    left: 1px;
    z-index: 1100;
    width: 31px;
    -webkit-transition: all 0.1s ease-in;
    transition: all 0.1s ease-in;
    height: 31px;
    border-radius: 100px;
    box-shadow: 0 3px 1px rgba(0,0,0,0.05), 0 0px 1px rgba(0,0,0,0.3);                
}
.slide_body{
    position: absolute;
    top: 0;
    -webkit-transition: box-shadow 0.1s ease-in;
    transition: box-shadow 0.1s ease-in;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 100px;
    box-shadow: inset 0 0 0 0 #eee, 0 0 1px rgba(0,0,0,0.4);                
} 

.input_edit{
    width: 100%;
    height: 60px;
    text-indent: 6px;
	border:0;
}
.input_edit2{
    width: 100%;
    height: 23px;
    text-indent: 6px;
}
.no_info{text-align:center;}
.no_info span{
    font-size: 15px;
    display: inline-block;
    margin-top: -10px;
	}
.no_border{border:0;}
.select_coupon{position:relative;}
.select_coupon_span_r{position:absolute;right: 35px;font-size: 13px !important;}
.select_store_span_r{position:absolute;right: 35px;font-size: 13px !important;}
.sharebg-active{z-index:3000;}
.layermbtn span{height: 39px;}
.item-row1-left1 .logo-img{width: 25px;height: auto;max-height: 30px;min-height:16px;vertical-align: middle;}

 //ld 点击效果
        .button{ 
        	-webkit-transition-duration: 0.4s; /* Safari */
        	transition-duration: 0.4s;
        }

        .buttonclick:hover{
        	box-shadow:  0 0 5px 0 rgba(0,0,0,0.24);
        }

</style>
 <script>
<?php echo 'var order_debug = true ;'		//调试开关?>
<?php echo 'var buy_all_data_json='.$buy_all_data_json.';' ;?>
<?php echo 'if(order_debug){console.log(buy_all_data_json);}' ;?>
<?php echo 'var buy_array_json='.$buy_array_add_express_json.';';?>
<?php echo 'if(order_debug){console.log(buy_array_json);}'?>	
<?php echo 'var supply_express_json='.$supply_express_json.';';?>
<?php echo 'if(order_debug){console.log(supply_express_json);}'?>				
 </script>
 
<body data-ctrl=true >
	<!-- header -->
	<!-- <header data-am-widget="header" class="am-header am-header-default header">
		<div class="am-header-left am-header-nav header-btn">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">确认订单</h1>
	</header>
    <div class="topDiv" style="height:49px;"></div> -->   <!-- 暂时屏蔽头部 -->
	<!-- header -->
	
    <!-- 个人地址 -->
	<?php 
	//$add_keyid = 1;
	if($add_keyid<0){		//开始判断是够为空 
						
			echo "<script>  
						
			showConfirmMsg('提醒','是否马上添加收货地址呢？','确定','取消',function(){
				window.location.href = 'my_address.php?customer_id=".$customer_id_en."&a_type=1';
				
			});
								
			</script>";
			
	?>
		<div class = "content-header" id="content_location">
			<div class = "content-header-left1">
				
				<div class = "content-header-left1-top2" style="margin-top: 8px;">
					
					<span style = "" onclick="location.href='my_address.php?customer_id=<?php echo $customer_id_en?>&a_type=1';"> 还没有默认收货地址，点击添加收货地址</span>	
				</div>
			</div>
			<div class = "content-header-right">
				<img src = "./images/vic/right_arrow.png" width = "10" height = "20">	
			</div>
		</div> 
	<?php 
	}else{ 
	?>
		<div class = "content-header">
			<div class = "content-header-left1">
				<div class = "content-header-left1-top1">
					<span class = "font1"> <?php echo $add_name?></span><span class = "font2"><?php echo $add_phone?> </span>
				</div>
				<div class = "content-header-left1-top2">
					<span style = "" > <?php echo $address_str;?></span>
				</div>
			</div>
			<div class = "content-header-right" >
				<img src = "./images/vic/right_arrow.png" width = "10" height = "20">	
			</div>
		</div>
	<?php } ?>	
	<!--个人信息-->
	<span class="icon-default" id="information" data-id="<?php echo $add_keyid;?>" data-name="<?php echo $add_name;?>" data-phone="<?php echo $add_phone;?>" data-address="<?php echo $address;?>" data-location_p="<?php echo $location_p;?>" data-location_c="<?php echo $location_c;?>" data-location_a="<?php echo $location_a;?>" data-identity="<?php echo $identity;?>" data-identityimgt="<?php echo $identityimgt;?>" data-identityimgf="<?php echo $identityimgf;?>" data-is_virtual="<?php echo $is_virtual;?>"></span>
	<!--个人信息-->
	
	<!-- 个人地址 -->
	
	<div  class = "content-space"></div> 
	<!--产品信息-->
	<div>
	<ul id="resultData" class = "content-list">
	<?php 
			  $all_rcount = 0;					//产品的数量
			  
			  $all_price = 0;					//产品的价格
			  
			  $ii = 0;							//供应商定位
			  
			  $sum_all_money = 0;				//订单总价
			  
			  $sum_p_is_identity = 0;			//累计产品需要身份证
			  
			  $sum_all_supply_pros_need_score = 0;	//累计产品需要的积分
		  
	foreach($buy_all_data as $supply_id_arr => $val){
			
			 $arr_data = $val;				//每个供应商所有的产品信息
			
			 $sum_rcount = 0;				//累计产品数目	
			 
			 $sum_price = 0;				//累计产品总价
			 
			 $sum_is_invoice = 0;			//累计开启发票数目	
			 	
			 $sum_is_express = 0;			//累计配送方式		
					
			 $sum_is_virtual = 0;			//累计虚拟产品			 
			
			 $sum_express = 0;				//累计快递费
			 
			
			
			 $sum_pro_weight = 0; 			//累计重量
			 
			 $sum_is_Pinformation = 0;		//累计必填信息开关之和，大于0则显示
			 
			
			 
			 $price_express = 0;		  	//同一个供应商的产品金额+快递
						
			 $PID_pros = '';			 	//同一个供应商的产品属性拼接
			
			 $PID_str = '';			 		//同一个供应商的产品ID拼接
			
			
			 $shop_name 		= $arr_data [0]['supply']['shop_name'];			//店铺名
			 $isbrand_supply 	= $arr_data [0]['supply']['isbrand_supply'];	//是否品牌供应商
			 $brand_logo 		= $arr_data [0]['supply']['brand_logo'];		//品牌供应商logo
			 $default_shop_logo = './images/goods_image/iconfont-jiantou.png';	//默认店铺logo
			 
			
	?>
	
		<li class="itemWrapper"  supply_id="<?php echo $supply_id_arr;?>" style="margin-top:0"  ii=<?php echo $ii ;?>>
			<!-- 店铺名 -->

			<div class="item-row1">
				<div class="item-row1-left1">
					<img src="<?php if($brand_logo==''){echo $default_shop_logo;}else{echo $brand_logo;}?>" width="20" height="20" class="logo-img">
				</div>
				<div class="item-row1-left2"><span><?php echo $shop_name;?></span></div>
			</div>
			<!-- 店铺名 -->
			
			
			<?php   foreach($arr_data as $key => $buy_values){		//同一个供应商的每个产品遍历
							
							if($buy_values[0] == 'no_use'){			//供应商邮费信息不能显示
								continue;
							} 	
							
							/*$select_express_id = $buy_values['express']['select_express_id'];		//获取快递规则ID
							$is_express = -1;		//是否有配送方式
							if($buy_values['express']['is_express']!=-1){
								$is_express = $buy_values['express']['is_express'];
							
							}*/
							//是否有配送方式 -1无，0有
							if($supply_express[$supply_id_arr][0] == 'failed'){
								$is_express = -1;
							}else{
								$is_express = 0;
							}
							
							$is_virtual = 0;		//是否虚拟产品
							if(!empty($buy_values['is_virtual'])){
								$is_virtual = $buy_values['is_virtual'];
							}	
							
							

			?>
			
			<!--产品详情-->
			<div class="product" id="pid_<?php echo $buy_values['pid'];?>" pid="<?php echo $buy_values['pid'];?>"> 
			
			<div class="itemMainDiv" select_express_id='<?php echo $select_express_id ;?>' pro_name = '<?php echo $buy_values['name'] ;?>' is_express='<?php echo $is_express ;?>' is_virtual='<?php echo $is_virtual ;?>' pros_need_score='<?php echo $buy_values['pros_need_score'];?>'>		
				<!--产品图片-->	
				<div class="itemPhoto" style="background-image:url(<?php echo $buy_values['imgurl'];?>)"></div>
				<!--产品属性-->
				<div class="contentLiDiv">
					<div class="itemProName">
					<?php 
					if($buy_values['supply']['brand_name']!= NULL){						//获取供应商的LOGO
						echo '['.$buy_values['supply']['brand_name'].']' ;
					}?>
					<?php echo $buy_values['name'];										//输出产品名称		?>							
					</div>
					<div class="itemProContent">
						<?php 
							foreach($buy_values['pros'] as $k => $value){
								
							echo $value['pro_parent_name'].':'.$value['child_name'];	//输出产品的属性
						?>
						
						<?php } ?>
					</div>
					<!-- <div class="itemProContent">
						<?php echo '积分：'.$buy_values['pros_need_score'] ;			//输出积分?>
					</div> -->
				</div>
				<!--产品属性-->
				<!--产品价格-->
				<div class="rightWrapper">
					<div class="itemProPrice">￥<?php echo number_format($buy_values['now_price'],2);?></div><div class="itemProCount">x<?php echo $buy_values['rcount'];?></div>
				</div>
				<!--产品价格-->
			</div>
			
			</div>	
			<!--产品详情-->
			<?php 
					/********统计每个供应商的所有产品的数据 start********/
				
					//累计产品数目
					 $sum_rcount += $buy_values['rcount'];
					 //累计产品总价
					 $sum_price += $buy_values['totalprice']; 
					 		
					 //累计开启发票
					 $sum_is_invoice += $buy_values['is_invoice'];	//只要大于0就能开启发票
					 
					 //累计是否有无配送方式
					// $sum_is_express += $is_express;
					 
					  //累计是否虚拟产品
					 $sum_is_virtual += $is_virtual;					 					
					 
					 //累计产品总量
					  $sum_pro_weight += $buy_values['express']['weight'];
					 					
					
					//累计必填信息开关
					$sum_is_Pinformation += $buy_values['is_Pinformation'];
					if($buy_values['is_Pinformation'] ==1){
						$PID_str .= $buy_values['pid'].',';
						$PID_pros .= $buy_values['prvalues'].'|*|';
					}
					 
					//累计是否需要身份证
					 $sum_p_is_identity += $buy_values['p_is_identity']; 
					 
					//累计产品需要积分 
					 $sum_all_supply_pros_need_score +=  $buy_values['pros_need_score'];
					 
					 /********统计每个供应商的所有产品的数据 end********/
					 
					 
			}
					$sum_express = $arr_data['supply_express'][1];	//每个供应商的总运费
					//var_dump($sum_express);
					//总金额 = 产品总价+每个供应商的快递费
					$price_express = $sum_price + $sum_express;
					
			?>
					
			
				
			<div class="white-list"> 
			<?php if( $sendstyle_express ){?>
				<!--配送方式-->				
				<div class="list-one express-type"> 
				   <div class="left-title">
					   <span>配送方式:</span>
				   </div> 
				   <div class="center-content">
					   <span><span>
					   <?php
					   

					   //sum_is_express 小于0则说明有一件产品没有配送方式，0表示都有配送 
					  /* if($sum_is_express < 0){		//无配送方式
					   
						   	echo '无配送方式';		//不允许支付
							
					   }else{
						   
						   if($sum_express>0){
								echo '快递￥'.$sum_express;
							}else{
								echo '免邮';
							}
					   }*/
					 
						
					   if($supply_express[$supply_id_arr][0] == 'failed'){
						   echo '无配送方式';		//不允许支付
					   }else{
						   if($sum_express>0){
								echo '快递￥'.$sum_express;
							}else{
								echo '免邮';
							}
					   }
					   
					  ?>
					   </span></span>
				   </div> 
				 
				</div> 	
				<div class="line"></div>
				<!--配送方式-->
				<?php } ?>
				
				<?php if( $sendstyle_pickup ){?>
				 
				<!--门店-->				
				<div class="list-one store button buttonclick" supply_id="<?php echo $supply_id_arr;?>"  pid="<?php echo substr($PID_str,0,-1);?>" pros="<?php echo substr($PID_pros,0,-3);?>" ii=<?php echo $ii ;?>>	 
					<div class="left-title">
					   <span>选择门店自提</span>
						<span class="select_store_span_r">							</span> 	
				    </div> 
				    <div class="right-action">
					   <img src="./images/vic/right_arrow.png" width="10" height="20">
				    </div> 
				</div> 
				<div class="line"></div>
				<!--门店-->				
				<?php } ?>
				
				<?php if($sum_is_invoice >0){?>	
				 
				<!--发票抬头-->				
				<div class="list-one"> 
					<div class="left-title">
					   <span>发票抬头:</span>
					</div> 
					<div class="center-content1 text-hidden">
					   <input type="text"  placeholder="请输入发票抬头，否则为个人发票" class="in-text invoice">
					</div> 
				</div> 	
				<div class="line"></div>
				<!--发票抬头-->
				<?php } ?>
				
				<?php if(1 == $is_identity){?>	
				 
				<!--身份证-->
				<div class="list-one"> 
					<div class="left-title">
					   <span>身份证:</span>
				    </div> 
				    <div class="center-content1 text-hidden">
					   <input type="text" placeholder="请输入正确的身份证号" class="in-text indentity" value="<?php echo $identity;?>" ii=<?php echo $ii ;?>>
				    </div> 
				</div> 
				<div class="line"></div> 
				<!--身份证-->
				<?php } ?>	
				<?php if($sum_is_Pinformation>0){?>	
				
				<!--必填信息-->
				<div class="list-one info" supply_id="<?php echo $supply_id_arr;?>"  pid="<?php echo substr($PID_str,0,-1);?>" pros="<?php echo substr($PID_pros,0,-3);?>" ii=<?php echo $ii ;?>>	 
					<div class="left-title">
					   <span>必填信息:</span>	
				    </div> 
				    <div class="right-action">
					   <img src="./images/vic/right_arrow.png" width="10" height="20">
				    </div> 
				</div> 
				<div class="line"></div> 
				<!--必填信息-->
				<?php } ?>			
				
				
				<!--买家留言-->
				<div class="list-one"> 
					<div class="left-title">
					   <span>买家留言:</span>
				    </div> 
				    <div class="center-content1 text-hidden">
						<input type="text" placeholder="选填, 可填写你和卖家达成一致的要求" class="in-text remark" ii=<?php echo $ii ;?>>	
				    </div> 
				</div>
				<div class="line"></div> 
				<!--买家留言-->					
				<div class="list-one-right"> 
					<span>共<?php echo $sum_rcount;?>件</span> 
					<span class="margin-left-span">合计:</span> 
					<span class="span3">￥<?php echo number_format($price_express,2);?></span> 
				</div> 
				<input type="hidden" class="is_theirself"  value='0'/>
				<input type="hidden" class="sum_express_supply"  value='<?php echo $sum_express?>'/>
				<input type="hidden" class="sum_price_supply"  value='<?php echo $sum_price?>'/>
			</div>
		</li>
	
	<?php 
		$all_rcount += $sum_rcount;		//产品总的数量
		$all_price += $sum_price;		//产品总的金额
		$all_express += $sum_express;	//产品总的快递费用
		$all_pro_weight += $sum_pro_weight;	//产品总的重量
	 
		$sum_all_money  += $price_express;	//产品总的金额 + 产品总的快递运费
		
		 





	  
		$ii ++;
	}
	
	?>
	</ul>
		
	</div>
	<!--产品信息-->
	
	<?php 
	
	if($isOpenCurrency == 1 ){
	?>
	<!--购物币-->
	<li class="itembutton">
      <div class="top">
        <span>使用<?php echo $custom;?>(可用<?php echo $custom.':'.$user_currency;?>)</span>  
        <input type="checkbox" id="checkbox_c1" class="chk_3" >
        <label for="checkbox_c1" open_val="0" class="open_curr">
              <div class="slide_body"></div>
              <div class="slide_block"></div>          
        </label>
	  </div>
	  <div class="currency" style="display:none">
		  <div class="line"></div>
		  <div class="bottom">	
			<input onkeyup="clearNoNum(this);" max_currr="<?php echo $user_currency;?>" class="user_currency"  placeholder="请输入抵用购物币数量" value="">
		  </div>
	  </div>
    </li>
	<!--购物币-->
	<?php 
	
	}
	?>
	<!--优惠券-->
	<?php 
	//查询用户可用的优惠券
		$coupon_count = 0;
		$query = "SELECT count(1) as coupon_count FROM weixin_commonshop_couponusers WHERE user_id=".$user_id." AND customer_id=".$customer_id." AND isvalid=true AND type=1 AND is_used=0 AND deadline >='".date("Y")."-".date("m")."-".date("d")." ".date("H").":".date("i").":".date("s")."' ";
		$result=mysql_query($query)or die('Query failed'.mysql_error());
		while($row=mysql_fetch_object($result)){
			$coupon_count = $row->coupon_count;
		}
	?>
	 <li class="itembutton">
      <div class="top select_coupon button buttonclick" onclick="go_select_coupon();" open_val="0" >
			<span>代金券</span>	

		<?php //if($select_coupon_id>0){	//选择后的优惠券详情?>
		
			<!-- <span class="select_coupon_span_r">				
			<?php echo '（满'.$rtn_coupon_array[0].'元优惠'.$rtn_coupon_array[1].'元）' ;?>
			</span>  -->
			
        <?php //}else{					//默认?>		
			
			<span class="select_coupon_span_r">您有<?php echo $coupon_count?>张可用的代金券</span>
			
		<?php //}?>
			
		       
		  <div class="right-jiantou" >
			 <img src="./images/vic/right_arrow.png" width="10" height="20">
		  </div>
		  
	 </div>
	  
    </li> 
	<!--优惠券-->
	
	<!--会员卡折扣-->
	<?php  /*			//暂时不用选择会员卡折扣
	$card_count = 0;
	$query_card = "SELECT count(1) as card_count from weixin_card_members as wcm inner join weixin_cards as wc on wcm.card_id=wc.id and wc.customer_id=".$customer_id." and wcm.isvalid=true and wc.isvalid=true and  wcm.user_id=".$user_id." ";
	//echo $query_card;
	$result=mysql_query($query_card)or die('Query failed'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$card_count = $row->card_count;
	}
	if($card_count>0){ 		//当用户拥有会员卡，才显示
	
	?>
	
	<li class="itembutton">
      <div class="top select_card" id="card" onclick="location.href='select_cards.php?customer_id=<?php echo $customer_id_en?>;'">
         		
        <?php if($select_card_id>0){	//选择后的会员卡详情?>
		
			<span>
			<?php //echo $rtn_card_array[3].'：'.'余额：'.$rtn_card_array[0].',积分：'.$rtn_card_array[1].'，折扣：'.$rtn_card_array[2].'%' ;?>
			<?php echo $rtn_card_array[3].'：折扣：'.$rtn_card_array[2].'%' ;?>
			</span> 
			
        <?php }else{					//默认?>		
		
			<span>请选择会员卡折扣</span>
			
		<?php }?>
		<!--  <span class="card_str">请选择会员卡折扣</span> -->
		  <div class="right-jiantou" >
			 <img src="./images/vic/right_arrow.png" width="10" height="20">
		  </div>
	  </div>
	 
	
	  
    </li>
	
	<?php } */?>	
	<!--会员卡折扣-->
	
	<!--会员卡折扣-->
	<?php  //会员卡折扣用商城绑定的会员卡
	$card_member_id = -1;
	$level_id = -1;
	//查找用户是否有商城会员卡，查ID和等级
	$query = "select id,level_id from weixin_card_members where isvalid=true  and user_id=".$user_id." and card_id=".$shop_card_id."";
	//echo $query;
	$result=mysql_query($query)or die('Query failed'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$card_member_id = $row->id;
		$level_id = $row->level_id;
		
	}
	if($card_member_id>0){
	
					//-----会员卡卡名
					$cardname = '';				
					$query2 = "select name from weixin_cards where isvalid=true and customer_id=".$customer_id." and id=".$shop_card_id."";
					$result2=mysql_query($query2)or die('Query failed'.mysql_error());
					while($row2=mysql_fetch_object($result2)){
						$cardname = $row2->name;
						break;
					}
					//-----会员卡剩余金额
					$query2 = "SELECT id,remain_consume from weixin_card_member_consumes where isvalid=true and  card_member_id=".$card_member_id;
					
					$result2 = mysql_query($query2) or die('w661 Query failed: ' . mysql_error());
					while ($row2 = mysql_fetch_object($result2)) {
						$card_remain = $row2->remain_consume;	
						break;
					}
					$card_remain = round($card_remain, 2);
					//-----会员卡剩余金额
					
					//-----会员卡剩余积分
					$query2 = "SELECT id,remain_score from weixin_card_member_scores where isvalid=true and  card_member_id=".$card_member_id;
					$result2 = mysql_query($query2) or die('w669 Query failed: ' . mysql_error());
					$remain_score=0;
					while ($row2 = mysql_fetch_object($result2)) {
						$remain_score = $row2->remain_score;	//会员卡剩余积分
						break;
					}
					$remain_score = round($remain_score, 2);
					//-----会员卡剩余积分
					
					$discount = 100;		//默认折扣100 不打折
					if($level_id >0){
						//----查找会员卡等级折扣
						$query2 = "SELECT discount,title,min_num from weixin_card_levels where isvalid=true and  id=".$level_id;
						
						$result2 = mysql_query($query2) or die('w678 Query failed: ' . mysql_error());
						$level_name = "";
						$discount = 100;		//默认折扣100 不打折
						$min_num = 0;
						while ($row2 = mysql_fetch_object($result2)) {
							$discount = $row2->discount;		//会员卡折扣
							$level_name = $row2->title;			//会员卡等级名称
							$miu_num = $row2->min_num;
							if($sum_all_money < $miu_num){
								$discount = 100;

							}
							break;
						}
					}
					if(!empty($level_name)){
					  $cardname = $cardname."(".$level_name.")";
					}
					//----查找会员卡等级折扣

	
				
				}
	if($card_member_id>0){ 		//当用户拥有会员卡，才显示
	
	?>
	
	<li class="itembutton">
      <div class="top">
        <span>使用会员卡折扣</span>  
        <input type="checkbox" id="checkbox_c2" class="chk_3" >
        <label for="checkbox_c2" open_val="0" class="open_card">
              <div class="slide_body"></div>
              <div class="slide_block"></div>          
        </label>
	  </div>
	  <div class="card" style="display:none">
		  <div class="line"></div>
		  <div class="bottom">	
			<div class="card_info">

			<p><?php echo $cardname.'：' ?></p>
			<p><?php echo '余额：'.$card_remain.'，积分：'.$remain_score.'，折扣：'.$discount.'%' ;?></p>

			</div> 
		  </div>
	  </div>
    </li>
	
	<?php } ?>	
	<!--会员卡折扣-->
	<!--收货时间-->
	<?php
	$sendtime_Rcount = 0;		 
	$query_sendtime="select count(1) as sendtime_Rcount from weixin_sendtimes where isvalid=true and customer_id=".$customer_id;
	//echo $query_sendtime;
	$result=mysql_query($query_sendtime)or die('Query failed'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$sendtime_Rcount = $row->sendtime_Rcount;
	}
	if($sendtime_Rcount>0){ 		//当商家设置了时间则显示
	
	?>
	
	<li class="itembutton">
      <div class="top select_sendtime button buttonclick" onclick="location.href='select_sendtime.php?customer_id=<?php echo $customer_id_en?>;'">
         		
        <?php if($sendtime_id>0){	//选择后的收货时间详情?>
		
			<span>
		
			<?php echo '送货时间：'.$rtn_sendtime_array[1];?>
			</span> 
			
        <?php }else{					//默认?>		
		
			<span>请选择送货时间</span>
			
		<?php }?>
		<!--  <span class="sendtime_str">请选择收货时间</span> -->
		  <div class="right-jiantou" >
			 <img src="./images/vic/right_arrow.png" width="10" height="20">
		  </div>
	  </div>
	 
	
	  
    </li>
	
	<?php } ?>	
	
	<li class="itembutton">
      <div class="top diy_team button buttonclick">
        
        <?php if($diy_area_id>0){	//选择后的收货时间详情?>
							
			<span><?php echo '已选择自定义区域：'.$rtn_diy_area_array[1];?></span> 			
			
        <?php }else{					//默认?>		
				
			<span>
			<?php
			if($diy_area_id!=-2){
						echo '默认选择自定义区域：'.$default_diy_areaname;
				}else{
						echo '不选择自定义区域';
				}
			?>
			</span>
			
		<?php }?>
		<!--  <span class="sendtime_str">请选择收货时间</span> -->
		  <div class="right-jiantou" >
			 <img src="./images/vic/right_arrow.png" width="10" height="20">
		  </div>
	  </div>
	 
	
	  
    </li>
	
	<!--收货时间-->
	
	<!--产品金额总明细-->
	<li class="foote">
        <div class="top">
          <div class="left">商品金额</div>
          <div class="right" id="sum_price" sum_price="<?php echo $all_price;?>">￥<?php echo number_format($all_price,2);?></div>
        </div> 
        <div class="top">
          <div class="left">优惠</div>
          <div class="right" id="save_money" save_money='0'></div>
        </div> 
        <div class="top">
          <div class="left">运费</div>
          <div class="right" id="sum_express" sum_express="<?php echo $all_express;?>">+￥<?php echo $all_express;?></div>
        </div> 
    </li>
    <div class="kong"></div>
	<!--产品金额总明细-->
	<!--代付信息-->
	<div class="pay_desc" style="display:none;">
		<span style="font-size:16px;display:block;margin-top:10px;">对你的好友说：</span>
		<textarea class="payother_desc" rows="6" cols="32" placeholder="蛋蛋的忧伤，钱不够了，你能不能帮我先垫付下"></textarea>
		<div class="pay_desc_btn">确定</div>
	</div>
	<div class="shadow" style="display:none;"></div>
	<!--代付信息-->
	
	
	<div class=" popup-memu" id = "zhifuPannel">
		<div class="list-one popup-menu-title">
            <span class="sub">选择支付方式</span>
        </div>
	<!--微信支付区域 -->
		
	<?php  if($from_type == 1){			//目前支持微信端的微信支付，app微信支付暂不支持?>	
	
		<?php if( $is_weipay ) {?>  	  
			<div class="line"></div>
			<div class = "popup-menu-row" data-value="微信支付">
				<img src="images/np-1.png">
				<span class="font">微信支付</span>
			</div>
		<?php }?>	
	
		<?php if( $is_payother ) {?> 
		<div class="line"></div>       
		<div class = "popup-menu-row" data-value="找人代付">
             <img src="images/np-6.png">
        	<span class="font">找人代付</span>
        </div>
		<?php }?>
		
	<?php  }else{						//网页端、app?>
	<!--非微信支付区域 -->	
	
		<?php if( $is_alipay) {?> 	
		<div class="line"></div>
		<div class = "popup-menu-row" data-value="支付宝支付">
             <img src="images/np-4.png">
        	<span class="font">支付宝支付</span>
        </div>
		
		<?php };?>

	<?php  } ?>
	
	<!--公共支付区域-->
    
	<?php if( $iscard && $card_member_id>0 ) {?> 
		<div class="line"></div>		
        <div class = "popup-menu-row" data-value="会员卡余额支付">
             <img src="images/np-2.png">
        	<span class="font">会员卡余额支付</span>
        </div>
	<?php }?>	

	<?php if($is_payChange){ ?>
	<div class="line"></div>
    <div class = "popup-menu-row" data-value="零钱支付">
             <img src="images/np-3.png">
        	<span class="font">钱包零钱支付</span>
    </div>
	<?php } ?>
	
	<?php if($is_yeepay){ ?>
	<div class="line"></div>
    <div class = "popup-menu-row" data-value="易宝支付">
             <img src="images/np-9.png">
        	<span class="font">易宝支付</span>
    </div>
	<?php } ?>
	
	<?php if($is_pay){ ?>
	<div class="line"></div>       
	<div class = "popup-menu-row" data-value="暂不支付">
		 <img src="images/pay_no.png">
		<span class="font">暂不支付</span>	
	</div>
	<?php } ?>
	
	
	 <?php if( $is_allinpay ) {?> 	      
		<!-- <div class="line"></div>
         <div class = "popup-menu-row" data-value="通联支付">
            <img src="images/np_8.png">
        	<span class="font">通联支付</span>
        </div>  -->
	<?php };?>	
	<?php if( $isshop ) {?> 
		<div class="line"></div>       
		<div class = "popup-menu-row" data-value="货到付款">
             <img src="images/np_5.png">
        	<span class="font">货到付款</span>
        </div>
	<?php };?>		    
	<?php if( $is_paypal ) {?> 	
     <!--   <div class="line"></div>
		 <div class = "popup-menu-row" data-value="PayPal支付">
             <img src="images/np_7.png">
        	<span class="font">PayPal</span>
        </div> -->
    <?php };?>	 
				
    
		
    <!--公共支付区域-->   
	</div>
	<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default  am-no-layout bottomButton">
		<div class = "bottomButton-left">
			<span>共<?php echo $all_rcount;?>件</span>
			<span>合计:</span>
			<span class = "bottomButton-left-right-span" id="sum_all_money"  sum_all_money="" temp_sum_all_money="" ></span>
		</div>
		<div class="bottomButton-rightWrapper" id="" style="margin-top:7px"> 
			<span>立即支付</span>
		</div>
	</div>
	 <div class="am-dimmer am-active" data-am-dimmer="" style="display: none;"></div>
	
	 <input type="hidden" class="sum_all_money"  value="<?php  echo $sum_all_money;?>">
	 <input type="hidden" class="all_pro_weight" value="<?php  echo $all_pro_weight;?>">
	 <input type="hidden" class="diy_area_id" value="<?php if($diy_area_id>0) {echo passport_encrypt((string)$diy_area_id);} else{ if($diy_area_id==-2){ echo -1;}else{echo passport_encrypt((string)$default_diy_area_id);}} ?>">	
	 <input type="hidden" class="aid" value="<?php echo passport_encrypt((string)$add_keyid) ;?>">	
	 <input type="hidden" class="select_card_id" value="<?php echo passport_encrypt((string)$card_member_id) ;?>">		
	 <input type="hidden" class="select_coupon_id" value="">	
	 <!-- <input type="hidden" class="sendtime_id" value="<?php echo $sendtime_id ;?>">	 -->
	 <input type="hidden" class="sendtime" value="<?php echo $rtn_sendtime_array[1] ;?>">	
	 <input type="hidden" class="is_payother" value="0">	
	 <input type="hidden" class="sendstyle_express" value="<?php echo $sendstyle_express ;?>">	
	 <input type="hidden" class="sendstyle_pickup" value="<?php echo $sendstyle_pickup ;?>">	
	 <input type="hidden" class="sum_all_supply_pros_need_score" value="<?php echo $sum_all_supply_pros_need_score ;?>">	
	 <input type="hidden" class="identity_str" value="<?php echo $is_identity.'_'.$is_uploadidentity.'_'.$sum_p_is_identity.'_'.$sum_ID_tf ;?>">	
		
	
</body>		

<script>


	
	var customer_id      	= '<?php echo $customer_id ?>';
	var customer_id_en      = '<?php echo $customer_id_en ?>';
	var is_diy_area    		= '<?php echo $is_diy_area; ?>';	//自定义区域
	var fromtype 			= '<?php echo $fromtype; ?>';
	var user_id 			= '<?php echo $user_id; ?>';
	var shop_card_id 		= '<?php echo $shop_card_id; ?>';
	var clean_cart  		= '<?php echo $clean_cart ; ?>';	//购物车数据
	var edit_type  			= 1;								//便于监听修改购物币使用
	
	/*********设置事件标识**********/
	var is_select_card 		= 0;		//是否使用会员卡
	var is_select_coupon 	= 0;		//是否使用代金卷
	var is_take_theirself 	= 0;		//是否使用自提	
	var is_express 			= 1;		//是否使用快递  默认使用快递
	var is_curr 			= 0;		//是否使用购物币
	/*********设置事件标识**********/	
	
	/*****缓冲变量*****/
	var card_discount 		= 0;		//会员卡折扣
	var card_discount_money = 0;		//会员卡折扣金额
	var coupon_money 		= 0;		//代金卷
	var user_curr_money 	= 0			//购物币
	/*****缓冲变量*****/	
	
	
	/***********设置事件本地存储***********/
	
	

	var envent_object = localStorage.getItem('envent_'+user_id); 	//读取localStorage的数据

	
	if(envent_object ==null || envent_object==''){
		
		var event_arr = {
			'event1':'',		//事件1
			'event2':'',		//事件2	
			'event3':'',		//事件3
			'event4':'',		//事件4
			'event5':''			//事件5
		}

		var event_arr_json = JSON.stringify(event_arr);	//转JSON	
		localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
		envent_object = localStorage.getItem('envent_'+user_id); 		//重新读取localStorage的数据
	}else{
		
	
	}
	var envent_object_arr = JSON.parse(envent_object);					//json转数组

	
		
	/************设置事件本地存储**********/	
	
	
	$(function(){
	
	
	
		/********************事件开始*****************/
			envent_listen();
		/********************事件结束*****************/

		
		/********************跳转到必填信息*****************/
			$('.info').click(function(){
				thiss = $(this);
				go_to_info(thiss);
			});
		/********************跳转到必填信息*****************/
				
		/********************跳转到选择门店*****************/
			$('.store').click(function(){
				thiss = $(this);
				go_to_store(thiss);
			});
		/********************跳转到选择门店*****************/
		
		/********************跳转到选择自定义区域*****************/
			$('.diy_team').click(function(){
				thiss = $(this);
				go_to_diy_area(thiss);
			});
		/********************跳转到选择自定义区域*****************/
		
		/********************选择支付方式*******************/	
			$('.popup-menu-row').click(function(){
				togglePan_down();
				thiss = $(this);
				choose_pay_type(thiss);	
			});
		/********************选择支付方式*******************/
	
	});
		
	
	/********************函数部分*********************/

	
	
	
	
	//用户触发事件
	var envent_listen = function(){	
	
		//------事件监听 start
		
		//-----先加载完被动事件，在执行主动事件	
		//被动		
		_event_listen();				//被动事件监听
		//主动									
		select_curr()					//选择购物币		
		select_card();					//选择会员卡
		invoice_input_listen();			//填写发票
		remark_input_listen();			//填写备注
		
		
		//------事件监听 end		
			
	
	}
	
	
	//根据保存到本地存储的最后触发的事件来计算价格
	function _event_listen(){
	
		var _object = localStorage.getItem('envent_'+user_id); 	//读取用户触发的事件localStorage的数据
			
		var _object_arr = JSON.parse(_object);							//json转数组
		
		if(_object_arr == null || _object_arr == ''){
		
		}else{		//根据不同的事件来调用方法
		
			if(_object_arr.event1 =='coupon'){			
			
				select_coupon_listen();					//当事件1为代金卷，则使用代金卷方法
				
			}
			if(_object_arr.event1 =='card'){
						
				select_card_listen();						//当事件1为会员卡折扣，则使用会员卡折扣方法
			}
											
									
			if(_object_arr.event3 =='curr'){			
				
				curr_listen();								//当事件3为购物币，则使用购物币方法
			
			}
			
			if(_object_arr.event4 =='invoice'){			
				
				invoice_listen();							//当事件4为发票，则使用发票方法
			
			}
			
			if(_object_arr.event5 =='remark'){			
				
				remark_listen();							//当事件5为备注，则使用备注方法
			
			}
		
		}
		
		take_theirself_storge_listen();						//事件2：门店自提或选择快递自动监听
		
		sum_all_money();									//计算总金额	
	}
	

	
	//计算总金额
	function sum_all_money(){
		//根据规则计算金额，列举出所有事件的计算方法
		
		if(order_debug){		//调试参数

			var data = {};

			//触发事件参数
			var _a = new Array();
			_a['card_discount_money']   = card_discount_money;
			_a['coupon_money'] 			= coupon_money;
			_a['user_curr_money'] 		= user_curr_money;
			
			data.data = _a;
			//触发事件
			var _b = new Array();
			_b['is_select_card']		 = is_select_card;
			_b['is_select_coupon']		 = is_select_coupon;
			_b['is_take_theirself'] 	 = is_take_theirself;
			_b['is_express'] 			 = is_express;
			_b['is_curr'] 				 = is_curr;
			
			data.event = _b;
						
			console.log(envent_object_arr);
			console.log('必填信息本地存储：'+localStorage.getItem('info_'+user_id));
			console.log('门店本地存储：'+localStorage.getItem('store_'+user_id));	
			console.log('购物币本地存储：'+localStorage.getItem('curr_'+user_id));	
			console.log('代金卷本地存储：'+localStorage.getItem('coupon_'+user_id));	
			console.log('发票本地存储：'+localStorage.getItem('invoice_'+user_id));	
			console.log('备注本地存储：'+localStorage.getItem('remark_'+user_id));	
			console.log(data);	
					
			
		}		
			
			
		//互斥事件1		
		//选择会员卡与代金卷互斥监听
		var envent_res1 = 0;		//事件1金额
		if(is_select_card ==1){			
						
			var select_discount = parseFloat('<?php echo $discount ;?>');
			envent_res1 = card_discount_money;			
		}
		if(is_select_coupon ==1){	
			
			envent_res1 = coupon_money;				//选择的代金券优惠金额				
		}
	
		//互斥事件2
		//选择自提与使用快递互斥监听
		var envent_res2 =0;			//事件2金额
		if(is_take_theirself ==1 ){					//自提
			//is_express = 0;
			//统计总运费
			var sum_supply_express = 0;
			$.each($('.sum_express_supply'),function(){
				var is_theirself = $(this).parent().find('.is_theirself').val();
				if(is_theirself==0){
					var supply_express = $(this).val();
					sum_supply_express += parseFloat(supply_express);
				}
				
			});
			$('#sum_express').attr('sum_express',sum_supply_express).text('+￥'+sum_supply_express);
			
			
			envent_res2 = sum_supply_express;	
		}
		if(is_express == 1){						//运费
			//is_take_theirself = 0;
			//统计总运费
			var sum_supply_express = 0;
			$.each($('.sum_express_supply'),function(){
				var is_theirself = $(this).parent().find('.is_theirself').val();
				if(is_theirself==0){
					var supply_express = $(this).val();
					sum_supply_express += parseFloat(supply_express);
				}
				
			});
			$('#sum_express').attr('sum_express',sum_supply_express).text('+￥'+sum_supply_express);
			
			
			envent_res2 = sum_supply_express;		
		}
		
		
		//使用购物币
		var envent_res3 = 0;		//事件3金额
		if(is_curr ==1 ){
			//envent_res3 = '扣除购物币的金额';
			envent_res3 = user_curr_money;
		}
						

			//节省金额 = 事件1金额+事件3金额
			var save_money = parseFloat(envent_res1) + parseFloat(envent_res3);
			
			//运费金额 = 事件2金额
			var add_money = parseFloat(envent_res2);
		
			//产品金额
			var sum_price = $('#sum_price').attr('sum_price');				//订单总价

			put_sum_data_to_html(sum_price,save_money,add_money);			//将结果赋值到html上
			
			
	}
	
	
	/*计算订单金额，并把其他数据加载到html上面*/

	function put_sum_data_to_html(sum_price,save_money,sum_express){
		
		//最终金额 = 产品原价 + 运费 - 优惠金额
		var sum_price = parseFloat(sum_price);
		var save_money = parseFloat(save_money).toFixed(2);
		var sum_express = parseFloat(sum_express);
		var final_money = sum_price  + sum_express - save_money;
			final_money = final_money.toFixed(2)
		$('#save_money').attr('save_money',save_money).text('-￥'+save_money);
		$('#sum_express').attr('sum_express',sum_express).text('+￥'+sum_express);
		$('#sum_all_money').attr('sum_all_money',final_money).text('￥'+final_money);
		
		if(edit_type == 1){		//记录每一次修改后的金额，除了购物币
				$('#sum_all_money').attr('temp_sum_all_money',final_money);
			}
	}

	/*计算订单金额，并把其他数据加载到html上面*/
	

	/************************互斥事件区域***********************/
	
	
	/******互斥事件1:会员卡折扣与代金卷*******/
	

	//监听选择会员卡折扣  暂时不用
	/*function select_card_old(){
		
		var select_card_id = parseFloat('<?php echo $select_card_id?>');			//选择的会员卡ID
		var select_card_remain = parseFloat('<?php echo $rtn_card_array[0]?>');		//选择的会员卡余额
		var select_card_score = parseFloat('<?php echo $rtn_card_array[1]?>');		//选择的会员卡积分
		var select_discount = parseFloat('<?php echo $rtn_card_array[2]?>');			//选择的会员卡折扣
			
		if(select_card_id>0 &&(select_discount>0 && select_discount<100)){
			
			//选择会员卡折扣标识置1
			is_select_card = 1;
			
			
			//var save_money =  parseFloat($('#sum_price').attr('sum_price')) * parseFloat((100 - select_discount)/100);
		
			//$('#save_money').attr('save_money',save_money.toFixed(2));
			
			
		}else{
			is_select_card = 0;
		}
	}*/
	
	
	//选择会员卡折扣
	function select_card(){
							
		$(".open_card").click(function(){
			//点击会员卡折扣事件
			console.log(envent_object_arr);
			//-----获取产品金额，优惠金额，运费
			var sum_price = parseFloat($('#sum_price').attr('sum_price'));			//产品金额
			
			var sum_express = parseFloat($('#sum_express').attr('sum_express'));	//产品的邮费
			
			//-----获取产品金额，优惠金额，运费
			
			var open_val = $(this).attr('open_val');
				
			if(open_val ==0 ){								//开启会员卡折扣
				is_select_card = 1;
				$('.card').show();							//限制会员卡折扣信息
				btn_on($(this));							//按钮显示ON 状态
				
				//----计算实际金额				
				var select_discount = parseFloat('<?php echo $discount ;?>');
				
				var save_card =  parseFloat($('#sum_price').attr('sum_price')) * parseFloat((100 - select_discount)/100);
					
				card_discount_money = save_card;		//赋值给折扣金额																	
	
				
								
				//代金卷互斥处理
				is_select_coupon = 0;	
				
				$('.select_coupon_span_r').hide();								//隐藏代金卷信息
				
				envent_object_arr.event1 = 'card';								//把事件1修改为'card',存入本地存储
				
				var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
				
				localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
				
				sum_all_money();						//----计算实际金额
				
			}else{											//关闭会员卡折扣
				
				is_select_card = 0;
				envent_object_arr.event1 = '';
				card_discount_money = 0;					//赋值给折扣金额				
				sum_all_money();							//重新计算价格
								
				$('.card').hide();							//隐藏会员卡折扣信息
				btn_off($(this));							//按钮显示ON 状态
					
			}							
				var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
				//console.log(event_arr_json);
				localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
		});
		
		
	}
	

	//会员卡折扣监听处理
	function select_card_listen(){
						
			is_select_card = 1;
			$('.card').show();							//限制会员卡折扣信息
			btn_on($(".open_card"));							//按钮显示ON 状态
			
			//----计算实际金额				
			var select_discount = parseFloat('<?php echo $discount ;?>');
			
			var save_card =  parseFloat($('#sum_price').attr('sum_price')) * parseFloat((100 - select_discount)/100);
				
			card_discount_money = save_card;		//赋值给折扣金额																	
				
			
			//代金卷互斥处理
			is_select_coupon = 0;	
			
			$('.select_coupon_span_r').hide();			//隐藏代金卷信息
	
	}
	
	
	
	
	//代金券监听处理
	function select_coupon_listen(){
		
		var sum_price = $('#sum_price').attr('sum_price');				//订单总价
		
		var coupon_object = localStorage.getItem('coupon_'+user_id); 	//读取localStorage的数据
			
		var coupon_object_arr = JSON.parse(coupon_object);				//json转数组
		
		if(coupon_object == null || coupon_object == ''){
		
		}else{
			var select_coupon_id 	= coupon_object_arr[0];				//选择的代金券ID
			var NeedMoney 		 	= coupon_object_arr[1];				//选择的代金券金额限制
			var money 				= coupon_object_arr[2];				//选择的代金券优惠金额
			
			if(select_coupon_id>0 && NeedMoney<=sum_price ){									//当选择了代金券且代金券使用金额限制小于订单总价，才能使用代金券
			
				//选择代金卷标识置1
				is_select_coupon = 1;	
				coupon_money	=	money;	//重新赋值给代金卷金额
				//sum_all_money();			//重新计算价格					
				$('.select_coupon_span_r').text('(满元'+NeedMoney+'优惠'+money+'元)');
				$('.select_coupon_id').val(select_coupon_id);
				
				//隐藏会员卡折扣
				$('.card').hide();
				//----关闭会员折扣开关
				btn_off($('.open_card'));
				//会员卡折扣互斥操作
				is_select_card	= 0;			
				
			}
			
		}
	}
	/******互斥事件1:会员卡折扣与代金卷*******/	
	
	
	
	/******互斥事件2：自提与使用快递*******/
	
	
	//监听门店的本地存储
	function take_theirself_storge_listen(){
		
		var store_object = localStorage.getItem('store_'+user_id); 	//读取localStorage的数据
			
		var store_object_arr = JSON.parse(store_object);			//json转数组
	
		var j = 0;
		
		if(store_object == null || store_object == ''){
		
		}else{
			
			$.each(store_object_arr,function(i,value){
				
				if(value == null || value == ''){
					return ;		//contiune的作用
				}
				var store_supply_id 	= value[0];
				var store_id 			= value[1];
				var default_name 		= value[2];
				
				
				//遍历每个供应商的产品
				$.each($('#resultData .itemWrapper'),function(k,val){
					var thiss = $(this);
				
					var self_supply_id =  thiss.attr('supply_id');
					
					if(store_supply_id == self_supply_id){			//找到用户每个供应商对应选择的的门店
						if(store_id != -1){
							thiss.find('.is_theirself').val(1);						//修改使用门店标识
							
							thiss.find('.store').find('.left-title').css('width','60%');
							thiss.find('.store').find('.left-title >span').text('已选择的门店：');
							thiss.find('.store').find('.left-title > .select_store_span_r').text(default_name);
							thiss.find('.express-type >.center-content span').text('门店自提');
							
							//修改价格
							var sum_price_supply = thiss.find('.sum_price_supply').val();																				
							thiss.find('.list-one-right >.span3 ').text('￥'+sum_price_supply);
							//选择门店不考虑无配送方式
							thiss.find('.itemMainDiv').attr('is_express',0);	
						}
						
					}
				
				});
				j++;
			});
			if(j >0){	//假如门店存储不为空且选择了门店，则使用自提计算运费

				is_take_theirself = 1;
				is_express = 0;
				envent_object_arr.event2 = 'take_theirself';
			}else{
			
				is_take_theirself = 0;
				is_express = 1;
				envent_object_arr.event2 = 'express';
			
			}
			
			var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
			
			localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
		
		}
	
	}
	
	
	
	/******互斥事件2：自提与使用快递*******/
	
	
	/******互斥事件3：使用购物币*******/
	
	//点击购物币事件
	function select_curr(){
		
		$(".open_curr").click(function(){
			
			var open_val = $(this).attr('open_val');

			if(open_val ==0 ){
				
				is_curr = 1;
				envent_object_arr.event3 = 'curr';
				btn_on($(this));
				$('.currency').show();
				curr_input_listen();					//监听购物币输入框
				
			}else{
				
				is_curr = 0;
				envent_object_arr.event3 = '';
				btn_off($(this));
				$('.user_currency').val('');			//清空用户输入的购物币
				$('.currency').hide();							
				user_curr_money = 0;					//不使用购物币					
				sum_all_money();
			}
			
			
		
			var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
			
			localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
			
		});
		
		curr_input_listen();				//跳转页面回来需要自动监听购物币输入框
		
	}
	
	
	//监听购物币输入框
	function curr_input_listen(){
		

		$('.user_currency').bind('input propertychange', function() {
			
			var all_money = $('#sum_all_money').attr('temp_sum_all_money');	
			
			edit_type = 0;	//不更新temp_sum_all_money
			
			var max_curr = $('.user_currency').attr('max_currr');
				
			var  curr_values = $('.user_currency').val();			//用户输入的购物币
			
			if(curr_values!='' ){
				
				if(parseFloat(curr_values) > parseFloat(max_curr)){	//当超出可用购物币
															
					curr_error('您输入的购物币超出可用的购物币');
					
					return;
				}

				if(parseFloat(curr_values) > parseFloat(all_money)){	//当超出订单金额
									
					//console.log()
					curr_error('您输入的购物币超出订单金额');
					
					return;
				}
								
				//在可用购物币以内，正常使用
				
				user_curr_money = curr_values;						
				
				localStorage.setItem('curr_'+user_id,curr_values);	//存入localStorage	
				
				sum_all_money();
			
			}else{													//当输入为空
								
				$('.user_currency').val('');
				
				user_curr_money = 0;
					
				sum_all_money();
				
			}
			

		});
	}
	
	
	//购物币监听
	function curr_listen(){
	
		var curr_object = localStorage.getItem('curr_'+user_id); 	//读取localStorage的数据
			
		if(curr_object ==null || curr_object=='' || parseFloat(curr_object) == 0){
			
				envent_object_arr.event3 = '';									//清空事件3			
				var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON		
				localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
		}else{
			
			is_curr = 1;
			user_curr_money = curr_object;					
			btn_on($('.open_curr'));
			$('.currency').show();
			$('.user_currency').val(curr_object);
		}
		
	
	}
	
	//购物币输入错误提示
	function curr_error(error_txt){

			showAlertMsg("提示",error_txt,"知道了");						//提示内容
			
			$('.user_currency').val('');									//清空输入框
			
			user_curr_money = 0;											//重新赋值为0
						
			sum_all_money();												//重新计算
			
			localStorage.setItem('curr_'+user_id,'');						//清空购物币本地存储
			
			envent_object_arr.event3 = '';									//清空事件3
			
			var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON	
	
			localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
		
	}
	
	/******互斥事件3：使用购物币*******/
	
	
	/******互斥事件4：使用发票*******/
	//发票输入监听
	function invoice_input_listen(){
			
		$.each($('.itemWrapper'),function(i,value){
			
			var thiss = $(this);
			var supply_id = thiss.attr('supply_id');
			var ii = thiss.attr('ii');
			
			thiss.find('.invoice').bind('input propertychange', function() {
					
					var  invoice_values = $('.invoice').val();			//用户输入的发票抬头					
					if(invoice_values!='' ){						
						var rtn_array_temp = new Array(supply_id,invoice_values); 						
						envent_object_arr.event4 = 'invoice';	
					}else{													//当输入为空
												
						envent_object_arr.event4 = '';									//清空事件4		
											
					}
											
					var invoice_object = localStorage.getItem('invoice_'+user_id); 	//读取localStorage的数据
					var invoice_object_arr = new Array();
					invoice_object_arr = JSON.parse(invoice_object);				//json转数组	
						
					if(invoice_object_arr==null || invoice_object_arr==''){			//创建
						var _A = new Array();					
						_A[ii] = rtn_array_temp ;					
						var rtn_array_json = JSON.stringify(_A);					//转JSON					
						localStorage.setItem('invoice_'+user_id,rtn_array_json);	//存入localStorage
					
					}else{					 			//修改自己的内容						
						invoice_object_arr[ii] = rtn_array_temp ; 			
						var rtn_array_json = JSON.stringify(invoice_object_arr);	//转JSON				
						localStorage.setItem('invoice_'+user_id,rtn_array_json);	//存入localStorage
						
					}
				
					//设置事件4为invoice		
					var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON		
					localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
					
				});
		});
						
	
	}
	
	//发票本地存储监听
	function invoice_listen(){
		
			var invoice_object = localStorage.getItem('invoice_'+user_id); 			//读取localStorage的数据
			var invoice_object_arr = new Array();
			invoice_object_arr = JSON.parse(invoice_object);						//json转数组
			if(invoice_object_arr ==null || invoice_object_arr==''){
				
			}else{
				
				$.each(invoice_object_arr,function(i,value){
					if(value == null || value == ''){
						return ;		//contiune的作用
					}
					var invoice_supply_id 	= value[0];
					var invoice 			= value[1];
				
					if(invoice == null || invoice==''){
						return ;		//contiune的作用
					}
					
					//遍历每个供应商的产品
					$.each($('#resultData .itemWrapper'),function(k,val){
						var thiss = $(this);
					
						var self_supply_id =  thiss.attr('supply_id');
						
						if(invoice_supply_id == self_supply_id){			//找到用户每个供应商对应选择的的发票
														
							thiss.find('.invoice').val(invoice);
							
						}
					
					});
					
				});												
			}	
	}
	
	/******互斥事件4：使用发票*******/
	
	
	/******互斥事件5：使用备注*******/
	
	//备注输入监听
	function remark_input_listen(){
			
		$.each($('.itemWrapper'),function(i,value){
			
			var thiss = $(this);
			var supply_id = thiss.attr('supply_id');
			var ii = thiss.attr('ii');
			
			thiss.find('.remark').bind('input propertychange', function() {
					
					var  remark_values = $('.remark').val();			//用户输入的发票抬头
					
					if(remark_values!='' ){
						
						var rtn_array_temp = new Array(supply_id,remark_values); 
						
						envent_object_arr.event5 = 'remark';							//设置事件5为remark
						
					}else{																//当输入为空
												
						envent_object_arr.event5 = '';									//清空事件5		
											
					}
											
					var remark_object = localStorage.getItem('remark_'+user_id); 	//读取localStorage的数据
					var remark_object_arr = new Array();
					remark_object_arr = JSON.parse(remark_object);			//json转数组	
						
					if(remark_object_arr==null || remark_object_arr==''){		//创建本地存储
						var _A = new Array();					
						_A[ii] = rtn_array_temp ;					
						var rtn_array_json = JSON.stringify(_A);				//转JSON					
						localStorage.setItem('remark_'+user_id,rtn_array_json);	//存入localStorage
					
					}else{					 									//修改自己的内容
						
						remark_object_arr[ii] = rtn_array_temp ; 			
						var rtn_array_json = JSON.stringify(remark_object_arr);	//转JSON				
						localStorage.setItem('remark_'+user_id,rtn_array_json);	//存入localStorage
						
					}
										
					var event_arr_json = JSON.stringify(envent_object_arr);			//转JSON		
					localStorage.setItem('envent_'+user_id,event_arr_json);			//初始化被动事件存入localStorage
					
				});
		});

	}
	
	//备注本地存储监听
	function remark_listen(){
		
			var remark_object = localStorage.getItem('remark_'+user_id); 			//读取localStorage的数据
			var invoice_object_arr = new Array();
			remark_object_arr = JSON.parse(remark_object);							//json转数组
			if(remark_object_arr ==null || remark_object_arr==''){
				
			}else{
				
				$.each(remark_object_arr,function(i,value){
					if(value == null || value == ''){
						return ;		//contiune的作用
					}
					var remark_supply_id 	= value[0];
					var remark 				= value[1];
				
					if(remark == null || remark==''){
						return ;		//contiune的作用
					}
					
					//遍历每个供应商的产品
					$.each($('#resultData .itemWrapper'),function(k,val){
						var thiss = $(this);
					
						var self_supply_id =  thiss.attr('supply_id');
						
						if(remark_supply_id == self_supply_id){			//找到用户每个供应商对应选择的的发票
														
							thiss.find('.remark').val(remark);
							
						}
					
					});
					
				});
									
			
			}	
	}
	
	/******互斥事件5：使用备注*******/
	
	/************************互斥事件区域***********************/
	
	/************************跳转区域 start***********************/
	
	//跳转填写必填信息
	function go_to_info(obj){
		
		var PID_str = obj.attr('pid');		
		var pros = obj.attr('pros');		
		var supply_id = obj.attr('supply_id');	
		var ii = obj.attr('ii');			

		var strurl = "required-info.php?customer_id="+customer_id_en;
		//该供应商下的所有产品ID
		var post_data1 = new Array();
		post_data1['key'] = 'pid';
		post_data1['val'] = PID_str;
		//品牌供应商ID或者平台-1
		var post_data2 = new Array();
		post_data2['key'] = 'supply_id';
		post_data2['val'] = supply_id;
		//该供应商下的所有属性ID
		var post_data3 = new Array();
		post_data3['key'] = 'pros';
		post_data3['val'] = pros;
		//定位ID
		var post_data4 = new Array();
		post_data4['key'] = 'ii';
		post_data4['val'] = ii;
		
		var post_object = [];
		post_object.push(post_data1,post_data2,post_data3,post_data4);
		Turn_Post(post_object,strurl);
		
	}
	   
	//跳转选择代金券
	function go_select_coupon(){
		var sum_price = $('#sum_price').attr('sum_price');				//订单总价			
		var post_data = new Array();		
		post_data['key'] = 'n_p';
		post_data['val'] = sum_price;
		var strurl = "coupon.php?customer_id="+customer_id_en+'&w=1';
		var post_object = [];
		post_object.push(post_data);									//将产品金额传入post
		Turn_Post(post_object,strurl);									//POST提交到代金券页面
	}
	
		//门店
	function go_to_store(obj){
				
		var supply_id = obj.attr('supply_id');	
		var ii = obj.attr('ii');			

		var strurl = "select_store.php?customer_id="+customer_id_en;
		//品牌供应商ID或者平台-1
		var post_data1 = new Array();
		post_data1['key'] = 'supply_id';
		post_data1['val'] = supply_id;
		//定位ID
		var post_data2 = new Array();
		post_data2['key'] = 'ii';
		post_data2['val'] = ii;
		
		var post_data3 = new Array();
		post_data3['key'] = 'shop_card_id';
		post_data3['val'] = shop_card_id;
		
		var post_object = [];
		post_object.push(post_data1,post_data2,post_data3);
		Turn_Post(post_object,strurl);
		
	}
	
		//自定义区域
	function go_to_diy_area(obj){
				
		var supply_id = obj.attr('supply_id');	
		var ii = obj.attr('ii');			

		var strurl = "select_diy_area.php?customer_id="+customer_id_en;
		
		var post_data1 = new Array();
		post_data1['key'] = 'all_areaname';
		post_data1['val'] = '<?php echo $location_p.$location_c.$location_a?>';	
		
		var post_object = [];
		post_object.push(post_data1);
		Turn_Post(post_object,strurl);
		
	}
	/************************跳转区域 end***********************/
	
	
/*POST提交数据*/
function Turn_Post(object,strurl){
  //object:需要创建post数据一对数组 [key:val]
  
	/* 将GET方法改为POST ----start---*/
		
    var objform = document.createElement('form');
	document.body.appendChild(objform);
	
	
	$.each(object,function(i,value){
	
		var obj_p = document.createElement("input");
		obj_p.type = "hidden";
		objform.appendChild(obj_p);
		obj_p.value = value['val'];
		obj_p.name = value['key'];
	});

	objform.action = strurl;
	objform.method = "POST"
	objform.submit();
	/* 将GET方法改为POST ----end---*/	
}
/*POST提交数据*/

function clearNoNum(obj){
	obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
	obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字而不是
	obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个. 清除多余的
	obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
	obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
}



	/********************函数部分*********************/
</script>	
<script src="./js/goods/order_form.js?v=<?php echo time();?>"></script>
<?php require('../common/share.php');?>	
		
</body>
</html>