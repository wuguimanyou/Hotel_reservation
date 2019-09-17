<?php

header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');  
 
$batchcode = $configutil->splash_new($_GET["batchcode"]);

$expressname = "";
$expressnum = "";

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
	

$query = 'SELECT id,appid,appsecret,access_token FROM weixin_menus where isvalid=true and customer_id='.$customer_id;
 
 $result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 $access_token="";
 $appid="";
 $appsecret = "";
 while ($row = mysql_fetch_object($result)) {
	$keyid =  $row->id ;
	$appid =  $row->appid ;
	$appsecret = $row->appsecret;
	$access_token = $row->access_token;
	break;
 }
 
$query="select transaction_id from weixin_weipay_notifys where isvalid=true and attach='1' and out_trade_no='".$batchcode."'";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
$transid = "";
while ($row = mysql_fetch_object($result)) {
	$transid = $row->transaction_id;
} 
  
  
$query = 'SELECT id,paysignkey FROM weixinpays where isvalid=true and customer_id='.$customer_id." limit 0,1"; 
$appkey ="";
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($result)) {
	$appkey = $row->paysignkey;
}
 
$timestr = time();
$app_signature = "appid=".$appid."&appkey=".$appkey."&deliver_msg=ok&deliver_status=1&deliver_timestamp=".$timestr."&openid=".$fromuser."&out_trade_no=".$batchcode."&transid=".$transid;
//echo $app_signature."<br/>";

$app_signature = SHA1($app_signature);
//echo $app_signature."<br/>";

$MENU_URL="https://api.weixin.qq.com/pay/delivernotify?access_token=".$access_token;
$data = "{\"appid\":\"".$appid."\",\"openid\":\"".$fromuser."\",\"transid\":\"".$transid."\",\"out_trade_no\":\"".$batchcode."\",\"deliver_timestamp\":\"".$timestr."\",\"deliver_status\":\"1\",\"deliver_msg\":\"ok\",\"app_signature\":\"".$app_signature."\",\"sign_method\":\"sha1\"}";
 // echo $data."<br/>";
 
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $MENU_URL); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
$info = curl_exec($ch);
if (curl_errno($ch)) {    
// echo 'Errno'.curl_error($ch);
}

curl_close($ch);
//var_dump($info);
// echo $info."<br/>"; 
$obj=json_decode($info);
  
  if(!empty($obj->errcode)){
		 $errcode =$obj->errcode ;
		 //echo $errorcode;
		 if($errcode==42001||$errcode==40014 ||$errcode==40001){
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
				   $query="update weixin_menus set appid='".$appid."',appsecret='".$appsecret."', access_token = '".$access_token."' where customer_id=".$customer_id;
				   mysql_query($query);
				   
				   //重新生成
				   $MENU_URL="https://api.weixin.qq.com/pay/delivernotify?access_token=".$access_token;
				  $data = "{\"appid\":\"".$appid."\",\"openid\":\"".$fromuser."\",\"transid\":\"".$transid."\",\"out_trade_no\":\"".$batchcode."\",\"deliver_timestamp\":\"".$timestr."\",\"deliver_status\":\"1\",\"deliver_msg\":\"ok\",\"app_signature\":\"".$app_signature."\",\"sign_method\":\"sha1\"}";
				 // echo $data."<br/>";
				  $ch = curl_init(); 
				  curl_setopt($ch, CURLOPT_URL, $MENU_URL); 
				  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
				  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				  curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
				  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				  curl_setopt($ch, CURLOPT_AUTOREFERER, 1); 
				  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
				  $info = curl_exec($ch);
				  if (curl_errno($ch)) {    
				// echo 'Errno'.curl_error($ch);
				  }
				  curl_close($ch);
					 //var_dump($info);
				  // echo $info."<br/>"; 
				  $obj=json_decode($info);
				}else{
				   echo "<script>win_alert('发生未知错误！请联系商家');</script>";
				   return;
				}
		 }
  }
  
  
  $errcode= $obj->errcode;
  if($errcode==0){
     //发货成功
	 $query="update weixin_weipay_notifys set sendstatus=1 where out_trade_no='".$batchcode."' and attach='1'";
	 mysql_query($query);
  }
 

mysql_close($link);

echo "<script>location.href='weipay_detail2.php?customer_id=".$customer_id_en."&batchcode=".$batchcode."';</script>"
  
 
?>
