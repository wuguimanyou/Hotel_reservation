<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
  
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');
mysql_query("SET NAMES UTF8");
require('../common/utility_4m.php');
require('../common/tupian/CreateExpQR.php');
require('../auth_user.php');
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

$query ="select isOpenPublicWelfare from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	   $isOpenPublicWelfare = $row->isOpenPublicWelfare;
	}

$sales=0;
if(!empty($_GET["sales"])){
	$sales=$configutil->splash_new($_GET["sales"]);
}
$op="";
if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
   $keyid=$configutil->splash_new($_GET["keyid"]);
   
   $sql="update weixin_commonshop_products set isvalid=false where id=".$keyid;
   mysql_query($sql);
   
   $customer_ids = $u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
   if($owner_general and !empty($customer_ids)){
	   //删除 4M下面商家的产品
	   $sql="update weixin_commonshop_products set isvalid=false  where customer_id in (".$customer_ids.") and create_type=".$owner_general." and create_parent_id=".$keyid;
	   mysql_query($sql);
	}
}
$new_baseurl = $http_host."/weixinpl/back_commonshop/";  

$keyword="";
/*if(!empty($_POST["keyword"])){
   $keyword=$configutil->splash_new($_POST["keyword"]);
}
$foreign_mark="";
if(!empty($_POST["foreign_mark"])){
   $foreign_mark=$configutil->splash_new($_POST["foreign_mark"]);
}
$search_type_id=-1;
if(!empty($_POST["search_type_id"])){
   $search_type_id=$configutil->splash_new($_POST["search_type_id"]);
}
if(!empty($_GET["search_type_id"])){
   $search_type_id=$configutil->splash_new($_GET["search_type_id"]);
}
$search_other_id=-1;
if(!empty($_POST["search_other_id"])){
   $search_other_id=$configutil->splash_new($_POST["search_other_id"]);
}*/

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


$query="select count(distinct batchcode) as new_order_count from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_order_count = $row->new_order_count;
   break;
}

$query="select sum(totalprice) as today_totalprice from weixin_commonshop_orders where paystatus=1 and sendstatus!=4 and isvalid=true and customer_id=".$customer_id." and year(paytime)=".$year." and month(paytime)=".$month." and day(paytime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $today_totalprice = $row->today_totalprice;
   break;
}
$today_totalprice = round($today_totalprice,2);

$query="select count(1) as new_customer_count from weixin_commonshop_customers where isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_customer_count = $row->new_customer_count;
   break;
}

$query="select count(1) as new_qr_count from promoters where status=1 and isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_qr_count = $row->new_qr_count;
   break;
}


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
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/global.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">


<script type="text/javascript" src="../common/js/jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="../common/js/layer/layer.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<style>
.r_con_table .paixu{
	margin-top:4px;
	width:100%;
	height:27px;
	line-height:27px;
	text-align:center;
	color:blue;
	font-size:18px;
	background:#fff;
	padding:4px 0;
	border-radius: 5px;
}
.r_con_table .paixu:focus{
	border-color:rgba(82,168,236,0.8);
	box-shadow:0 1px 1px rgba(0,0,0,0.075) inset, 0 0 8px rgba(82,168,236,0.6);
	outline:0 none;
}
.ajax_deal{
	position: absolute;
	bottom: 35px;
	right: 7px;
}

</style>
</head>

<body>


