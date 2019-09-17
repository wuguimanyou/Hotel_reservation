<?php
//V8.0 详情页
/*
header("Location:personal_center.php");
exit();
*/
//停止V8.0之前的代码

header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility.php');
require('../common_shop/common/cookie.php');

$totalInfo = isset($_COOKIE['totalInfo']) ? $_COOKIE['totalInfo'] : "";
$len = (int)explode("|", $totalInfo)[0];//购物车数量显示

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('../common/jssdk.php');    //导入JS SDK配置


$jssdk = new JSSDK($customer_id);           //实例化
$signPackage = $jssdk->GetSignPackage();  

$now=date("Y-m-d H:i:s");

//$new_baseurl = BaseURL."back_commonshop/";
$new_baseurl = "http://".$http_host; //新商城图片显示
if(!preg_match('/^\d+$/i', $customer_id)){			
	$customer_id = passport_decrypt($customer_id);
}
if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
	$user_id = passport_decrypt($user_id);
}else{
	if(!empty($_SESSION["user_id_".$customer_id])){
	   $user_id=$_SESSION["user_id_".$customer_id];
	}
}
if(!empty($_GET["apptype"])){
    $apptype=$configutil->splash_new($_GET["apptype"]);
}else{
	if(!empty($_SESSION["apptype".$customer_id])){
	   $apptype=$_SESSION["apptype".$customer_id];
	}
}
$currtype= 1;
if(!empty($_GET["currtype"])){
    $currtype = $_GET["currtype"];
}
$pagenum = 1;

