<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');

$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');

$batchcode="";
if(!empty($_GET["batchcode"])){
	$batchcode = $configutil->splash_new($_GET["batchcode"]);
}
 
$shop_card_id=-1;
$query="select shop_card_id from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query);
while ($row = mysql_fetch_object($result)) {
	$shop_card_id=$row->shop_card_id;
}

$batchcode_status = 0;
$query = "select status from weixin_commonshop_orders where isvalid=true and batchcode='".$batchcode."' and customer_id=".$customer_id; 
$result = mysql_query($query) or die('L18: '.mysql_error());
while($row = mysql_fetch_object($result)){
	$batchcode_status = $row->status;
}
if($batchcode_status){
	$cashback_id 	 	= -1;
	$cashback 	 	 	= 0;	//总返现金额
	$rest_cashback 	 	= 0;	//剩余返现金额
	$cashback_status 	= 0;
	$total_get_cashback = 0;	//已经领取金额
	$query = "select id,cashback,rest_cashback,isvalid from cashback where batchcode='".$batchcode."' and customer_id=".$customer_id; 
	$result = mysql_query($query) or die('L18: '.mysql_error());
	while($row = mysql_fetch_object($result)){
		$cashback_id 	 = $row->id;
		$cashback 		 = $row->cashback;
		$rest_cashback 	 = $row->rest_cashback;
		$cashback_status = $row->isvalid;
		
		$total_get_cashback = $cashback-$rest_cashback;
		$total_get_cashback = round($total_get_cashback,2);
	}
}else{
	$cashback 	 	 	= 0;	//总返现金额
	$cashback_status 	= 0;
	$total_get_cashback = 0;	//已经领取金额
	$sql_cashback_t = "select cashback,isvalid from cashback_t where batchcode='".$batchcode."' and customer_id=".$customer_id;
	$res_cashback_t = mysql_query($sql_cashback_t) or die('sql_cashback_t failed: '.mysql_error());
	while($row_cashback_t = mysql_fetch_object($res_cashback_t)){
		$cashback 		 = $row_cashback_t->cashback;
		$cashback_status = $row_cashback_t->isvalid;
	}
}

?>

<!doctype html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../../../css/inside.css" media="all">
<script type="text/javascript" src="../../../common/js/jquery-1.7.2.min.js"></script>
<meta http-equiv="content-type" content="text/html;charset=UTF-8">
<script charset="utf-8" src="../../../common/js/layer/V2_1/layer.js"></script>
<title>返现说明 订单号:<?php echo $batchcode;?></title>
</head>
<body> 
	<!--内容框架-->
	<div class="WSY_content">
		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<!--列表头部切换开始-->
			<div class="WSY_column_header">
				<div class="WSY_columnnav">
					<a class="white1">返现说明</a>
				</div>
			</div>
			<!--列表头部切换结束-->
			<div class="WSY_remind_main">
				<form class="search" id="search_form" method="post" action="cash.php?customer_id=AzBVZ1UzVGk=">
					<div class="WSY_list" style="margin-top: 18px;">
						<li class="WSY_left"><a>订单号：<?php echo $batchcode; ?></a></li>	
						<li style="margin-right: 60px;"><a href="javascript:history.go(-1);" class="WSY_button" style="margin-top: 0;width: 60px;height: 28px;vertical-align: middle;line-height: 28px;">返回</a></li>
						
					</div> 
					<div class="WSY_list" style="margin-top: 18px;">
						<li style="margin-left: 20px;">
							<a style="color: #646464;font-size: 14px;">总返现金额：<span style="font-size:16px;color:red;margin-right: 20px;"><?php echo $cashback; ?>元 </a>
							<a style="color: #646464;font-size: 14px;">已领金额：<span style="font-size:16px;color:red;"><?php echo $total_get_cashback; ?>元 </a>
						</li>					
					</div>	
					<?php if($cashback_status){?>
					<div class="WSY_list" style="float: right;margin-top: -45px;margin-right: 8px;">
						<ul class="WSY_righticon" id="butt">
						   <li><a style="background:red;cursor: pointer;" onclick="delCashback();">删除此笔返现</a></li>
						</ul>
					</div>
					<?php }else{?>
					<div class="WSY_list" style="float: right;margin-top: -45px;margin-right: 8px;">
						<ul class="WSY_righticon">
						   <li><a style="background:#5D5D5D;">已删除</a></li>
						</ul>
					</div>
					<?php }?>
				</form>
  
				<table width="97%" class="WSY_table" id="WSY_t1">
					<thead class="WSY_table_header">
						<th width="20%" nowrap="nowrap">推广员信息</th>
						<th width="20%" nowrap="nowrap">会员卡编号</th>
						<th width="20%" nowrap="nowrap">返现状态</th>
						<th width="20%" nowrap="nowrap">返现金额</th>
						<th width="20%" nowrap="nowrap">领取时间</th>
					</thead>
					<tbody>
					<?php 
						 $pagenum = 1;

						 if(!empty($_GET["pagenum"])){
						    $pagenum = $configutil->splash_new($_GET["pagenum"]);
						 }

						 $start = ($pagenum-1) * 20;
						 $end = 20;		
						
						 $query  = "select user_id,createtime,get_cashback from cashback_log where isvalid=true and batchcode='".$batchcode."'";
						
						 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
						 $rcount_q2 = mysql_num_rows($result);
						 $query1 = $query.' order by id desc limit '.$start.','.$end;
						 $result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());
						 while ($row = mysql_fetch_object($result1)) {
							$user_id = $row->user_id;							
							$createtime = $row->createtime;
							$get_cashback = $row->get_cashback;



							$weixin_name="";
							$name="";
							$user_name="";
							$sql = "select name,weixin_name from weixin_users where isvalid=true and id=".$user_id." and customer_id=".$customer_id." limit 0,1";
							$result2 = mysql_query($sql) or die('Query failed: ' . mysql_error()); 
							while ($row2 = mysql_fetch_object($result2)) { 
								$weixin_name = $row2->weixin_name;  
								$name = $row2->name;  
								$user_name = $name."(".$weixin_name.")";
							} 							  
							 
							$sql="select id from weixin_card_members where isvalid=true and card_id=".$shop_card_id." and user_id=".$user_id;
							$result2 = mysql_query($sql) or die('Query failed: ' . mysql_error());  
							$card_member_id=-1;
							while ($row2 = mysql_fetch_object($result2)) {
								$card_member_id = $row2->id;
								break;
							} 
							 
							$status_str = "已到会员卡";
					?> 
						<tr>
							<td>
								<a href="../../Users/promoter/promoter.php?search_user_id=<?php echo $user_id; ?>&customer_id=<?php echo $customer_id_en; ?>"><?php echo $user_name; ?></a>
								<br>用户编号：<?php echo $user_id; ?>		
							</td>
							<td><?php echo $card_member_id; ?></td>
							<td style='color:red'><?php echo $status_str; ?></td>							
							<td><?php echo $get_cashback; ?></td>							
							<td><?php echo $createtime; ?></td>
						</tr>
					   <?php } ?>					  
					</tbody>					
				</table>
				 <!--翻页开始-->
				<div class="WSY_page">
			
				</div>
				<!--翻页结束-->
			</div>
		</div>
	</div>
