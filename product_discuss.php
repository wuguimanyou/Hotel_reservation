<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../auth_user.php');
require('../proxy_info.php');

mysql_query("SET NAMES UTF8");

$op="";
if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
   $keyid=$configutil->splash_new($_GET["keyid"]);
   if($op=="del"){
	   $sql="update weixin_commonshop_product_evaluations set isvalid=false where id=".$keyid;
	   mysql_query($sql);
   }else if($op=="status"){
       $status= $configutil->splash_new($_GET["status"]);
	   $sql="update weixin_commonshop_product_evaluations set status=".$status." where id=".$keyid;
	   mysql_query($sql);
   }
}
$new_baseurl = BaseURL."back_commonshop/";

$pid=-1;
if(!empty($_GET["pid"])){
   $pid=$configutil->splash_new($_GET["pid"]);
}

$search_status = "";
if($_GET["search_status"] !=""){
	$search_status = $configutil->splash_new($_GET["search_status"]);	 
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
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/icon.css" media="all">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/inside.css" media="all">
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/global.js"></script>
</head>

<body>

<style type="text/css">
body, html{background:url(images/main-bg.jpg) left top fixed no-repeat;}
.table-img{width:50px;height:50px;}
</style>
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
		</ul>
	</div>
<div id="products" class="r_con_wrap">

<form class="search" style="display:block" method="get" action="product_discuss.php?customer_id=<?php echo $customer_id_en; ?>">
	关键词：<input type="text" name="keyword" value="<?php echo $keyword; ?>" class="form_input" size="15">	
	状态：
	<select name="search_status">
		<option value="">--请选择--</option>
		<option value="1" <?php if($search_status==1){?>selected <?php } ?>>有效</option>
		<option value="0" <?php if($search_status==0){?>selected <?php } ?>>无效</option>		
	</select>
	<input type="submit" class="search_btn" value="搜索">
</form>

<table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
	<thead>
		<tr>
			<td width="8%" nowrap="nowrap">序号</td>
			<td width="8%" nowrap="nowrap">评论者</td>
			<td width="8%" nowrap="nowrap">订单号</td>
			<td width="8%" nowrap="nowrap">产品名称</td>
			<td width="8%" nowrap="nowrap">评论级别</td>
			<td width="8%" nowrap="nowrap">类型</td>
			<td width="21%" nowrap="nowrap">评论</td>
			<td width="21%" nowrap="nowrap">图片</td>
			<td width="10%" nowrap="nowrap">时间</td>
			<td width="8%" nowrap="nowrap">状态</td>
			<td width="10%" nowrap="nowrap" class="last">操作</td>
		</tr>
	</thead>
	<tbody>
	  <?php
	    $pagenum = 1;

		if(!empty($_GET["pagenum"])){
		   $pagenum = $configutil->splash_new($_GET["pagenum"]);
		}

		$start = ($pagenum-1) * 20;
		$end = 20; 
		
		
	    $query2="select id,user_id,status,discuss,level,createtime,discussimg,type,batchcode,product_id from weixin_commonshop_product_evaluations where isvalid=true and customer_id=".$customer_id;
 		/* 关键字搜索 */
		$keyword = "";
		if(!empty($_GET["keyword"])){
		   $keyword = $configutil->splash_new($_GET["keyword"]);
		    $query2 .= " and discuss like '%".$keyword."%'";
		}
		/* 关键字搜索End	 */
		
  		/* 状态搜索 */
		if($_GET["search_status"] !=""){
		   $query2 .= " and status =".$search_status;
		}
		/* 状态搜索End	 */
		$query2=$query2." order by batchcode desc,id desc limit ".$start.",".$end;
		$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
		$rcount_q = mysql_num_rows($result2);
		while ($row2 = mysql_fetch_object($result2)) {
		   $d_id = $row2->id;
		   $user_id = $row2->user_id;
		   $level = $row2->level;
		   $discuss = $row2->discuss;
		   $createtime = $row2->createtime;
		   $status = $row2->status;
		   $discussimg = $row2->discussimg;
		   $type = $row2->type;
		   $batchcode = $row2->batchcode;
		   $product_id = $row2->product_id;
		   $img_array = explode(",", $discussimg); 
		   $statusname="无效";
		   $typename="未知";
		   switch($type){
		      case 1:
			      $typename="普通";
			     break;
			  case 2:
			     $typename="追加";
		   }		   
		   if($status==1){
		      $statusname="有效";
		   }
		   $levelname="好评";
		   switch($level){
		      case 2:
			      $levelname="中评";
			     break;
			  case 3:
			     $levelname="差评";
			     break;
		   }
		   
		   //产品 名称
		   $query_pn="select name from weixin_commonshop_products where customer_id=".$customer_id." and id=".$product_id." limit 0,1";
		   $result_pn = mysql_query($query_pn) or die('Query_pn failed: ' . mysql_error());
		   $porductName="";
		   while ($row_pn = mysql_fetch_object($result_pn)) {
			  $porductName=$row_pn -> name;
		   }		   
		   //产品 名称End
		   
		   //购买用户 名称
		   $query3="select name,weixin_headimgurl,weixin_name from weixin_users where isvalid=true and id=".$user_id;
		   $result3 = mysql_query($query3) or die('Query failed: ' . mysql_error());
		   $username="";
		   $headimgurl="";
		   while ($row3 = mysql_fetch_object($result3)) {
			  $username=$row3->name;
			  $headimgurl = $row3->weixin_headimgurl;
			  $weixin_name=$row3->weixin_name;
			  break;
		   }
		   if(empty($username)){
			  $username = $weixin_name;
		   }
		   //购买用户 名称End
		   
	  ?>
		<tr>
			<td nowrap="nowrap"><?php echo $d_id; ?></td>
			<td><?php echo $username; ?></td>
			<td><?php echo $batchcode; ?></td>
			<td><?php echo $porductName; ?></td>
			<td><?php echo $levelname; ?></td>
			<td><?php echo $typename; ?></td>
			<td><?php echo $discuss; ?></td>
			<td>
			<?php  if(!empty($discussimg)){ 
							foreach($img_array as $key=>$value){
							$value=substr($value,3);
							echo '<a href="'.$value.'"><img class="table-img" src="'.$value.'"></img></a>';			
							}	
						}else{ echo '无'; } ?>
			</td>
			<td nowrap="nowrap"><?php echo $createtime; ?></td>
			<td><?php echo $statusname; ?></td>
			<td class="last" nowrap="nowrap">
			    <?php if($status==0){?>	
					   <a  class="btn"  href="product_discuss.php?op=status&keyid=<?php echo $d_id; ?>&status=1"  title="确认">
						  <i  class="icon-ok"></i>
						</a>
					<?php }else{ ?>
					    <a  class="btn"  href="product_discuss.php?op=status&keyid=<?php echo $d_id; ?>&status=0"  title="取消确认">
						  <i  class="icon-minus"></i>
						</a>
					<?php } ?>
				<a href="product_discuss.php?customer_id=<?php echo $customer_id_en; ?>&keyid=<?php echo $d_id; ?>&op=del" onclick="if(!confirm(&#39;删除后不可恢复，继续吗？&#39;)){return false};"><img src="images/del.gif" align="absmiddle" alt="删除"></a>
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
     document.location= "product_discuss.php?customer_id=<?php echo customer_id_en; ?>&pagenum="+pagenum;
  }
  
  function nextPage(){
     pagenum++;
     document.location= "product_discuss.php?customer_id=<?php echo customer_id_en; ?>&pagenum="+pagenum;
  }

</script>
<?php 

mysql_close($link);
?>
</div></div></body></html>