<style type="text/css">body, html{background:url(images/main-bg.jpg) left top fixed no-repeat;}</style>
<div class="div_line">
		   <div class="div_line_item" onclick="show_newOrder('<?php echo $customer_id_en; ?>');">
		      今日订单: <span style="padding-left:10px;font-size:18px;font-weight:bold"><?php echo $new_order_count; ?></span>
		   </div>
		   <div class="div_line_item_split"></div>
		   <div class="div_line_item"  onclick="show_todayMoney('<?php echo $customer_id_en; ?>');">
		      今日销售: <span style="padding-left:10px;color:red;font-size:18px;font-weight:bold">￥<?php echo $today_totalprice; ?></span>
		   </div>
		   <div class="div_line_item_split"></div>
		   <div class="div_line_item"  onclick="show_newCustomer('<?php echo $customer_id_en; ?>');">
		       新增客户: <span style="padding-left:10px;font-size:18px;font-weight:bold"><?php echo $new_customer_count; ?></span>
		   </div>
		   <div class="div_line_item_split"></div>
		   <div class="div_line_item"  onclick="show_newQrsell('<?php echo $customer_id_en; ?>');">
		      新增推广员: <span style="padding-left:10px;font-size:18px;font-weight:bold"><?php echo $new_qr_count; ?></span>
		   </div>
		   <div class="div_line_item_split"></div>
		   <?php
		   $sql_stock = "select stock_remind from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
		   $res_stock = mysql_query($sql_stock) or die('Query failed: ' . mysql_error());
		   while ($row_sql_stock = mysql_fetch_object($res_stock)) {
				$stock_remind = $row_sql_stock->stock_remind;		//库存提醒;
			}
  		    $stock_mun=0;
			$stock_pidarr="";
			$query_stock1="select id from weixin_commonshop_products where isvalid=true and storenum<".$stock_remind." and isout=0 and customer_id=".$customer_id;
			//echo $query_stock1;
			$result_stock1 = mysql_query($query_stock1) or die('Query failed: ' . mysql_error());
			$stock_mun1 = mysql_num_rows($result_stock1);
			while ($row_stock1 = mysql_fetch_object($result_stock1)) {
				$stock_pid1 = $row_stock1->id;
				if(!empty($stock_pidarr)){
					$stock_pidarr=$stock_pidarr."_".$stock_pid1;
				}else{
					$stock_pidarr=$stock_pid1;
				}
				
			}
			
			$query_stock2="select id,propertyids,storenum from weixin_commonshop_products where isvalid=true and isout=0 and storenum>".$stock_remind." and customer_id=".$customer_id;
			$result_stock2 = mysql_query($query_stock2) or die('Query failed: ' . mysql_error());
			$stock_mun2=0;
			while ($row_stock2 = mysql_fetch_object($result_stock2)) {
				$stock_pid = $row_stock2->id;			
				$stock_storenum = $row_stock2->storenum;			
				$stock_propertyids = $row_stock2->propertyids;			
				if(!empty($stock_propertyids)){
				   $query_stock3="SELECT * FROM weixin_commonshop_product_prices WHERE storenum<".$stock_remind." and product_id='".$stock_pid."' limit 0,1";
				   //echo  $query_stock3;
				   $result_stock3 = mysql_query($query_stock3) or die('Query failed: ' . mysql_error());
				   $result_stock3_mun1 = mysql_num_rows($result_stock3);
				   while ($row_stock3 = mysql_fetch_object($result_stock3)) {
						$stock_pid2 = $row_stock3->product_id;
					}
				   if($result_stock3_mun1 !=0){
					   $stock_mun2=$stock_mun2 + 1;
					   if(!empty($stock_pidarr)){
							$stock_pidarr=$stock_pidarr."_".$stock_pid2;
						}else{
							$stock_pidarr=$stock_pid2;
						}
				   }				   
				}
			}
			$stock_mun=$stock_mun1+$stock_mun2; 
			
		   ?>
		   
		   <div class="div_line_item"  onclick="show_stock('<?php echo $customer_id_en; ?>','<?php echo $stock_pidarr; ?>');">
		      库存提醒: 已有<span style="padding-left:10px;color:red;font-size:18px;font-weight:bold"><?php echo $stock_mun; ?></span>个商品库存不足了
		   </div>
		</div>
