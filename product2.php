<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');

mysql_query("SET NAMES UTF8");

$op="";
if(!empty($_GET["op"])){
   $op=$_GET["op"];
   $keyid=$_GET["keyid"];
   
   $sql="update weixin_commonshop_products set isvalid=false where id=".$keyid;
   mysql_query($sql);
}
$new_baseurl = BaseURL."back_commonshop/";

$keyword="";
if(!empty($_POST["keyword"])){
   $keyword=$_POST["keyword"];
}
$search_type_id=-1;
if(!empty($_POST["search_type_id"])){
   $search_type_id=$_POST["search_type_id"];
}
$search_other_id=-1;
if(!empty($_POST["search_other_id"])){
   $search_other_id=$_POST["search_other_id"];
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
				<li class=""><a href="qrsell.php?customer_id=<?php echo $customer_id; ?>">推广员</a></li>
		</ul>
	</div>
<div id="products" class="r_con_wrap">
<script language="javascript">$(document).ready(shop_obj.products_list_init);</script>
<div class="control_btn">
	<a href="product_type.php?customer_id=<?php echo $customer_id; ?>" class="btn_green btn_w_120">产品分类管理</a>
	<a href="product_pro.php?customer_id=<?php echo $customer_id; ?>" class="btn_green btn_w_120">产品属性管理</a>	
	<a href="add_product.php?customer_id=<?php echo $customer_id; ?>" class="btn_green btn_w_120">添加产品</a>
	<a href="#search" class="btn_green btn_w_120">产品搜索</a>
</div>
<form class="search" method="post" action="product.php?customer_id=<?php echo $customer_id; ?>">
	关键词：<input type="text" name="keyword" value="<?php echo $keyword; ?>" class="form_input" size="15">
	产品分类：<select name="search_type_id">
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
	其他属性：<select name="search_other_id">
		<option value="-1">--请选择--</option>
		<option value="1" <?php if($search_other_id==1){?>selected <?php } ?>>下架</option>
		<option value="2" <?php if($search_other_id==2){?>selected <?php } ?>>新品</option>		
		<option value="3" <?php if($search_other_id==3){?>selected <?php } ?>>热卖</option>	
	</select>
	<input type="submit" class="search_btn" value="搜索">
</form>
<table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
	<thead>
		<tr>
			<td width="8%" nowrap="nowrap">序号</td>
			<td width="30%" nowrap="nowrap">名称</td>
			<td width="15%" nowrap="nowrap">属性分类</td>
			<td width="15%" nowrap="nowrap">价格</td>			
			<td width="20%" nowrap="nowrap">图片</td>			
			<td width="12%" nowrap="nowrap">属性</td>
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
		
	    $query2="select id,name,type_id,orgin_price,now_price,isnew,createtime,isout,ishot,isnew,good_level,meu_level,bad_level from weixin_commonshop_products where isvalid=true and customer_id=".$customer_id;
		if($keyword!=""){
		   $query2=$query2." and name like'%".$keyword."%'";
		}
		if($search_type_id>0){
		   $query2=$query2." and type_id=".$search_type_id;
		}
		if($search_other_id>0){
		   switch($search_other_id){
		      case 1:
			    $query2=$query2." and isout=true";
			    break;
			  case 2:
			    $query2=$query2." and isnew=true";
			    break;
			  case 3:
			    $query2=$query2." and ishot=true";
			    break;
		   }
		}
		$query2=$query2." order by id desc limit ".$start.",".$end;
		$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
		$rcount_q = mysql_num_rows($result2);
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
		   $query3="select name from weixin_commonshop_types where isvalid=true and id=".$p_type_id;
		   $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
		   $typename="";
		   while ($row3 = mysql_fetch_object($result3)) {
		      $typename = $row3->name;
		   }
		   $query3="select imgurl from weixin_commonshop_product_imgs where isvalid=true and product_id=".$p_id." limit 0,1";
		   $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
		   $imgurl="";
		   while ($row3 = mysql_fetch_object($result3)) {
		      $imgurl = $row3->imgurl;
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
	  ?>
		<tr>
			<td nowrap="nowrap"><?php echo $p_id; ?></td>
			<td><?php echo $p_name; ?></td>
			<td><?php echo $typename; ?></td>
			<td nowrap="nowrap">
				<del>￥<?php echo $p_orgin_price; ?><br></del>￥<?php echo $p_now_price; ?>				</td>
			<td nowrap="nowrap"><img src="<?php echo $new_baseurl.$imgurl; ?>" style="width:80px;height:80px;" /></td>
						<td nowrap="nowrap">
				<?php echo $otherstr;?><br>			</td>
			
			<td nowrap="nowrap"><?php echo $p_createtime; ?></td>
			<td nowrap="nowrap"><a href="discuss.php?customer_id=<?php echo $customer_id; ?>&product_id=<?php echo $p_id; ?>"><?php echo $good_level."/".$meu_level."/".$bad_level; ?></a></td>
			<td class="last" nowrap="nowrap">
				<a href="add_product.php?customer_id=<?php echo $customer_id; ?>&product_id=<?php echo $p_id; ?>"><img src="images/mod.gif" align="absmiddle" alt="修改" title="修改"></a>
				<a href="<?php echo QRURL."?qrtype=1&customer_id=".$customer_id; ?>&product_id=<?php echo $p_id; ?>&data=<?php echo $data; ?>" target="_blank"><img src="images/m-ico-4.png" align="absmiddle" alt="产品二维码" title="产品二维码"></a>
				<a href="product.php?customer_id=<?php echo $customer_id; ?>&keyid=<?php echo $p_id; ?>&op=del" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};"><img src="images/del.gif" align="absmiddle" alt="删除"></a>
			</td>
		</tr>
		<?php } ?>
		</tbody>
</table>
<div class="blank20"></div>
<div id="turn_page"><?php if($pagenum>1){ ?><font class="page_noclick" onclick="prePage();">&lt;&lt;上一页</font>&nbsp;<?php } ?>
<font class="page_item_current"><?php echo $pagenum; ?></font>&nbsp;
<?php if($rcount_q==20){?><font class="page_noclick" onclick="nextPage();">下一页&gt;&gt;<?php } ?></font>
</div></div>	
</div>
<div>

<script>
  var pagenum = <?php echo $pagenum ?>;
  function prePage(){
     pagenum--;
     document.location= "product.php?customer_id=<?php echo customer_id; ?>&pagenum="+pagenum;
  }
  
  function nextPage(){
     pagenum++;
     document.location= "product.php?customer_id=<?php echo customer_id; ?>&pagenum="+pagenum;
  }

</script>
<?php 

mysql_close($link);
?>
</div></div></body></html>