/*
$from = "";
if(!empty($_GET["from"])){
    $from = $configutil->splash_new($_GET["from"]);//finance金融保险
}

 if(!empty($_SESSION["myfromuser_".$customer_id])){
	$fromuser = $_SESSION["myfromuser_".$customer_id];
	$_SESSION["fromuser_".$customer_id]=$fromuser;
	$query = "SELECT id,parent_id from weixin_users where isvalid=true and  customer_id=".$customer_id." and weixin_fromuser='".$fromuser."' limit 0,1";
	$result = mysql_query($query) or die('Query failed1: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	  $parent_id = $row->parent_id;
	  $user_id = $row->id;
	  break;
	}
	$_SESSION["user_id_".$customer_id] = $user_id;
	$_SESSION["parent_id_".$customer_id] = $parent_id;
}
if($user_id<0){
   if(!empty($fromuser)){
			
		 $query = "select id,parent_id from weixin_users where isvalid=true and customer_id=".$customer_id." and weixin_fromuser='".$fromuser."'  limit 0,1";
		 $result = mysql_query($query) or die('Query failed2: ' . mysql_error());
		 $user_id = -1;
		 $parent_id = -1;
		 while ($row = mysql_fetch_object($result)) {
			$user_id = $row->id;
			$parent_id = $row->parent_id;
		 }
		 if($user_id<0){
			 $sql="insert into weixin_users(weixin_fromuser,isvalid,customer_id) values('".$fromuser."',true,".$customer_id.")";
			 mysql_query($sql);
			 $user_id =mysql_insert_id();
		 }
		 $_SESSION["user_id_".$customer_id] = $user_id;
		 $_SESSION["parent_id_".$customer_id] = $parent_id;
	 }
}




//define("InviteUrl","http://".CLIENT_HOST."/weixinpl/commonshop/show_commonshop.php?customer_id=");
if(empty($_GET["islist"])){
    //是否需要判断 要跳转     此句有错
	$query="select member_template_type from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed3:' . mysql_error());
	$member_template_type=1;
	while ($row = mysql_fetch_object($result)) {
	   $member_template_type = $row->member_template_type;
	   break;
	}
	if($member_template_type==2){
	   //另外一个 个人中心模板
	   echo "<script>document.location='../common_shop/jiushop/order_list_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=2&from=".$from."';</script>";
	   mysql_close($link);
	   return;
	}
	if($member_template_type==3){
	   //单品 个人中心模板
	   echo "<script>document.location='../common_shop/jiushop/order_list_new_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=3&from=".$from."';</script>";
	   mysql_close($link);
	   return;
	}
	if($member_template_type==4){
	   header("Location: ../common_shop/jiushop/order_list_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=4&from=".$from);
	   mysql_close($link);
	   return;
	}
	if($member_template_type==5){
	   header("Location: ../common_shop/jiushop/order_list_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=5&from=".$from);
	   mysql_close($link);
	   return;
	}
	if($member_template_type==6){
	   header("Location: ../common_shop/jiushop/order_list_new_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=6&from=".$from);
	   mysql_close($link);
	   return;
	}
	if($member_template_type==7){
	   header("Location: ../common_shop/jiushop/order_list_new_new.php?user_id=".passport_encrypt((string)$user_id)."&member_template_type=7&from=".$from);
	   mysql_close($link);
	   return;
	}
}else{ 
	//城市商圈，渠道开关
	$is_cityarea=0;
	$is_cityarea_count=0;
	$query="select count(1) as is_cityarea_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and (c.sys_name='商圈-美食' or c.sys_name='商圈-外卖' or c.sys_name='商圈-金融保险' or c.sys_name='商圈-酒店' or c.sys_name='商圈-ktv') and c.id=cf.column_id";
	$result = mysql_query($query) or die('W_is_supplier Query failed: ' . mysql_error());  
	while ($row = mysql_fetch_object($result)) {
		$is_cityarea_count = $row->is_cityarea_count;
		break;
	}
	if($is_cityarea_count>0){
		$is_cityarea=1;
	}
	$isshop = 0;   
	if(!empty($_GET["isshop"])){
	   $isshop = $_GET["isshop"];
	}
	if($is_cityarea==0){
		$isshop = 1;   //若商圈功能没开通，直接进入商城订单页
	}
	if($is_cityarea==1 && $isshop==0){	
		header("Location: ../common_shop/jiushop/type_list.php?customer_id=".$customer_id_en."&user_id=".passport_encrypt((string)$user_id));
		mysql_close($link);
		return;
	}
}

$weixin_name="";
$query="select name,weixin_name,weixin_headimgurl from weixin_users where isvalid=true and id=".$user_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$name = "";
$headimgurl="";
while ($row = mysql_fetch_object($result)) {
   $name = $row->name;
   $weixin_name = $row->weixin_name;
   $headimgurl= $row->weixin_headimgurl;
}
if(empty($headimgurl)){
   //重新获取头像
    $headimgurl = "../common_shop/common/images/user_log.png";
    $query = 'SELECT id,appid,appsecret,access_token FROM weixin_menus where isvalid=true and customer_id='.$customer_id;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
	$access_token="";
	while ($row = mysql_fetch_object($result)) {
		$keyid =  $row->id ;
		$appid =  $row->appid ;
		$appsecret = $row->appsecret;
		$access_token = $row->access_token;
		break;
	}
	if(!empty($appid)){
	    //认证的服务号
		$url="https://api.weixin.qq.com/cgi-bin/user/info";
		$data = array('access_token'=>$access_token,'openid'=>$fromuser); 
		

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1); 
		// 这一句是最主要的
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
		$html = curl_exec($ch);  
		
		curl_close($ch) ;

		$obj=json_decode($html);
		
		 if(!empty($obj->errmsg) or  empty($obj->nickname)){
			 $errmsg =$obj->errmsg ;
			//echo $errorcode;
			if($errmsg=="access_token expired" or  empty($obj->nickname)){
			 //高级接口超时，重新绑定
			//echo "<script>win_alert('发生未知错误！请联系商家');</script>";
				$data = array('grant_type'=>'client_credential','appid'=>$appid,'secret'=>$appsecret);  
				$url = "https://api.weixin.qq.com/cgi-bin/token";

				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, 1); 
				// 这一句是最主要的
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
				$html = curl_exec($ch);  
				
				$obj=json_decode($html);
				
				$access_token = "";
				curl_close($ch) ;
				if(!empty($obj->access_token)){
				   $access_token = $obj->access_token;
				   
				   $query4="update weixin_menus set appid='".$appid."',appsecret='".$appsecret."', access_token = '".$access_token."' where customer_id=".$customer_id;
				   mysql_query($query4);
				   
					$url="https://api.weixin.qq.com/cgi-bin/user/info";
				   $data = array('access_token'=>$access_token,'openid'=>$fromuser); 


					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_POST, 1); 
					// 这一句是最主要的
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
					$html = curl_exec($ch);  
					$obj=json_decode($html);
					
					if(empty($obj->nickname)){
					    $data = array('grant_type'=>'client_credential','appid'=>$appid,'secret'=>$appsecret);  
						$url = "https://api.weixin.qq.com/cgi-bin/token";

						$ch = curl_init(); 
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_POST, 1); 
						// 这一句是最主要的
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
						$html = curl_exec($ch);  
						//echo $html;
						$obj=json_decode($html);
						
						$access_token = "";
						curl_close($ch) ;
						if(!empty($obj->access_token)){
						   $access_token = $obj->access_token;
						   
						   $query4="update weixin_menus set appid='".$appid."',appsecret='".$appsecret."', access_token = '".$access_token."' where customer_id=".$customer_id;
						   mysql_query($query4);
						   
							$url="https://api.weixin.qq.com/cgi-bin/user/info";
						   $data = array('access_token'=>$access_token,'openid'=>$fromuser); 


							$ch = curl_init(); 
							curl_setopt($ch, CURLOPT_URL, $url);
							curl_setopt($ch, CURLOPT_POST, 1); 
							// 这一句是最主要的
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
							curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
							$html = curl_exec($ch); 
                            
							$obj=json_decode($html);
						}else{
						   echo "<script>win_alert2('发生未知错误！请联系商家');</script>";
						   return;
						}
					}
					
					$weixin_name =  $obj->nickname;
					$sex = $obj->sex;
					$headimgurl= $obj->headimgurl;
					$subscribe_time = $obj->subscribe_time;
					$query4 = "update weixin_users set weixin_headimgurl='".$headimgurl."',weixin_name='".$weixin_name."',sex=".$sex." where id=".$user_id;
					//echo $query;	
					mysql_query($query4);
				}else{
				   echo "<script>win_alert2('发生未知错误！请联系商家');</script>";
				   return;
				}
			 }
		  }else{
				$weixin_name =  $obj->nickname;
				
				$sex = $obj->sex;
				$headimgurl= $obj->headimgurl;
				$subscribe_time = $obj->subscribe_time;
				$query4 = "update weixin_users set weixin_headimgurl='".$headimgurl."',weixin_name='".$weixin_name."',sex=".$sex." where id=".$user_id;
				mysql_query($query4);
		 }

	}
}

if(empty($weixin_name)){
    $weixin_name = $name;
}


$issell = 0;
if(!empty($_SESSION["issell"])){

   $issell = $_SESSION["issell"];
}



$is_alipay=false;
$is_weipay=false;
$is_tenpay=false;
$is_allinpay=false;
$isdelivery=false;
$iscard=false;
$isshop =false;
$query2 = 'SELECT id,is_alipay,is_tenpay,is_weipay,is_allinpay,isdelivery,iscard,isshop,is_paypal FROM customers where isvalid=true and id='.$customer_id;

$defaultpay = "去付款";
$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
while ($row2 = mysql_fetch_object($result2)) {
    $is_alipay=$row2->is_alipay;
	$is_tenpay=$row2->is_tenpay;
	$is_weipay = $row2->is_weipay;
	$is_allinpay = $row2->is_allinpay;
	$iscard = $row2->iscard;
	$isdelivery = $row2->isdelivery;
	$isshop = $row2->isshop;
	$is_paypal=$row2->is_paypal;
	break;
}
$card_remain=0;
$card_member_id=-1;
$sendstatus = 0;
$is_pic = 0;
$shop_card_id=-1;
$template_type_bg;
 $query="select shop_card_id,exp_name,template_head_bg from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
 $result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 while ($row = mysql_fetch_object($result)) {
     $shop_card_id = $row->shop_card_id;
	 $template_head_bg=$row->template_head_bg;
	$template_type_bg=$template_head_bg?1:0;	
	 break;
 }
 
 $member_template_type=1;
 $nopostage_money = 0;
$query="select member_template_type,nopostage_money,is_pic from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());   
while ($row = mysql_fetch_object($result)) {
	$is_pic=$row->is_pic;
	$member_template_type = $row->member_template_type;
	$nopostage_money = $row->nopostage_money;
}





$is_shopdistr=0;
$is_my_commission = 0;
$query = "select issell,is_my_commission from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$issell = $row->issell;
    $is_my_commission = $row->is_my_commission;	
}

//是否开启购物币
$sql_cur_sql = "SELECT c.isOpen,u.currency,c.custom FROM weixin_commonshop_currency c LEFT JOIN weixin_commonshop_user_currency u ON c.customer_id=u.customer_id WHERE c.customer_id=".$customer_id." and u.user_id=".$user_id." limit 1";
$sql_cur_res = mysql_query($sql_cur_sql);
while($row=mysql_fetch_object($sql_cur_res)){
	$isOpen_currency = $row->isOpen;
	$currency 		 = $row->currency;
	$custom 		 = $row->custom;
}

*/