<div id="iframe_page">
	<div class="iframe_content">
			<link href="css/shop.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/shop.js"></script>
	<div class="r_nav">
		<ul>
			<li id="auth_page0" class=""><a href="base.php?customer_id=<?php echo $customer_id_en; ?>">基本设置</a></li>
			<li id="auth_page1" class=""><a href="fengge.php?customer_id=<?php echo $customer_id_en; ?>">风格设置</a></li>
			<li id="auth_page2" class=""><a href="defaultset.php?customer_id=<?php echo $customer_id_en; ?>&default_set=1">首页设置</a></li>
			<li id="auth_page3" class="cur"><a href="product.php?customer_id=<?php echo $customer_id_en; ?>">产品管理</a></li>
			<li id="auth_page4" class=""><a href="order.php?customer_id=<?php echo $customer_id_en; ?>&status=-1">订单管理</a></li>
			<?php if($is_supplierstr){?><li id="auth_page5" class=""><a href="supply.php?customer_id=<?php echo $customer_id_en; ?>">供应商</a></li><?php }?>
			<?php if($is_distribution){?><li id="auth_page6" class=""><a href="agent.php?customer_id=<?php echo $customer_id_en; ?>">代理商</a></li><?php }?>
			<li id="auth_page7" class=""><a href="qrsell.php?customer_id=<?php echo $customer_id_en; ?>">推广员</a></li>
			<li id="auth_page8" class=""><a href="customers.php?customer_id=<?php echo $customer_id_en; ?>">顾客</a></li>
			<li id="auth_page9"><a href="shops.php?customer_id=<?php echo $customer_id_en; ?>">门店</a></li>
			<?php if($isOpenPublicWelfare){?><li id="auth_page10"><a href="publicwelfare.php?customer_id=<?php echo $customer_id_en; ?>">公益基金</a></li><?php }?>
			
		</ul>
	</div>
<div id="products" class="r_con_wrap">
<script language="javascript">$(document).ready(shop_obj.products_list_init);</script>
<div class="control_btn">
	<a href="product_type.php?customer_id=<?php echo $customer_id_en; ?>" class="btn_green btn_w_120">产品分类管理</a>
	<a href="product_pro.php?customer_id=<?php echo $customer_id_en; ?>" class="btn_green btn_w_120">产品属性管理</a>	
	<a href="product_discuss.php?customer_id=<?php echo $customer_id_en; ?>" class="btn_green btn_w_120">产品评论管理</a>	 
	<a href="add_product.php?customer_id=<?php echo $customer_id_en; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>" class="btn_green btn_w_120">添加产品</a>
	<a href="area/product_city.php?customer_id=<?php echo passport_encrypt((string)$customer_id); ?>" class="btn_green btn_w_120">添加产品地区</a>  
	<a href="#search" class="btn_green btn_w_120">产品搜索</a>
	<a href="#store" class="btn_green btn_w_120">校对库存</a>
	<a href="product.php?customer_id=<?php echo $customer_id_en; ?>&sales=1" class="btn_green btn_w_120" onclick="salesShow();">销量排序</a>
	<?php if($product_num==-1){?>
		<span style="display: inline-block;margin-top: 6px;"><span style="color:red">不限制</span>上架商品个数，</span>
	<?php }else{ ?>
		<span style="display: inline-block;margin-top: 6px;">商家可上架<span style="color:red"><?php echo $product_num;?></span>个商品，</span>
	<?php } ?>
	
	<input type="button" class="search_btn" value="导出产品+" onClick="exportProduct();" class="button" style="cursor:hand;padding-left:15px;">
</div>
     <form  id="frm_import" action="../excel/import_excel_store.php?customer_id=<?php echo passport_encrypt((string)$customer_id); ?>" enctype="multipart/form-data" method="post" class="store">
		 <input type=file name="excelfile" style="width:150px;" id="excelfile" />&nbsp;
		 <input type=button class="store_btn" value="导入库存" onclick="importMember();" />&nbsp;
		 <a href="../excel/store_template.xls">下载模板文件</a>
	</form>

	
<form class="search" method="get" action="product.php?customer_id=<?php echo $customer_id_en; ?>">
	
	关键词：<input type="text" id="keyword" name="keyword" value="<?php echo $keyword; ?>" class="form_input" size="15">
	外部标识：<input type="text" name="foreign_mark" id="foreign_mark" value="<?php echo $foreign_mark; ?>" class="form_input" size="15">
	产品分类：<select name="search_type_id" id="search_type_id">
	<option value="">--请选择--</option>
	<?php 
	  $query="select id,name from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id;
	  $result = mysql_query($query) or die('Query failed: ' . mysql_error());
	  while ($row = mysql_fetch_object($result)) {
		   $pt_id = $row->id;
		   $pt_name = $row->name;
	?>
	  <option value="<?php echo $pt_id; ?>" <?php if($pt_id==$search_type_id){?>selected <?php } ?>><?php echo $pt_name; ?></option>
	<?php } ?>
	
	</select>	
	其他属性：<select name="search_other_id" id="search_other_id">
		<option value="-1">--请选择--</option>
		<option value="1" <?php if($search_other_id==1){?>selected <?php } ?>>下架</option>
		<option value="2" <?php if($search_other_id==2){?>selected <?php } ?>>新品</option>		
		<option value="3" <?php if($search_other_id==3){?>selected <?php } ?>>热卖</option>	
	</select>
	<input type="submit" class="search_btn" value="搜索">
