<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
require('../common/utility_shop.php');
$link =mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
include_once("../log_.php");
$log_ = new Log_();
$log_name="./notify_url_order.log";//log文件路径

/*搜索条件获取开始*/
$Url = "order.php?customer_id=".$customer_id_en;
if(!empty($_GET["pagenum"])){
    $pagenum = $configutil->splash_new($_GET["pagenum"]);
	$Url =$Url."&pagenum=".$pagenum;
}
if(!empty($_GET["search_money"])){
    $search_money = $configutil->splash_new($_GET["search_money"]);
	$Url =$Url."&search_money=".$search_money;
}
if(!empty($_GET["search_status"])){
    $search_status = $configutil->splash_new($_GET["search_status"]);
	$Url =$Url."&search_status=".$search_status;
}
if(!empty($_GET["search_sendstatus"])){
    $search_sendstatus = $configutil->splash_new($_GET["search_sendstatus"]);
	$Url =$Url."&search_sendstatus=".$search_sendstatus;
}
if(!empty($_GET["search_paystyle"])){
    $search_paystyle = $configutil->splash_new($_GET["search_paystyle"]);
	$Url =$Url."&search_paystyle=".$search_paystyle;
}
if(!empty($_GET["pagesize"])){
    $pagesize = $configutil->splash_new($_GET["pagesize"]);
	$Url =$Url."&pagesize=".$pagesize;
}
if(!empty($_GET["begintime"])){
    $begintime = $configutil->splash_new($_GET["begintime"]);
	$Url =$Url."&begintime=".$begintime;
}
if(!empty($_GET["endtime"])){
    $endtime = $configutil->splash_new($_GET["endtime"]);
	$Url =$Url."&endtime=".$endtime;
}
if(!empty($_GET["pay_begintime"])){
    $pay_begintime = $configutil->splash_new($_GET["pay_begintime"]);
	$Url =$Url."&pay_begintime=".$pay_begintime;
}
if(!empty($_GET["pay_endtime"])){
    $pay_endtime = $configutil->splash_new($_GET["pay_endtime"]);
	$Url =$Url."&pay_endtime=".$pay_endtime;
}
if(!empty($_GET["search_name_type"])){
    $search_name_type = $configutil->splash_new($_GET["search_name_type"]);
	$Url =$Url."&search_name_type=".$search_name_type;
}
if(!empty($_GET["orgin_from"])){
    $orgin_from = $configutil->splash_new($_GET["orgin_from"]);
	$Url =$Url."&orgin_from=".$orgin_from;
}
if(!empty($_GET["search_name"])){
    $search_name = $configutil->splash_new($_GET["search_name"]);
	$Url =$Url."&search_name=".$search_name;
}
if(!empty($_GET["search_batchcode"])){
    $search_batchcode = $configutil->splash_new($_GET["search_batchcode"]);
	$Url =$Url."&search_batchcode=".$search_batchcode;
}
if(!empty($_GET["search_order_ascription"])){
    $search_order_ascription = $configutil->splash_new($_GET["search_order_ascription"]);
	$Url =$Url."&search_order_ascription=".$search_order_ascription;
}
/*搜索条件获取结束*/

$batchcode = -1;  //订单号
if($_GET["batchcode"] != ""){
	(int)$batchcode = $configutil->splash_new($_GET["batchcode"]); 
}
$refund = 0;  //订单号
if($_GET["totalprice"] != ""){
	(float)$refund = $configutil->splash_new($_GET["totalprice"]); 
}
$query="select sendstatus,paystyle,card_member_id,user_id from weixin_commonshop_orders where batchcode=".$batchcode." limit 0,1";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$card_member_id = -1;
$buyer_user_id=-1;
while ($row = mysql_fetch_object($result)) {
	$sendstatus = $row->sendstatus;
	$paystyle = $row->paystyle;
	$card_member_id = $row->card_member_id;
	$buyer_user_id = $row->user_id;

}
$retype = $configutil->splash_new($_GET["retype"]);

