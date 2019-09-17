<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');

mysql_query("SET NAMES UTF8");

$productpro_id=-1;
if(!empty($_GET["productpro_id"])){
   $productpro_id=$_GET["productpro_id"];
}
$productpro_name="";
if($productpro_id>0){
   $query="select name,parent_id from weixin_commonshop_pros where isvalid=true and  id=".$productpro_id;
   $result = mysql_query($query) or die('Query failed: ' . mysql_error());
   while ($row = mysql_fetch_object($result)) {
       $productpro_name = $row->name;
   }
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
			<li class=""><a href="base.php?customer_id=<?php echo $customer_id; ?>">基本设置</a></li>
			<li class=""><a href="fengge.php?customer_id=<?php echo $customer_id; ?>">风格设置</a></li>
			<li class=""><a href="defaultset.php?customer_id=<?php echo $customer_id; ?>">首页设置</a></li>
			<li class="cur"><a href="product.php?customer_id=<?php echo $customer_id; ?>">产品管理</a></li>
			<li class=""><a href="order.php?customer_id=<?php echo $customer_id; ?>">订单管理</a></li>
		</ul>
	</div>
   <div id="products" class="r_con_wrap">
   <script language="javascript">$(document).ready(shop_obj.products_property_init);</script>
     <div class="property">
	   <div class="m_lefter">
		  <dl>
		        <?php 
				$query="select id,name,parent_id from weixin_commonshop_pros where isvalid=true and parent_id=-1 and customer_id=".$customer_id;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
			    while ($row = mysql_fetch_object($result)) {
				   $parent_id = $row->id;
				   $pname = $row->name;
				?>
					<dd>
						<div class="list">
							<a href="product_pro.php?customer_id=<?php echo $customer_id; ?>&productpro_id=<?php echo $parent_id; ?>" title="修改">
							 <img src="images/mod.gif" align="absmiddle">
							</a>
							<a href="#" title="删除" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};">
							<img src="images/del.gif" align="absmiddle">
							</a>
							<?php echo $pname; ?>
						</div>
						<ul>
						   <?php 
						     $query2 = "select id,name from weixin_commonshop_pros where isvalid=true and parent_id=".$parent_id;
							 $result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
			                 while ($row2 = mysql_fetch_object($result2)) {
							     $p_id=$row2->id;
								 $p_name =$row2->name;
							 
						   ?>
							<li>
								<div class="title"><img src="images/jt.gif"><?php echo $p_name; ?></div>
								<div class="opt"><a href="#" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};"><img src="images/del.gif"></a></div>
							</li>
							<?php } ?>
						</ul>
						<div class="blank9"></div>
					</dd>
				<?php } ?>
		 </dl>
	</div>
	<div class="m_righter">
		<form id="frm_pro" name="frm_pro" class="" method="post" action="save_productpro.php?customer_id=<?php echo $customer_id; ?>">
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
				<a href="#" class="btn_gray">返回</a></span>
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