</form>
<table width="100%" align="center" border="0" cellpadding="11" cellspacing="0" class="r_con_table">
	<thead>
		<tr>
			<td width="2%" nowrap="nowrap">序号</td>
			<td width="5%" nowrap="nowrap">排序</br>(按降序排序)</td>
			<td width="30%" nowrap="nowrap">名称</td>
			<td width="10%" nowrap="nowrap">属性分类</td>
			<td width="10%" nowrap="nowrap">价格</td>			
			<td width="10%" nowrap="nowrap">销量</td>			
			<td width="13%" nowrap="nowrap">图片</td>			
			<td width="10%" nowrap="nowrap">属性</td>
			<td width="10%" nowrap="nowrap">时间</td>
			<td width="15%" nowrap="nowrap">好评/中评/差评</td>
			<td width="10%" nowrap="nowrap" class="last">操作</td>
		</tr>
	</thead>
	<tbody>
	  <?php
	    $pagenum = 1;

		if(!empty($_GET["pagenum"])){
		   $pagenum = $_GET["pagenum"];
		}
		$start = ($pagenum-1) * 20;
		
		$end = 20; 
		
		
		$query_count="select count(1) as tcount FROM weixin_commonshop_products WHERE isvalid=true AND customer_id=".$customer_id;
		
	    $query2="SELECT id,name,asort_value,type_id,type_ids,orgin_price,now_price,default_imgurl,isnew,createtime,isout,ishot,isnew,good_level,meu_level,bad_level,is_supply_id,create_type,sell_count,is_QR  FROM weixin_commonshop_products WHERE isvalid=true AND customer_id=".$customer_id; 
		$query3="";
		if($_SESSION['is_auth_user']=='yes' && $_SESSION['user_id']){
			
			$query2 = $query2." and (auth_users=".$auth_user_id." or is_supply_id>0)";	//授权用户只能看到自己上传的产品;
			$query_count = $query_count." and (auth_users=".$auth_user_id." or is_supply_id>0)";	//授权用户只能看到自己上传的产品和供应商的产品;
		}
		if($keyword!=""){
		   
		   $query3=$query3." AND name like'%".$keyword."%'";
		}
		if($foreign_mark!=""){
		 
		   $query3=$query3." AND foreign_mark like'%".$foreign_mark."%'";
		}
		if($search_type_id>0){
		  
		   $query3=$query3." AND type_ids like'%".$search_type_id."%'";
		}
		if($search_other_id>0){
		   switch($search_other_id){
		      case 1:
			    
			    $query3=$query3." AND isout=true";
			    break;
			  case 2:
			   
			    $query3=$query3." AND isnew=true";
			    break;
			  case 3:
			   
			    $query3=$query3." AND ishot=true";
			    break;
		   }
		}
		 
	
		$query2=$query2.$query3; 
		//echo $query2;
		$query_count=$query_count.$query3;
		/* 输出数量开始 */

		$rcount_q2=1;
		$result2 = mysql_query($query_count) or die('Query failed: ' . mysql_error());
		while ($row2 = mysql_fetch_object($result2)) {
			$rcount_q2=$row2->tcount;
		 }
		//$rcount_q = mysql_num_rows($result2);
		/* 输出数量结束 */
		
		if($sales==1){
		   $query2=$query2."  order by sell_count desc,id desc limit ".$start.",".$end;
		}else{
			$query2=$query2." order by asort_value desc,id desc limit ".$start.",".$end;
		}
		$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
		
		$supply_id = -1; //供应商user_id
		while ($row2 = mysql_fetch_object($result2)) {
			$p_id=$row2->id;
			$p_name = $row2->name;
			$p_orgin_price = $row2->orgin_price;
			$p_now_price = $row2->now_price;
			$p_isnew= $row2->isnew;
			$p_createtime = $row2->createtime;
			$p_type_id = $row2->type_id;
			$p_isout = $row2->isout;
			$p_isnew= $row2->isnew;
			$p_ishot = $row2->ishot;
			$is_QR = $row2->is_QR;
			$type_ids = $row2->type_ids;
			$asort_value = $row2->asort_value;
			$supply_id = $row2->is_supply_id;
			$create_type = $row2->create_type;
			$sell_count = $row2->sell_count;
		   
		   $query3="select name from weixin_commonshop_types where isvalid=true and id=".$p_type_id;
		   $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
		   $typename="";
		   while ($row3 = mysql_fetch_object($result3)) {
		      $typename = $row3->name;
		   }
		   
		   if(!empty($type_ids)){
		      $tid_arr = explode(",",$type_ids);
			  $typename="";
			  $tlen = count($tid_arr);
			  
			  for($m=0;$m<$tlen;$m++){
			      $tt_id = $tid_arr[$m];
				  if(!empty($tt_id) and is_numeric($tt_id)){
					  $query3="select name from weixin_commonshop_types where isvalid=true and id=".$tt_id;
					  
					  $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
					   while ($row3 = mysql_fetch_object($result3)) {
						  $t_name = $row3->name;
						  $typename = $typename."/".$t_name;
					   }
				   }
			  }
		   }
		   
		   $imgurl = $row2->default_imgurl;
		   if(empty($imgurl)){
			   $query3="select imgurl from weixin_commonshop_product_imgs where isvalid=true and product_id=".$p_id." limit 0,1";
			   $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
			   $imgurl="";
			   while ($row3 = mysql_fetch_object($result3)) {
				  $imgurl = $row3->imgurl;
			   }
		   }
		   $otherstr="";
		   if($p_isout){
		      $otherstr=$otherstr."下架";
		   }
		   if($p_isnew){
		      $otherstr=$otherstr."/新品";
		   }
		   if($p_ishot){
		      $otherstr=$otherstr."/热卖";
		   }
		   
		   $good_level=$row2->good_level;
		   $meu_level = $row2->meu_level;
		   $bad_level = $row2->bad_level;
		   
		   
		   $data= BaseURL."common_shop/jiushop/detail.php?pid=".$p_id."&customer_id=".$customer_id;
		   
		   
			$Query2= "SELECT name,phone,weixin_name,weixin_fromuser FROM weixin_users WHERE isvalid=true AND id=".$supply_id; 
			//echo $query2;
			$Result2 = mysql_query($Query2) or die('Query failed35: ' . mysql_error());
			$supply_username="";
			$supply_userphone="";
			$supply_weixin_fromuser="";
			$supply_username = "";
			while ($Row2 = mysql_fetch_object($Result2)) {
				$supply_username=$Row2->name;
				$supply_userphone = $Row2->phone;
				$supply_weixin_fromuser= $Row2->weixin_fromuser;
				$supply_weixin_name=$Row2->weixin_name;
				$supply_username = "供应商:".$supply_username."(".$supply_weixin_name.")";//供应商名称加昵称
				break;
			}
			
			if($supply_id==-1){ $supply_username ="";}//如果不是供应商上传的产品,则为空;
			$shopSupplyName= new createExpQrUtility();
			//$shopSupplyName->mb_substrgb($user_id,$parent_id,$customer_id,1);	//1:商家后台手动改动关系 2:通过分享建立关系 3:推广二维码扫描建立关系;
			$supply_username = $shopSupplyName->mb_substrgb($supply_username,16);//限制文字长度
	  ?>
		<tr>
		
			<td nowrap="nowrap"><?php echo $p_id; ?><br>
				<a href="supply.php?search_user_id=<?php echo $supply_id; ?>&customer_id=<?php echo $customer_id_en; ?>"><?php echo $supply_username;?></a>
			</td>
			<td style="position: relative;"><input class="paixu" id="<?php echo $p_id; ?>" type="text" value="<?php echo $asort_value; ?>" onblur="change_Sort(<?php echo $p_id; ?>,this)" /></td>
			<td><?php echo $p_name; if($is_QR == 1){ echo ' (券)';} ?></td>
			<td><?php echo $typename; ?></td>
			<td nowrap="nowrap">
				<del>￥<?php echo $p_orgin_price; ?><br></del>￥<?php echo $p_now_price; ?>				</td>
			<td nowrap="nowrap">
				<?php echo $sell_count; ?>				</td>
			<td nowrap="nowrap"><img src="<?php echo "http://".$new_baseurl.$imgurl; ?>" style="width:80px;height:80px;" /></td>
			<td nowrap="nowrap"><?php echo $otherstr;?><br></td>  
			<td nowrap="nowrap"><?php echo $p_createtime; ?></td>
			<td nowrap="nowrap">
			<a href="discuss.php?customer_id=<?php echo $customer_id_en; ?>&pid=<?php echo $p_id; ?>"><?php echo $good_level."/".$meu_level."/".$bad_level; ?></a></td>
			<td class="last" nowrap="nowrap">
			<?php if($_SESSION['is_auth_user']=='no' or ($_SESSION['is_auth_user']=='yes' and $p_isout==1)){ // 如果是授权用户,则需要商家下架后才能编辑 或者 商家才能编辑?>
			      <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2) or ($owner_general==0 and $create_type==3)) or ($create_type==-1) ){ ?>
				    <a href="add_product.php?customer_id=<?php echo $customer_id_en; ?>&product_id=<?php echo $p_id; ?>&pagenum=<?php echo $pagenum; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>"><img src="images/mod.gif" align="absmiddle" alt="修改" title="修改"></a>
				<?php } 
			}?>
				<a href="javascript:;" onclick="showMediaMap('<?php echo $customer_id_en; ?>',<?php echo $p_id; ?>,'<?php echo QRURL."?qrtype=1&customer_id=".$customer_id; ?>&product_id=<?php echo $p_id; ?>&data=<?php echo $data; ?>');" target="_blank"><img src="images/m-ico-4.png" align="absmiddle" alt="产品推广二维码，扫描即可购买" title="产品推广二维码，扫描即可购买"></a>
			<?php if($_SESSION['is_auth_user']=='no' or ($_SESSION['is_auth_user']=='yes' and $p_isout==1)){ // 如果是授权用户,则需要商家下架后才能编辑 或者 商家才能编辑?>
				  <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2) or ($owner_general==0 and $create_type==3)) or ($create_type==-1) ){ ?>
				    <a href="product.php?customer_id=<?php echo $customer_id_en; ?>&keyid=<?php echo $p_id; ?>&op=del" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};"><img src="images/del.gif" align="absmiddle" alt="删除"></a>
				 <?php }
			}?>
			</td>
		</tr>
		<?php } ?>
		</tbody>
