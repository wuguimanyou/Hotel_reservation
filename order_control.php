<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]

require('../common/utility_shop.php');  //商城方法
date_default_timezone_set('PRC');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');



$resultArr = array(); //用于JSON返回的结果

$op = $configutil->splash_new($_GET["op"]);
$batchcode = $configutil->splash_new($_GET["batchcode"]);
$username = $_SESSION['username'];
$role = $configutil->splash_new($_GET["user_role"]);
$roletypeStr = "平台";
if($role == 2){
	$username = $_SESSION['supplier_Acount'];
	$roletype = $_SESSION['user_roletype']; //1 ：代理商 ； 3 ：供应商
	if($roletype == 1){
		$roletypeStr = "代理商";
	}else if($roletype == 3){
		$roletypeStr = "供应商";
	}
}
$query="select customer_id,supply_id from weixin_commonshop_orders where isvalid = true  and batchcode = '".$batchcode."'";
$result = mysql_query($query);
$customer_id = mysql_result($result,0,0);
$supply_id = mysql_result($result,0,1);

$shopmessage= new shopMessage_Utlity();
if($op == 1){ //更新自动收货时间
	$days = $configutil->splash_new($_GET["days"]);
	$is_delay = $configutil->splash_new($_GET["is_delay"]);
	if(empty($days) || $days <= 0){
		$days = 3; //默认延后3天
	}
	if(!empty($batchcode) && !empty($days)){
		if($is_delay == 1){
			$query = "update weixin_commonshop_orders set auto_receivetime = DATE_ADD(auto_receivetime, INTERVAL ".$days." DAY ),is_delay = 2 where isvalid = true and sendstatus = 1 and batchcode = '".$batchcode."'";
		}else{
			$query = "update weixin_commonshop_orders set auto_receivetime = DATE_ADD(auto_receivetime, INTERVAL ".$days." DAY ) where isvalid = true and sendstatus = 1 and batchcode = '".$batchcode."'";
		}
		mysql_query($query);
		
		$query = "select auto_receivetime from weixin_commonshop_orders where isvalid = true and sendstatus = 1 and batchcode = '".$batchcode."'";
		$result = mysql_query($query);
		$receivetime = mysql_result($result,0,0);
		$resultArr["receivetime"] = $receivetime;
		
		$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
			values('".$batchcode."',6,'".$roletypeStr."更新的订单的自动收货日期为".$receivetime."','".$username."',now(),1)";
		mysql_query($query);
		
		if($is_delay == 1){
			$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
			$result = mysql_query($query);
			$fromuser = mysql_result($result,0,0);
			$content = "订单编号：".$batchcode.",商家已处理了您的延迟收货申请，当前自动收货时间为".$receivetime;
			$shopmessage->SendMessage($content,$fromuser,$customer_id);
		}
	}
}else if($op == 2){//同意或驳回换货申请
	$status = $configutil->splash_new($_GET["status"]);
	$reason = $configutil->splash_new($_GET["reason"]);
	if(!empty($batchcode) && !empty($status)){
		
		$query_orders = "select return_type,sendstatus from weixin_commonshop_orders where isvalid=true and batchcode = ".$batchcode." limit 0,1";
		$result_orders  = mysql_query($query_orders);
		$return_type = 0;
		$sendstatus = 0;
		if($row_orders = mysql_fetch_object($result_orders)){
			$return_type = $row_orders -> return_type;
			$sendstatus = $row_orders -> sendstatus;
		}
		
		$tip_str = "退货申请";
		switch($sendstatus){
			case 3:
				$tip_str = "退货申请";
				
				switch($return_type){
					case 0:
						$tip_str .= "[仅退款]";
						break;				
					case 1:
						$tip_str .= "[退货]";
						break;
					case 2:
						$tip_str .= "[换货]";
						break;					
				}	
				
				break;				
			case 5:
				$tip_str = "退款申请";
				break;				
		}
		
		
		$status_str = $status == 1 ? "同意" : "驳回";
		
		$st = $status == 1 ? 2 : 3;
		//修改订单表状态 
		if($status == 1){ //同意
			$query = "update weixin_commonshop_orders set return_status = ".$st." where isvalid = true and return_status = 0 and batchcode = '".$batchcode."'";
		}else{ //驳回 , 驳回退货申请后，将订单状态重新设置为已发货状态
			$query = "update weixin_commonshop_orders set return_status = ".$st." , sendstatus = 1 where isvalid = true and sendstatus = 3 and return_status = 0 and batchcode = '".$batchcode."'";
		}
		mysql_query($query);
		
		/*//添加驳回记录
		$query = "insert into weixin_commonshop_order_rejects(batchcode,remark,createtime,isvalid,operation_role,record_type,images,account,reason) 
		values('".$batchcode."','".$reason."',now(),1,1,0,'".$images."','".$re_price."','".$reason."')";
		mysql_query($query);*/
		//添加订单日志
		$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
			values('".$batchcode."',9,'".$roletypeStr."".$status_str."用户的退换货申请".($status == 2 ? ",原因：".$reason.";订单更新为已发货状态。" : "")."','".$username."',now(),1)";
		mysql_query($query);
		
		
		$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
		$result = mysql_query($query);
		$fromuser = mysql_result($result,0,0);
		
		
		$content = "订单编号：".$batchcode.",商家已".$status_str."您的".$tip_str."".($status == 2 ? ",原因：".$reason : ",备注：".$reason."");
		$query_address="select location_p,location_c,location_a,address,zipcode,name,phone,tel,comment from weixin_commonshop_returnaddress where customer_id='".$customer_id."' and supplier_id='".$supply_id."'";
			$result_address = mysql_query($query_address) or die('Query failed: ' . mysql_error());
			$location_p="";
			$location_c="";
			$location_a="";
			$address="";
			$zipcode="";
			$name="";
			$phone="";
			$tel="";
			$comment="";
			while ($row = mysql_fetch_object($result_address)) {
				$location_p = $row->location_p;
				$location_c	= $row->location_c;
				$location_a	= $row->location_a;
				$address	= $row->address;
				$zipcode	= $row->zipcode;
				$name	= $row->name;
				$phone	= $row->phone;
				$tel	= $row->tel;
				$comment	= $row->comment;
			}
			$content_address="退货地址:\n".$location_p."".$location_c."".$location_a."".$address."\n收件人:".$name;
			if(strlen($zipcode."")>0){
				$content_address=$content_address."\n邮编:".$zipcode;
			}
			if(strlen($phone."")>0){
				$content_address=$content_address."\n手机:".$phone;
			}
			if(strlen($tel."")>0){
				$content_address=$content_address."\n座机:".$tel;
			}
			if(strlen($comment."")>0){
				$content_address=$content_address."\n备注:".$comment;
			}
						if($status == 1){
				$content=$content."\n".$content_address;
			}
		$shopmessage->SendMessage($content,$fromuser,$customer_id);
	
	}
}else if($op == 3){ //同意或驳回退款申请
	$status = $configutil->splash_new($_GET["status"]);
	$reason = $configutil->splash_new($_GET["reason"]);
	
	if(!empty($batchcode) && !empty($status)){
		
		$status_str = $status == 1 ? "同意" : "驳回";
		
		
		$st = $status == 1 ? 8 : 9;
		//修改订单表状态 
		if($status == 1){ //同意
			$query = "update weixin_commonshop_orders set return_status = ".$st." where isvalid = true and sendstatus = 5  and batchcode = '".$batchcode."'";
		}else{ //驳回 , 驳回退款后，将订单状态重新设置为未发货
			$query = "update weixin_commonshop_orders set return_status = ".$st." , sendstatus = 0 where isvalid = true  and sendstatus = 5 and batchcode = '".$batchcode."'";
		}
		mysql_query($query);
		
		//添加订单日志
		$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
			values('".$batchcode."',11,'".$roletypeStr."".$status_str."用户的退款申请".($status == 2 ? ",原因：".$reason.";订单更新为未发货状态。" : "")."','".$username."',now(),1)";
		mysql_query($query);
		
		$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
		$result = mysql_query($query);
		$fromuser = mysql_result($result,0,0);
		
		$content = "订单编号：".$batchcode."商家已".$status_str."您的退款申请".($status == 2 ? ",原因：".$reason : ",在处理中 ... ");
		$shopmessage->SendMessage($content,$fromuser,$customer_id);
	}
}else if($op == 4){ //商家确认已收到退货
	
	$account = $configutil->splash_new($_GET["return_account"]);
	$remark = $configutil->splash_new($_GET["remark"]);

	if($account > 0){
		$query = "update weixin_commonshop_orders set return_status = 6,return_account = ".$account." where isvalid = true and batchcode = '".$batchcode."'";
	}else{
		$query = "update weixin_commonshop_orders set return_status = 6 where isvalid = true and batchcode = '".$batchcode."'";
	}
	mysql_query($query);
	
	
	$log_content = $roletypeStr."已确认收到退货.";
	if($account > 0){
		$log_content = $log_content. ",确认可退款金额为：".$account;
		if(!empty($remark)){
			$log_content = $log_content.",备注：".$remark;
		}
	}
	
	//添加订单日志
	$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
		values('".$batchcode."',14,'".$log_content."','".$username."',now(),1)";
	mysql_query($query);
	
	$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
	$result = mysql_query($query);
	$fromuser = mysql_result($result,0,0);
	
	$content = "订单编号：".$batchcode.",商家已确认收到您的退货!";
	if($account > 0){
		$content = $content."确认可退款金额为：".$account;
		if(!empty($remark)){
			$content = $content.",商家留言：".$remark;
		}
	}
	//echo $content ;
	//echo $fromuser;
	$shopmessage->SendMessage($content,$fromuser,$customer_id);
}else if($op == 5){ //审批售后维权申请
	$status = $configutil->splash_new($_GET["status"]);
	$reason = $configutil->splash_new($_GET["reason"]);
	
	if(!empty($batchcode) && !empty($status)){
		
		$status_str = $status == 1 ? "同意" : "驳回";
		
		$st = $status == 1 ? 2 : 3;
		//修改订单表状态 
		if($status == 1){ //同意
			$query = "update weixin_commonshop_orders set aftersale_state = ".$st." where isvalid = true   and batchcode = '".$batchcode."'";
		}else{ //驳回 , 驳回退款后，将订单状态重新设置为未发货
			$query = "update weixin_commonshop_orders set aftersale_state = ".$st.",aftersale_reason= '".$reason."' where isvalid = true and batchcode = '".$batchcode."'";
		}
		mysql_query($query);
		
		//添加订单日志
		$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
			values('".$batchcode."',19,'".$roletypeStr."".$status_str."用户的维权申请".($status == 2 ? ",原因：".$reason : "")."','".$username."',now(),1)";
		mysql_query($query);
		
		$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
		$result = mysql_query($query);
		$fromuser = mysql_result($result,0,0);
		
		$content = "订单编号:".$batchcode.";商家已".$status_str."您的售后申请".($status == 2 ? ",原因：".$reason.",正在处理中 ... " : "");
		$shopmessage->SendMessage($content,$fromuser,$customer_id);
	}
}else if($op == 6){ //处理维权
	$query = "update weixin_commonshop_orders set aftersale_state = 4 where isvalid = true and aftersale_state = 2 and batchcode = '".$batchcode."'";
	mysql_query($query);
	
	//添加订单日志
	$query = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid) 
		values('".$batchcode."',20,'".$roletypeStr."处理了用户的维权申请。','".$username."',now(),1)";
	mysql_query($query);
	
	$query = "select weixin_fromuser from weixin_users where id  = (select user_id from weixin_commonshop_orders where isvalid = true and batchcode = '".$batchcode."' limit 0,1)";
	$result = mysql_query($query);
	$fromuser = mysql_result($result,0,0);
	
	$content = "订单编号:".$batchcode.";商家已处理了您的售后申请";
	$shopmessage->SendMessage($content,$fromuser,$customer_id);
}

$error = mysql_error();
//echo $error."<br/>";
mysql_close($link);

$resultArr["result"] = empty($error) ? 1 : 0;
$resultArr["msg"] = empty($error) ? "操作成功" : $error;
echo json_encode($resultArr);
?>