?>
<!DOCTYPE html>
<html>
<head>
    <title>我的订单</title>
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
    <link type="text/css" rel="stylesheet" href="./css/css_orange.css" />    
    


    <!-- 基本dialog-->
    <link type="text/css" rel="stylesheet" href="./css/goods/dialog.css" />
    <link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />
	
</head>

<link type="text/css" rel="stylesheet" href="./css/order_css/style.css" media="all">
<link type="text/css" rel="stylesheet" href="./css/order_css/dingdan.css" />

<body id="mainBody" data-ctrl=true>
    <div id="mainDiv">
	    <!--
		<header data-am-widget="header" class="am-header am-header-default">
		    <div class="am-header-left am-header-nav" onclick="history.back()">
			    <img class="am-header-icon-custom icon_back" src="./images/center/nav_bar_back.png"/><span>返回</span>
		    </div>
	        <h1 class="am-header-title topTitle">订单管理</h1>
	    </header>
        <div class="topDiv"></div><!-- 暂时隐藏头部导航栏 -->
        
		<!-- 上面的Tabbar开始 -->
		<div id="middle-tab" class="tabbar">
            <div id="kindAll" class="area-one" data-type="1">
                <img src="./images/order_image/icon_dingdan_quanbu_sel-orange.png" width="20" height="20">
                <div>全部</div>
            </div>
            
			<div id="kindDaiFuKuan" class="area-one" data-type="2">
                <img src="./images/order_image/icon_daifukuan.png" width="20" height="20">
                <div>待付款</div>
            </div>
            
			<div id="kindDaiFaHuo" class="area-one" data-type="3">
                <img src="./images/order_image/icon_daifahuo.png" width="20" height="20">
                <div>待发货</div>
            </div>
            
			<div id="kindDaiShouHuo" class="area-one" data-type="4">
                <img src="./images/order_image/icon_daishouhuo.png" width="20" height="20">
                <div>待收货</div>
            </div>
            
			<div id="kindDaiPingJia" class="area-one" data-type="5">
                <img src="./images/order_image/icon_daipingjia.png" width="20" height="20">
                <div>待评价</div>
            </div>
            <!-- 已完成暂时不用 -->
            <!--
			<div id="kindYiWanCheng" class="area-one" data-type="6">
                <img src="./images/order_image/icon_daipingjia.png" width="20" height="20">
                <div>已完成</div>
            </div>
            -->
			<div id="kindShouHouZhong" class="area-one" data-type="7">
                <img src="./images/order_image/icon_shouhouzhong.png" width="20" height="20">
                <div>售后中</div>
            </div>
			
        </div>
		<!-- 上面的Tabbar终结 -->
		<!--占位-->
		<div style="height:62px;width:100%;"></div>
		<!-- 基本数据地区 开始 -->            
        <div id="productContainerDiv">
            <div class="entry-content">
                <ul id="pinterestList">
                	<?php
               // if($currtype !=7) { //非售后
                    $sql_count = " select count(batchcode) as datacount from weixin_commonshop_orders ";
                    $sql_cond = " where isvalid = true and customer_id=" . $customer_id . "  and user_id=" . $user_id;

                    switch ($currtype) {
                        case 1:
                            // 所有订单
                            break;
                        case 2: //待付款
                            $sql_cond = $sql_cond . " and (status = 0 or status=-1) and paystatus = 0";
                            break;
                        case 3: // 待发货
                            $sql_cond = $sql_cond . " and paystatus=1 and status = 0 and sendstatus = 0 ";
                            break;
                        case 4: //待收货
                            $sql_cond = $sql_cond . " and paystatus=1 and status = 0 and sendstatus = 1";
                            break;
                        case 5: //待评价
                            $sql_cond = $sql_cond . " and status = 0 and sendstatus = 2 and is_discuss = 0 ";
                            break;
                        case 7: //售后中
                            $sql_cond = $sql_cond . " and sendstatus > 2 || aftersale_type > 0";
                            break;

                    }

                    //$sql_batchcode .= $sql_cond;
                    $sql_count .= $sql_cond;
              /*  }else{
                    $sql_count = "select count(batchcode) as datacount from weixin_commonshop_order_aftersale";
                }*/
                    $datacounts = 0;
                    $result_count = mysql_query($sql_count) or die("Query sql_count failed : ".mysql_error());
                    if($row_count = mysql_fetch_object($result_count)){
                        $datacounts = $row_count->datacount;
                    }

                    require('orderlist_prods.php');
				?>


				</ul>
            </div>
        </div>
		<!-- 基本数据地区 终结 -->
        
    </div>
   
 </body>		
