<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');

mysql_query("SET NAMES UTF8");
require('../auth_user.php');
require('../common/utility_4m.php');


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

//1：厂家总店； 2：代理商总店
$owner_general = $rearr[5] ;

$orgin_adminuser_id = $rearr[6] ;


$query ="select isOpenPublicWelfare from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	   $isOpenPublicWelfare = $row->isOpenPublicWelfare;
	}
 
$productpro_id=-1;
if(!empty($_GET["productpro_id"])){
   $productpro_id=$configutil->splash_new($_GET["productpro_id"]);
}
$productpro_name="";  
if($productpro_id>0){
   $query="select name,parent_id from weixin_commonshop_pros where isvalid=true and  id=".$productpro_id;
   $result = mysql_query($query) or die('Query failed: ' . mysql_error());
   while ($row = mysql_fetch_object($result)) {
       $productpro_name = $row->name;
   }
}
$op="";
if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
    $customer_ids = $u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
    if($op=="del"){
       $id=$configutil->splash_new($_GET["id"]);
		$query="select id,parent_id from weixin_commonshop_pros where isvalid=true and customer_id=".$customer_id." and supply_id<0 and id=".$id;
		$p_parent_id = -1;
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());
		while ($row = mysql_fetch_object($result)) {
		   $p_parent_id = $row->parent_id;
		}
		if($p_parent_id<0){	//是父类则删除子类的属性
				$query="select id from weixin_commonshop_pros where isvalid=true and customer_id=".$customer_id." and supply_id<0 and parent_id=".$id;
				$p_id = -1;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
				$p_id = $row->id;
				$sql = "DELETE FROM weixin_commonshop_product_prices WHERE proids like '%\_".$p_id."' or proids like '%\_".$p_id."\_%' or proids like '".$p_id."\_%' or proids='".$p_id."'";
				mysql_query($sql);
				}
	   }else{			//删除单个子类属性
		   $sql = "DELETE FROM weixin_commonshop_product_prices WHERE proids like '%\_".$id."' or proids like '%\_".$id."\_%' or proids like '".$id."\_%' or proids='".$id."'";
		   mysql_query($sql);//删除该属性下面的产品分类价格
	   }
	  
	   mysql_query("update weixin_commonshop_pros set isvalid=false where id=".$id);
	   
	   
	  
	   //echo "customerids========".$customer_ids."<br/>";
	   //echo "owner_general========".$owner_general."<br/>";
	   if($owner_general and !empty($customer_ids)){
		   //删除 4M下面商家的 属性
		   $sql="update weixin_commonshop_pros set isvalid=false  where customer_id in (".$customer_ids.") and create_type=".$owner_general." and create_parent_id=".$id;
		  // echo "sql========".$sql."<br/>";
		   mysql_query($sql);
		}
	   
   }
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
?>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/global.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<script type="text/javascript" src="js/product.js"></script>
</head>

<body>