$username = $_SESSION['username'];
$shopmessage= new shopMessage_Utlity();
if($retype == 0 && $sendstatus !=6){ //退款
	if($paystyle != '微信支付'){
		$refunds="insert into weixin_commonshop_refunds (customer_id,batchcode,refund,isvalid,createtime) values(".$customer_id.",'".$batchcode."',".$refund.",true,now())";
		mysql_query($refunds) or die (" L20 : QUERY_refunds ERROR : ".mysql_error());
		
	}
	if($sendstatus == 3){ //已发货后的申请退货（仅退款） ，更新为 4 ， return_status = 1
		$orders="update weixin_commonshop_orders set sendstatus=4,return_status = 1 where batchcode='".$batchcode."'";
	}else{ //未发货的申请退款 更新为 6
		$orders="update weixin_commonshop_orders set sendstatus=6 where batchcode='".$batchcode."'";
	}
	//$orders="update weixin_commonshop_orders set sendstatus=6 where batchcode='".$batchcode."'";
	mysql_query($orders) or die (" L22 : QUERY_orders ERROR : ".mysql_error());
	
	
	//退款扣佣金
	$query = "select remark,reward,user_id,card_member_id,level_name,own_user_name,id_new from weixin_commonshop_order_promoters where isvalid=true and paytype=0 and batchcode=".$batchcode;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$money = $row->reward;
		$user_id = $row->user_id;
		$card_member_id = $row->card_member_id;
		$level_name = $row->level_name;
		$own_user_name = $row->own_user_name;
		$id_new = $row->id_new;
		$remark = $level_name.$own_user_name."退款扣除:".$money;	
		//扣佣金
		$query2="select id from weixin_qr_infos where type=1 and foreign_id=".$user_id." and user_type=1 limit 0,1";
		$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
		$qr_info_id=-1;
		while ($row2 = mysql_fetch_object($result2)) {
			$qr_info_id = $row2->id;
		}
		if($qr_info_id>0){
			$sql="update weixin_qrs set reward_money= reward_money-".$money." where qr_info_id=".$qr_info_id;
			mysql_query($sql);
		}	
		//更改佣金表状态		
		$sql = "update weixin_commonshop_order_promoters set do_time=now(),paytype=4 where id_new=".$id_new;
		mysql_query($sql);
		 //添加信息提醒
		$query5="select weixin_fromuser from weixin_users where id=".$user_id." limit 0,1";
		$result5 = mysql_query($query5) or die('Query failed: ' . mysql_error());
		$parent_fromuser="";
		while ($row5 = mysql_fetch_object($result5)) {
			 $parent_fromuser= $row5->weixin_fromuser;
			 break;
		}
		$remark=addslashes($remark);
		//$content = "买家退货，您于".date( "Y-m-d H:i:s")." 扣除￥".$remark."元";
		
		$content = "亲，您的佣金 -".$money."\r\n".
							"来源：【退款】\n".
							"身份：【".$level_name."】\n".
							"顾客：".$own_user_name."\n".
							"时间：<".date( "Y-m-d H:i:s").">";
		
		$shopmessage->SendMessage($content,$parent_fromuser,$customer_id);
	}
	
	//添加日志记录，给退款用户发送消息
	
	$query_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
	values('".$batchcode."',12,'平台退款，退款金额：".$refund."','".$username."',now(),1)";
	mysql_query($query_logs) or die("L77 query error  : ".mysql_error());
	//$content = "订单：".$batchcode."商家已退款，退款金额：".$refund.",请注意查收。";
	
	$content = "亲，您有一笔订单【已退款】\r\n".
							"订单：".$batchcode."\n".
							"金额：".$refund." 元\n".
							"类型：微信零钱\n".
							"时间：<".date( "Y-m-d H:i:s").">";
	
	$query = "select weixin_fromuser from weixin_users where id  =".$buyer_user_id." limit 0,1";
	$result = mysql_query($query)or die("L83 query error  : ".mysql_error());
	$fromuser = "";
	if($row = mysql_fetch_object($result)){
		$fromuser = $row->weixin_fromuser;
	}
	$shopmessage->SendMessage($content,$fromuser,$customer_id);
	//退款加库存减销量
	$shopmessage->Sales_And_Stores($batchcode,$customer_id,2);
}else if($retype == 1){ //退货后退款
	$query="select pid,rcount,prvalues,sendstatus,return_type,user_id from weixin_commonshop_orders where isvalid=true and batchcode='".$batchcode."'"; 
	$result = mysql_query($query) or die('Query failed3: ' . mysql_error());
	$pid=-1;
	$rcount = 0;
	$prvalues="";
	$buyer_user_id = 0;
	 while ($row = mysql_fetch_object($result)) {
		$pid = $row->pid;
		$rcount = $row->rcount;
		$sendstatus= $row->sendstatus;
		$prvalues= $row->prvalues;
		$return_type = $row->return_type;
		$buyer_user_id = $row->user_id;
		
		//退款不加回产品库存
		if($return_type == 1){ //只有退货操作中的退货项才加回产品库存 , 放循环里，订单如有购买多件商品，多件商品都需要加回库存
			$prvalues= rtrim($prvalues,"_");
			if(!empty($prvalues)){
				$sql="update weixin_commonshop_product_prices set storenum= storenum+".$rcount." where product_id=".$pid." and proids='".$prvalues."'";
				mysql_query($sql);
			}else{
				$sql="update weixin_commonshop_products set storenum= storenum+".$rcount." where id=".$pid;
				mysql_query($sql);
			}
		}
	 }
	 if($sendstatus == 3){
		//退货完成
		$sql = "update weixin_commonshop_orders set sendstatus=4 where batchcode='".$batchcode."'";
		mysql_query($sql);

		$log_->log_result($log_name,"退货开始 sql======".$sql);

		$shopUtility_back =  new shopMessage_Utlity();
		$log_->log_result($log_name,"退货进入card_member_id-------".$card_member_id);
		$log_->log_result($log_name,"退货进入totalprice-------".$refund);
		$log_->log_result($log_name,"退货进入customer_id-------".$customer_id);
		$log_->log_result($log_name,"退货进入paystyle-------".$paystyle);
		$log_->log_result($log_name,"退货进入2-------".$batchcode);
		$shopUtility_back->Back_GetMoney($batchcode,$card_member_id,$refund,$customer_id,$paystyle);
	 }
	 
	
	
	$query_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
	values('".$batchcode."',12,'平台退货退款，退款金额：".$refund."','".$username."',now(),1)";
	mysql_query($query_logs) or die("L136 query error  : ".mysql_error());
	
	//$content = "订单：".$batchcode."商家已完成退货，退款金额：".$refund.",请注意查收。";
	
	$content = "亲，您有一笔订单【已退款】\r\n".
							"订单：".$batchcode."\n".
							"金额：".$refund." 元\n".
							"类型：微信零钱\n".
							"时间：<".date( "Y-m-d H:i:s").">";

	$query = "select weixin_fromuser from weixin_users where id  = ".$buyer_user_id." limit 0,1";
	$result = mysql_query($query) or die("L208 query error  : ".mysql_error());
	$fromuser = "";
	if($row = mysql_fetch_object($result)){
		$fromuser = $row->weixin_fromuser;
	}
	
	$shopmessage->SendMessage($content,$fromuser,$customer_id);
}
mysql_close($link);	
//return;

header("Location: ".$Url); 	
exit;
?>