<script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
<script type="text/javascript" src="./assets/js/amazeui.js"></script>
<script type="text/javascript" src="./js/global.js"></script>
<script type="text/javascript" src="./js/loading.js"></script>
<script src="./js/jquery.ellipsis.js"></script>
<script src="./js/jquery.ellipsis.unobtrusive.js"></script>
<script type="text/javascript">
	var downFlag = false; // 是否加载全部
    var pageNum = 1, pageSize = 5,isMore = true; // 总笔数
    var dataCounts = <?php echo $datacounts;?>;
    var maxPage = Math.ceil(dataCounts/pageSize);
	var user_id_en='<?php echo passport_encrypt($user_id);?>';
    var winWidth = $(window).width();
    var winheight = $(window).height();
    var ctype = '<?php echo $currtype;?>'
    var customer_id_en = '<?php echo $customer_id_en;?>'
	$(function() {
        $(".area-one[data-type='"+ctype+"']").addClass("select");
       $(".area-one").click(function(){
           window.location.href = "orderlist.php?customer_id="+customer_id_en+"&currtype="+$(this).data("type")+"&user_id=<?php echo passport_encrypt($user_id);?>";
       });
	});
      
    function searchData() {
        content = "";

        if (pageNum == maxPage) return;

        $.ajax({
            type: "get",
            url: "orderlist_turnpage.php",
            data: "pagenum="+(pageNum+1)+"&currtype="+ctype,
            success: function(msg){
                $("#pinterestList").append(msg);
            }
        });
		pageNum++;
    }
	
	$(".area-one").click(function(){
		$(".area-one").removeClass("select");
		$(this).addClass("select");
	});
	
	window.onscroll = function (event) {  // 返回顶部
		var intY = $(window).scrollTop();
        if (pageNum == maxPage) return;
        
		var height = document.body.scrollHeight - 100;
        if (intY+winheight-15>height) searchData();
    };
    

    //提醒发货
    function order_remind(batchcode){
        $.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"remind"},function(data){
            showAlertMsg("操作提示",data.msg,"知道了");
        });
    }
    //申请延时收货
    function order_delay(batchcode){

        showConfirmMsg("操作提示","只能延迟一次，是否确定申请延迟收货？","申请","取消",function(){
            $.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"delay"},function(data){
                alert(data.msg);
            });
        });
    }

    //取消订单
    function order_cancel(batchcode){
        showConfirmMsg("操作提示","取消后不可恢复，是否确认取消订单？","取消","不取消",function(){
            $.getJSON("orderlist_operation.php",{batchcode:batchcode,op:"cancel"},function(data){
                alert(data.msg);
            });
        });
    }
    //确认订单
    function order_confirm(batchcode,totalprice){
        showConfirmMsg("操作提示","警：确认完成后，订单将进行结算，订单不再受理退货，退款，如若确定商品无误，请点击确定，否则取消。","确定","取消",function(){
            $.getJSON("orderlist_operation.php",{batchcode:batchcode,totalprice:totalprice,op:"confirm"},function(data){
                alert(data.msg);
                location.reload()
            });
        });
    }
    //点击【查看物流】
    function check_express(expressNum){
        //window.location.href = "http://m.kuaidi100.com/index_all.html?type="+expressNum+"&postid="+expressNum+"#result";
        window.location.href = " http://m.kuaidi100.com/result.jsp?nu="+expressNum;
    }
 //链接到评价页

    
     function toEvaluation(batchcode){
        window.location.href = "orderlist_evaluation.php?batchcode="+batchcode+"&customer_id="+customer_id_en;
    }

    /*
    function toAftersale(batchcode,pid,prvalues){
        location.href='orderlist_aftersale.php?batchcode='+batchcode+"&pid="+pid+"&prvalues="+prvalues+"&customer_id=<?php echo $customer_id_en;?>";
    }
	*/
    function toAftersale(batchcode){
        location.href='orderlist_aftersale.php?batchcode='+batchcode+"&customer_id=<?php echo $customer_id_en;?>";
    }
	
</script>
 <!--悬浮按钮-->
	<?php  include_once('float.php');?>
	<!--悬浮按钮-->
</body>
</html>