</table>
<div class="blank20"></div>

<div class="tcdPageCode"></div>
</div>	
</div>
<div>

<script>
function change_Sort(id,e){
var value=e.value;
var before_val=e.id;
var a=$(e);
//alert(value+'=='+before_val);
if(value == before_val){
return;
}else if(!value){
	alert('请输入排序数字');
	return;
}else if(isNaN(value)){
	alert('输入错误,排序只能是数字');
	return;
}else{
	a.after('<img id="ajax_deal" class="ajax_deal" src="images/loading/ajax_small.gif" />');	
	$.ajax({
		url:'save_asort_value.php',
		dataType:'json',
		data:{'id':id,'val':value},
		success:function(result){
			if(result.code==0){
				$('#ajax_deal').attr('src',"images/loading/s_success.png");
				setTimeout(function(){
					$('#ajax_deal').remove();
				},500);
			}else{
					$('#ajax_deal').attr('src',"images/loading/s_error.png");
					setTimeout(function(){
						$('#ajax_deal').remove();
						
					},500);			
				}
			}
		})

}
}


</script>
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/fenye/fenye.css" media="all">
<script src="../js/fenye/jquery.page.js"></script>
<script type="text/javascript" src="../js/tis.js"></script>
<script>
var pagenum = <?php echo $pagenum ?>;