<style type="text/css">body, html{background:url(images/main-bg.jpg) left top fixed no-repeat;}</style>
<div id="iframe_page">
	<div class="iframe_content">
	<link href="css/shop.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="js/shop.js"></script>
	
	<div class="r_nav">
		<ul>
			<li id="auth_page0" class=""><a href="base.php?customer_id=<?php echo $customer_id; ?>">基本设置</a></li>
			<li id="auth_page1" class=""><a href="fengge.php?customer_id=<?php echo $customer_id; ?>">风格设置</a></li>
			<li id="auth_page2" class=""><a href="defaultset.php?customer_id=<?php echo $customer_id; ?>&default_set=1">首页设置</a></li>
			<li id="auth_page3" class="cur"><a href="product.php?customer_id=<?php echo $customer_id; ?>">产品管理</a></li>
			<li id="auth_page4" class=""><a href="order.php?customer_id=<?php echo $customer_id; ?>&status=-1">订单管理</a></li>
			<?php if($is_supplierstr){?><li id="auth_page5" class=""><a href="supply.php?customer_id=<?php echo $customer_id; ?>">供应商</a></li><?php }?>
			<?php if($is_distribution){?><li id="auth_page6" class=""><a href="agent.php?customer_id=<?php echo $customer_id; ?>">代理商</a></li><?php }?>
			<li id="auth_page7" class=""><a href="qrsell.php?customer_id=<?php echo $customer_id; ?>">推广员</a></li>
			<li id="auth_page8" class=""><a href="customers.php?customer_id=<?php echo $customer_id; ?>">顾客</a></li>
			<li id="auth_page9"><a href="shops.php?customer_id=<?php echo $customer_id; ?>">门店</a></li>
			<?php if($isOpenPublicWelfare){?><li id="auth_page10" class=""><a href="publicwelfare.php?customer_id=<?php echo $customer_id; ?>">公益基金</a></li><?php }?>
		</ul>
	</div>
   <div id="products" class="r_con_wrap">
   <script language="javascript">$(document).ready(shop_obj.products_property_init);</script>
     <div class="property">
	   <div class="m_lefter">
		  <dl>
		        <?php 
				$query="select id,name,parent_id,create_type from weixin_commonshop_pros where isvalid=true and parent_id=-1 and customer_id=".$customer_id." and supply_id<0";
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			    while ($row = mysql_fetch_object($result)) {
				   $parent_id = $row->id;
				   $pname = $row->name;
				   $create_type = $row->create_type;
				?>
					<dd>
						<div class="list">
						    <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2)) or ($create_type==3)){ ?>
								<a href="product_pro.php?customer_id=<?php echo $customer_id; ?>&productpro_id=<?php echo $parent_id; ?>" title="修改">
								 <img src="images/mod.gif" align="absmiddle">
								</a>
								<a href="product_pro.php?customer_id=<?php echo $customer_id; ?>&op=del&id=<?php echo $parent_id; ?>" title="删除" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};">
								<img src="images/del.gif" align="absmiddle">
								</a>
							<?php } ?>
							<?php echo $pname; ?>
						</div>
						<ul>
						   <?php 
						     $query2 = "select id,name,create_type from weixin_commonshop_pros where isvalid=true and parent_id=".$parent_id;
							 $result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
			                 while ($row2 = mysql_fetch_object($result2)) {
							     $p_id=$row2->id;
								 $p_name =$row2->name;
							     $create_type = $row2->create_type;
						   ?>
							<li>
								<div class="title"><img src="images/jt.gif"><?php echo $p_name; ?></div>
								 <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2)) or ($create_type==3)){ ?>
									<div class="opt"><a href="product_pro.php?customer_id=<?php echo $customer_id; ?>&op=del&id=<?php echo $p_id; ?>" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};"><img src="images/del.gif"></a></div>
								<?php } ?>
							</li>
							<?php } ?>
						</ul>
						<div class="blank9"></div>
					</dd>
				<?php } ?>
		 </dl>
	</div>
	<div class="m_righter">
		<form id="frm_pro" name="frm_pro" class="" method="post" action="save_productpro.php?customer_id=<?php echo $customer_id; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>">
			<h1>添加产品属性</h1>
			<div class="opt_item">
				<label>属性名称：</label>
				<span class="input"><input type="text" name="name" id="name" value="<?php echo $productpro_name; ?>" class="form_input" size="15" maxlength="30" notnull=""> <font class="fc_red">*</font></span>
				<div class="clear"></div>
			</div>
			<div class="opt_item">
				<label>属性列表：</label>
				<span class="input">
					<ul>
					  <?php 
					    if($productpro_id>0){
						//修改
							 $query2= "select id,name from weixin_commonshop_pros where isvalid=true and parent_id=".$productpro_id;
							 $result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
							 while ($row2 = mysql_fetch_object($result2)) {
								 $p_id=$row2->id;
								 $p_name =$row2->name;
						?>
						<li>
							<input type="text" name="PropertyList[]" value="<?php echo $p_name; ?>" class="form_input" size="15" maxlength="30">
							<input type="hidden" name="LId[]" value="">
							<img src="images/del.gif">
						</li>
					  <?php  }?>
					    <li>
							<input type="text" name="PropertyList[]" value="" class="form_input" size="15" maxlength="30">
							<input type="hidden" name="LId[]" value="">
							<img src="images/del.gif">
							<img src="images/add.gif">						
						</li>
				<?php }else{
					  ?>
						<li>
							<input type="text" name="PropertyList[]" value="" class="form_input" size="15" maxlength="30">
							<input type="hidden" name="LId[]" value="">
							<img src="images/del.gif">
						</li>
						<li>
							<input type="text" name="PropertyList[]" value="" class="form_input" size="15" maxlength="30">
							<input type="hidden" name="LId[]" value="">
							<img src="images/del.gif">
						</li>
						<li>
							<input type="text" name="PropertyList[]" value="" class="form_input" size="15" maxlength="30">
							<input type="hidden" name="LId[]" value="">
							<img src="images/del.gif">
							<img src="images/add.gif">						
						</li>
					<?php } ?>
						</ul>
				</span>
				<input type="hidden" id="keyid" name="keyid" value="<?php echo $productpro_id;?>">
				<input type="hidden" id="subpro" name="subpro" value="">
				<div class="clear"></div>
			</div>
			<div class="opt_item">
				<label></label>
				<span class="input">
				<input type="button" class="btn_green btn_w_120" name="submit_button" value="添加属性" onclick="subPro();">
				<a href="product.php" class="btn_gray">返回</a></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="shop.products_property">
			<input type="hidden" name="PId" value="0">
			<input type="hidden" name="ajax" value="1">
		</form>
	</div>
	<div class="clear"></div>
</div></div>	</div>
<div>

<?php 

mysql_close($link);
?>
</div></div></body></html>