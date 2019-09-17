<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php'); //配置
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

$customer_id = -1;
$user_id = -1;
$batchcode = -1;
$action_type = '';
$array = array();
$data = array();
$n = 0;

$customer_id = $_POST["customer_id"];
$user_id = $_POST["user_id"];
$batchcode = $_POST["batchcode"];		//订单号
$action_type = $_POST["action_type"];
$customer_id = 3243;
$user_id = 196282;
// $batchcode = '1962821463136793';
// $action_type = 'qxdd';

switch($action_type){
	case 'qxdd':
		$query = "update weixin_commonshop_orders set status=-1 where isvalid=true and customer_id=".$customer_id." and batchcode=".$batchcode;
		mysql_query($query) or die('query failed'.mysql_error());
		$row = mysql_affected_rows();
		if($row > 0){
			$data['status'] = 1;
		}else{
			$data['status'] = -1;
		}
		echo json_encode($data);
		
		break;
		
	case 'qrsh':
		$query = "update weixin_commonshop_orders set sendstatus=2,confirm_receivetime=now() where isvalid=true and customer_id=".$customer_id." and batchcode=".$batchcode;
		mysql_query($query) or die('query failed'.mysql_error());
		$row = mysql_affected_rows();
		if($row > 0){
			$data['status'] = 1;
		}else{
			$data['status'] = -1;
		}
		echo json_encode($data);
		
		break;
		
}
?>