var rcount_q = <?php echo $rcount_q2?>; 
var end = <?php echo $end ?>;
var sales = <?php echo $sales ?>;
var count =Math.ceil(rcount_q/end);//总页数
//pageCount：总页数
//current：当前页
	$(".tcdPageCode").createPage({
        pageCount:count,
        current:pagenum,
        backFn:function(p){
			var search_type_id = document.getElementById("search_type_id").value;
			//var keyword = 2;
			//var search_type_id = document.getElementById("search_type_id").value;
			//var search_type_id = document.getElementById("search_type_id").value;
			var keyword = '<?php echo $keyword;?>';
			
			var foreign_mark = '<?php echo $foreign_mark;?>';
			var search_other_id = '<?php echo $search_other_id;?>';		
			 if(sales==1){
				
				document.location= "product.php?customer_id=<?php echo $customer_id_en; ?>&sales=1&pagenum="+p+"&search_type_id="+search_type_id+"&keyword="+keyword+"&foreign_mark="+foreign_mark+"&search_other_id="+search_other_id;
			}else{
				
				document.location= "product.php?customer_id=<?php echo $customer_id_en; ?>&pagenum="+p+"&search_type_id="+search_type_id+"&keyword="+keyword+"&foreign_mark="+foreign_mark+"&search_other_id="+search_other_id;
			}
	   }
    });


