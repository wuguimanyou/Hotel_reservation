<?php 
require('../logs.php');   
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');

  
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../proxy_info.php');
mysql_query("SET NAMES UTF8");
require('../../../common/utility_4m.php');
require('../../../common/tupian/CreateExpQR.php');
require('../../../auth_user.php');
$u4m = new Utiliy_4m();
$rearr = $u4m->is_4M($customer_id);

//是4m分销
$is_shopgeneral = $rearr[0]  ;
//厂家编号
$adminuser_id = $rearr[1] ;
//是否是厂家总店
$is_samelevel = $rearr[2] ;
//总店模板编号
$general_template_id = $rearr[3] ;
//总店商家编号
$general_customer_id = $rearr[4] ;

//是否本身就是厂家总店
//1：厂家总店； 2：代理商总店
$owner_general = $rearr[5] ;

$orgin_adminuser_id = $rearr[6] ;

$stock_remind = 1;
$query ="select isOpenPublicWelfare,stock_remind from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	   $isOpenPublicWelfare = $row->isOpenPublicWelfare;
	   $stock_remind = $row->stock_remind;
	}

$sales=0;
if(!empty($_GET["sales"])){
	$sales=$configutil->splash_new($_GET["sales"]);
}

if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
   $keyid=$configutil->splash_new($_GET["keyid"]);
   if($op == "del"){
	   $sql="update weixin_commonshop_products set isvalid=false where id=".$keyid;
	   mysql_query($sql);
	   
	   $customer_ids = $u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
	   if($owner_general and !empty($customer_ids)){
		   //删除 4M下面商家的产品
		   $sql="update weixin_commonshop_products set isvalid=false  where customer_id in (".$customer_ids.") and create_type=".$owner_general." and create_parent_id=".$keyid;
		   mysql_query($sql);
		}
   }else if($op == "onsale"){ //上架
	    $sql="update weixin_commonshop_products set isout = 0 where isvalid = true and  id=".$keyid;
		 mysql_query($sql) or die("L65 : query error : ".mysql_error());
   }else if($op == "onsale_m"){ //上架 ： 多条
	    $sql="update weixin_commonshop_products set isout = 0 where isvalid = true and  id in (".$keyid.")";
		 mysql_query($sql) or die("L68 : query error : ".mysql_error());
   }else if($op == "unsale"){ //下架
	    $sql="update weixin_commonshop_products set isout = 1 where isvalid = true and  id=".$keyid;
		 mysql_query($sql) or die("L71 : query error : ".mysql_error());
   }else if($op == "unsale_m"){ //下架 ： 多条
	   $sql="update weixin_commonshop_products set isout = 1 where isvalid = true and  id in (".$keyid.")";
		 mysql_query($sql) or die("L74 : query error : ".mysql_error());
   }
}

$new_baseurl = $http_host;  
$keyword="";
if(!empty($_GET["keyword"])){
   $keyword=$configutil->splash_new($_GET["keyword"]);
}
$foreign_mark="";
if(!empty($_GET["foreign_mark"])){
   $foreign_mark=$configutil->splash_new($_GET["foreign_mark"]);
}
$search_type_id=-1;
if(!empty($_GET["search_type_id"])){
   $search_type_id=$configutil->splash_new($_GET["search_type_id"]);
}
if(!empty($_GET["search_type_id"])){
   $search_type_id=$configutil->splash_new($_GET["search_type_id"]);
}
$search_other_id=-1;
if(!empty($_GET["search_other_id"])){
   $search_other_id=$configutil->splash_new($_GET["search_other_id"]);
}
$search_source = -1;
if(!empty($_GET["search_source"])){
   $search_source=$configutil->splash_new($_GET["search_source"]);
}
$search_supply = -1;
if(!empty($_GET["search_supply"])){
   $search_supply=$configutil->splash_new($_GET["search_supply"]);
}

//新增客户
$new_customer_count =0;
//今日销售
$today_totalprice=0;
//新增订单
$new_order_count =0;
//新增推广员
$new_qr_count =0;

$nowtime = time();
$year = date('Y',$nowtime);
$month = date('m',$nowtime);
$day = date('d',$nowtime);


$is_distribution=0;//渠道取消代理商功能
//代理模式,分销商城的功能项是 266
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scdl' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_distribution=1;
}
$is_supplierstr=0;//渠道取消供应商功能
//供应商模式,渠道开通与不开通
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scgys' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_supplierstr=1;
}


$query="select product_num from customers where isvalid=true and id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  	
while ($row = mysql_fetch_object($result)) {
   $product_num = $row->product_num;//最多上架商品数量
   break;
}
$query="select isout,count(1) as num from weixin_commonshop_products where isout=0 and isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  	
while ($row = mysql_fetch_object($result)) {
   $num = $row->num;//已经上架商品数量
   break;
}
$auth_user_id = -1;
if($_SESSION['is_auth_user']=='yes' && $_SESSION['user_id']){
	$auth_user_id = $_SESSION['user_id'];
}

?>