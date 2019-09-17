<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php');   //解密参数
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

require('../../../common/utility_shop.php');
	
$callback = $configutil->splash_new($_GET["callback"]);


$op =$configutil->splash_new($_GET["op"]);
//1:是更新order_remind，2:是查order_remind的状态，3:查是否有新订单吧
if($op==1){
	
	$keyid=-1;
	$query="select id from weixin_commonshop_orderremind where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed1_weixin_commonshop_orderremind: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$keyid = $row->id;
	}
	if($keyid<0){
		$query="insert into weixin_commonshop_orderremind (customer_id,order_remind,isvalid,order_count,last_record) values(".$customer_id.",0,true,0,0)";
		$result = mysql_query($query) or die('Query failed2_weixin_commonshop_orderremind: ' . mysql_error());
	}
	$ordercount=-1;
	$sql_ordercount="select count(1) as ordercount from weixin_commonshop_orders where customer_id=".$customer_id." and isvalid=true and paystatus=1";
	$re=mysql_query($sql_ordercount) or die('Query sql_ordercount: '.mysql_error());
	while ($ro = mysql_fetch_object($re)) {
		$ordercount= $ro->ordercount;
	}
	if($ordercount>0){
		$query="update weixin_commonshop_orderremind set order_count=".$ordercount.",last_record=".$ordercount." where isvalid=true and customer_id=".$customer_id;
		$result = mysql_query($query) or die('Query failed3_weixin_commonshop_orderremind: ' . mysql_error());
	}		
	
	$order_remind =$configutil->splash_new($_GET["order_remind"]);
	$query_orderremind = "update weixin_commonshop_orderremind set order_remind=".$order_remind." where customer_id=".$customer_id;
	$result = mysql_query($query_orderremind) or die('Query failed_orderremind: ' . mysql_error());
	
	$error =mysql_error();	
	if($order_remind==1){
		echo $callback."([{status:1}";  
	}else{
		echo $callback."([{status:0}"; 
	}
	
	echo "]);";
	echo $callback;
	
}else if($op==2){
	
	$order_remind=-1;
	$query="select order_remind from weixin_commonshop_orderremind where isvalid=true and customer_id=".$customer_id." limit 1";
	$result = mysql_query($query) or die('Query failed2: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$order_remind = $row->order_remind;
	}
	
	$error =mysql_error();
	if($order_remind>0){
		echo $callback."([{status:1}";
		echo "]);";
		echo $callback;
	}else{
		echo $callback."([{status:0}";
		echo "]);";
		echo $callback;
	}
}else if($op==3){
	$query="select count(1) as ordercount from weixin_commonshop_orders where customer_id=".$customer_id." and isvalid=true and paystatus=1";
	$result=mysql_query($query) or die('Query failed3: '.mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$ordercount= $row->ordercount;
	}
	
	if(!empty($_GET['update'])){
		$query="update weixin_commonshop_orderremind set order_count=".$ordercount.",last_record=".$ordercount." where isvalid=true and customer_id=".$customer_id;
		$result = mysql_query($query) or die('Query failed3_weixin_commonshop_orderremind1: ' . mysql_error());
	}
	$query="select last_record from weixin_commonshop_orderremind where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed4: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$last_record = $row->last_record;
	}
	$count=$ordercount-$last_record;
	
	if($count < 0){
		$count=0;
	}
	echo $callback."([{status:1,count:".$count."}";
	echo "]);";
	echo $callback;
}

mysql_close($link);


?>