/*   function prePage(){
     pagenum--;
     document.location= "product.php?customer_id=<?php echo customer_id; ?>&pagenum="+pagenum;
  }
  
  function nextPage(){
     pagenum++;
     document.location= "product.php?customer_id=<?php echo customer_id; ?>&pagenum="+pagenum;
  } */
  
  
  var i;
function showMediaMap(customer_id,product_id,url){
	i = $.layer({
		type : 2,
		shadeClose: true,
		offset : ['10px' , '80px'],
		time : 0,
		iframe : {
			//src : '../common_shop/jiushop/forward.php?type=2&customer_id='+customer_id+'&product_id='+product_id
			src:url
		},
		title : "该产品二维码(扫码即可以购买)",
		//fix : true,
		zIndex : 2,
		border : [5 , 0.3 , '#437799', true],
		area : ['500px','500px'],
		closeBtn : [0,true],
		success : function(){ //层加载成功后进行的回调
			//layer.shift('right-bottom',1000); //浏览器右下角弹出
		},
		end : function(){ //层彻底关闭后执行的回调
			/*$.layer({
				type : 2,
				offset : ['100px', ''],
				iframe : {
					src : 'http://sentsin.com/about/'
				},	
				area : ['960px','500px']
			})*/
		}
	});
}

function importMember(){
     var f_content = document.getElementById("excelfile").value;
	 var fileext=f_content.substring(f_content.lastIndexOf("."),f_content.length)
     fileext=fileext.toLowerCase()
    if (fileext!='.xls')
    {
        alert("对不起，导入数据格式必须是xls格式文件哦，请您调整格式后重新上传，谢谢 ！");
        return false;
    }
	
	document.getElementById("frm_import").submit();
}

function exportProduct(){
     var keyword = document.getElementById("keyword").value;
     var foreign_mark = document.getElementById("foreign_mark").value;
     var search_type_id = document.getElementById("search_type_id").value;
	 var search_other_id = document.getElementById("search_other_id").value;
	 var auth_user_id = <?php echo  $auth_user_id; ?>;
	  var url='/weixin/plat/app/index.php/Excel/commonshop_excel_product/customer_id/<?php echo $customer_id; ?>';
	 if(keyword!="") {
		 
		 url=url+'/keyword/'+keyword;
	 }
	 if(foreign_mark!="") {
		 
		 url=url+'/foreign_mark/'+foreign_mark;
	 }
	 if(search_type_id!="" && search_type_id>0) {
		 
		 url=url+'/search_type_id/'+search_type_id;
	 }
	 if(search_other_id!="" && search_other_id>0) {
		 
		 url=url+'/search_other_id/'+search_other_id;
	 }
	 url=url+'/auth_user_id/'+auth_user_id+'/';
	  //alert(url);
	  goExcel(url,1,'http://<?php echo $http_host;?>/weixinpl/');
}

</script>
<?php 

mysql_close($link);
?>
</div></div></body></html>