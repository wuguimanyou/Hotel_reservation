<?php
header("Content-type: text/html; charset=utf-8"); 
define("bug_time", "2015-5-11 12:18:00");  //新旧版本返佣翻倍 时间上判断常量
define("version", "2"); //新旧版确定订单，有楼上bug_time的是版本2没有就是版本1..

require('../config.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

require('../common/utility_shop.php');

$customer_id = $configutil->splash_new($_POST["customer_id"]);
if(empty($customer_id)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：customer_id!\" }";
	return;
}
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
session_start();
$_SESSION["customer_id"] = $customer_id;


//$order_id=$_GET["order_id"];
$batchcode=$configutil->splash_new($_POST["batchcode"]);
$card_member_id = $configutil->splash_new($_POST["card_member_id"]);
$totalprice = $configutil->splash_new($_POST["totalprice"]);
$paystyle = $configutil->splash_new($_POST["paystyle"]);
$createtime = $configutil->splash_new($_POST["createtime"]);
if(empty($batchcode)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：batchcode!\" }";
	return;
}
if(empty($card_member_id)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：card_member_id!\" }";
	return;
}
if(empty($totalprice)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：totalprice!\" }";
	return;
}
if(empty($paystyle)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：paystyle!\" }";
	return;
}
if(empty($createtime)){
	echo "{\"result\":\"-3\" , \"msg\":\"缺少参数：createtime!\" }";
	return;
}
//完成
	   $query = "select fromuser_app,agentcont_type,sendstatus,status from weixin_commonshop_orders where batchcode='".$batchcode."'";
		$result = mysql_query($query) or die('Query failed1: ' . mysql_error());
		$fromuser_app="";
		$agentcont_type=0;
		$sendstatus=0;
		$status=0;	
		while ($row = mysql_fetch_object($result)) {
			$sendstatus = $row->sendstatus;
			$fromuser_app = $row->fromuser_app;
			$agentcont_type = $row->agentcont_type;		//代理结算 0:推广员结算 1:代理结算
			$status = $row->status;		//1:确认完成1
			
		}
		
		$type=-1;
		if($fromuser_app!=""){
			$query = "select type from weixin_users where weixin_fromuser='".$fromuser_app."' and customer_id=".$customer_id;
			$result = mysql_query($query) or die('Query failed2: ' . mysql_error());
			while ($row = mysql_fetch_object($result)) {
				$type = $row->type;
			}
		}
		if($status==1){
			echo '<script language="javascript">alert("代理商已确认订单");</script>';  
		}else{
		//如果为app运营商用户，则进行消费反馈
		if($type==4){
			//file_put_contents ( "log.txt", "mul=消费反馈=1==\r\n", FILE_APPEND );
			//通过接口 反馈给app运营商用户
				$customerid = $customer_id;
				$app_type=6;
				$paymoney=$now_totalprice;
				$score_data = array('fromuser'=>$fromuser_app,'paymoney'=>$paymoney,'type'=>$app_type,'customerid'=>$customerid);
				//file_put_contents ( "log.txt", "mul=消费反馈=2==\r\n", FILE_APPEND );
				$data_url=http_build_query($score_data);
				//file_put_contents ( "log.txt", "mul=消费反馈=3==\r\n", FILE_APPEND );
				$score_url = "http://115.28.160.40:8080/mayidaxiang/invoke/feedbackScore.jsp?";		
				$url=$score_url.$data_url;
				//echo $url;
				//file_put_contents("log.txt", "status=============".$url."=======".date("Y-m-d h:i:sa")."\r\n",FILE_APPEND);
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
				curl_setopt($ch, CURLOPT_POSTFIELDS, "");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 		
				$json = curl_exec($ch); 
				curl_close($ch); 
				//接口end
				//echo $json;
				$WH_status = -1;
				$jsonObj = json_decode($json);    //json解码
				//获取接口返回信息
				foreach ($jsonObj as $v) {
					if(isset($v->status)){
						$WH_status=$v->status;
						
					}
				}
				
				if($WH_status==1){
					$sql="update weixin_commonshop_orders set status=1,fromuser_app='' where batchcode='".$batchcode."'"; 
					mysql_query($sql);
				}else{
					$sql="update weixin_commonshop_orders set status=1 where batchcode='".$batchcode."'"; 
					mysql_query($sql);
				}
				
				
		}else{

            $sql="update weixin_commonshop_orders set status=1,confirm_receivetime=now() where batchcode='".$batchcode."'"; //test 
	        mysql_query($sql);
			$shopUtility_get =  new shopMessage_Utlity();
		   /* if($sendstatus!=4){
			  if(version==2 && strtotime($createtime) < strtotime(bug_time) ){//调旧方法
				 if($agentcont_type==1){
					$shopUtility_get->Confirm_GetMoney_Agent_old($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);
				 }else{
					$shopUtility_get->Confirm_GetMoney_old($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);					   
				 }
			  }else{//新方法
				 if($agentcont_type==1){
					$shopUtility_get->Confirm_GetMoney_Agent($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);
				 }else{
					$shopUtility_get->Confirm_GetMoney($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);

				}
			}

		  } */
			if($sendstatus!=4){
				if($agentcont_type==1){
					//file_put_contents("Confirm_GetMoney_Agent.txt", "1.batchcode=======".var_export($batchcode,true)."\r\n",FILE_APPEND); 
					//file_put_contents("Confirm_GetMoney_Agent.txt", "2.card_member_id=======".var_export($card_member_id,true)."\r\n",FILE_APPEND); 
					//file_put_contents("Confirm_GetMoney_Agent.txt", "3.totalprice=======".var_export($totalprice,true)."\r\n",FILE_APPEND); 
					//file_put_contents("Confirm_GetMoney_Agent.txt", "4.customer_id=======".var_export($customer_id,true)."\r\n",FILE_APPEND); 
					//file_put_contents("Confirm_GetMoney_Agent.txt", "5.paystyle=======".var_export($paystyle,true)."\r\n",FILE_APPEND); 
					$shopUtility_get->Confirm_GetMoney_Agent($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);
				}else{
					$shopUtility_get->Confirm_GetMoney($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);

				}
				
				//增加团队订单数
				$shopUtility_get->Confirm_Team_order($batchcode);
			}
		  //给顾客增加消费积分奖励.1 表示为商城消费
		  $shopUtility_get->AddScore_level($card_member_id,$totalprice,1,$paystyle);
		 
	    }
	   if($isOpenPublicWelfare==1){
		$query="select valuepercent from weixin_commonshop_publicwelfare where isvalid=true and customer_id=".$customer_id; 
		$result = mysql_query($query);
		while ($row = mysql_fetch_object($result)) {
			$valuepercent=$row->valuepercent;
		}
		$batchcode=$configutil->splash_new($_GET["batchcode"]);
		$welfare_user_id=$configutil->splash_new($_GET["user_id"]);
		$totalprice=$configutil->splash_new($_GET["totalprice"]);
		$express_price=$configutil->splash_new($_GET["express_price"]);
		if($express_price>0){$totalprice=$totalprice-$express_price;}//减去运费
		$welfare=$totalprice*$valuepercent;
		$welfare=round($welfare,2);
		
		$query="select id from weixin_commonshop_publicwelfare_log where isvalid=true and customer_id=".$customer_id." and user_id=".$welfare_user_id; 
		$result = mysql_query($query);
		$welfare_id = -1;
		while ($row = mysql_fetch_object($result)) {
			$welfare_id=$row->id;
		}
		//判断此用户是否曾经捐助过 
		if($welfare_id>0){
			$query="select before_score,add_score from weixin_commonshop_publicwelfare_log where isvalid=true and customer_id=".$customer_id." and user_id=".$welfare_user_id." order by id desc limit 0,1";
			$result = mysql_query($query);
			while ($row = mysql_fetch_object($result)) {
				$before_score=$row->before_score;
				$add_score=$row->add_score;
			}
			$new_before_score=$before_score+$add_score;
			$sql="insert into weixin_commonshop_publicwelfare_log(user_id,createtime,isvalid,customer_id,before_score,add_score,batchcode) values(".$welfare_user_id.",now(),true,".$customer_id.",".$new_before_score.",".$welfare.",".$batchcode.")";
			mysql_query($sql);
			//累加至奖金池
		    $query="select publicwelfare from weixin_commonshop_publicwelfare where isvalid=true and customer_id=".$customer_id; 
			$result = mysql_query($query);
			while ($row = mysql_fetch_object($result)) {
				$publicwelfare=$row->publicwelfare;
			}
			$new_publicwelfare=$publicwelfare+$welfare;
			$sql = "update weixin_commonshop_publicwelfare set publicwelfare=".$new_publicwelfare." where customer_id=".$customer_id;
            mysql_query($sql);
		}else{
			$sql="insert into weixin_commonshop_publicwelfare_log(user_id,createtime,isvalid,customer_id,before_score,add_score,batchcode) values(".$welfare_user_id.",now(),true,".$customer_id.",0,".$welfare.",".$batchcode.")";
			mysql_query($sql);
			//累加至奖金池
			$query="select publicwelfare from weixin_commonshop_publicwelfare where isvalid=true and customer_id=".$customer_id; 
			$result = mysql_query($query); 
			while ($row = mysql_fetch_object($result)) {
				$publicwelfare=$row->publicwelfare;
			}
			$new_publicwelfare=$publicwelfare+$welfare;
			$sql = "update weixin_commonshop_publicwelfare set publicwelfare=".$new_publicwelfare." where customer_id=".$customer_id;
            mysql_query($sql);
			}
		}
	}
echo "{\"result\":\"1\" , \"msg\":\"订单确认成功！\" }";
mysql_close($link);
?>