<script src="../../../js/fenye/jquery.page1.js"></script>

<script>
 var pagenum = '<?php echo $pagenum ?>';
 var rcount_q2 = '<?php echo $rcount_q2 ?>';
 var end = '<?php echo $end ?>';
 var customer_id = '<?php echo $customer_id ?>';
 var batchcode = '<?php echo $batchcode ?>';
 /* var user_id = <?php echo $user_id ?>; */
 var count =Math.ceil(rcount_q2/end);//总页数

  	//pageCount：总页数
	//current：当前页
	
	$(".WSY_page").createPage({
        pageCount:count,
        current:pagenum,
        backFn:function(p){
		 document.location= "order_cashback_log.php?pagenum="+p+"&batchcode="+batchcode+"&customer_id="+customer_id;
	   }
    });

  var pagenum = <?php echo $pagenum ?>;
   var page = count;
  function jumppage(){
	var a=parseInt($("#WSY_jump_page").val());
	if((a<1) || (a==pagenum) || (a>page) || isNaN(a)){
		return false;
	}else{
		 document.location= "order_cashback_log.php?pagenum="+p+"&batchcode="+batchcode+"&customer_id="+customer_id;
	}
  }


var index_layer;
function layer_open(){
	index_layer= layer.load(0, {
		shade: [0.1,'#000'], //0.1透明度的白色背景
		content: '<div style="position:relative;top:30px;width:200px;color:red">数据处理中</div>'
	});	
}

function delCashback(){
	layer.confirm('订单：'+batchcode+'，您确定要删除此笔返现吗？删除后，用户将无法继续领取，但已领取部分不能退回！', {
		btn: ['确认','取消'] 
	}, function(confirm){
		
		layer.close(confirm);	  
		layer_open();			
		$.ajax({
			url: "cashback.class.php",
			type:"POST",
			data:{'batchcode':batchcode,'op':"del"},
			dataType:"json",
			success: function(res){
				layer.close(index_layer);
				layer.alert(res.msg);
				$('#butt').html('<li><a style="background:#5D5D5D;">已删除</a></li>');
			},	
			error:function(res){
				layer.close(index_layer);
				layer.alert("网络错误请检查网络");
			}						
		});		 
	}, function(){
		layer.msg('已取消', {
			time: 4000,
			btn: ['确认'],
			icon:1
		});
	});
			
}  
</script>

<?php mysql_close($link);?>	


</body>
</html>