<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../proxy_info.php');
//echo "customer_id : ".$customer_id;

$batchcode="";
if(!empty($_GET["batchcode"])){
	(int)$batchcode = $configutil->splash_new($_GET["batchcode"]);
}

$custom = "购物币";
$query = "SELECT custom FROM weixin_commonshop_currency WHERE isvalid=true AND customer_id=$customer_id LIMIT 1";
$result= mysql_query($query) or die('Query failed 19: ' . mysql_error());
while( $row = mysql_fetch_object($result) ){
	$custom = $row->custom;
}
?>
<!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css"><!--内容CSS配色·蓝色-->
<script type="text/javascript" src="../../../common/js/jquery-1.7.2.min.js"></script>
</head>

<body>
<div id="WSY_content">
	<div class="WSY_columnbox" style="min-height: 300px;">
		<div class="WSY_column_header">
			<div class="WSY_columnnav">
				<a class="white1">发红包	</a>
				
			</div>
		</div>
		<div  class="WSY_data">
			<div id="WSY_list" class="WSY_list">
				<div class="WSY_left" style="background: none;">
					订单号：<span style="font-weight:bold"><?php echo $batchcode; ?></span>
				</div>
			</div>
		<table width="97%" class="WSY_table WSY_t2" id="WSY_t1">
			<thead class="WSY_table_header">
				<tr>
					<th width="17%" nowrap="nowrap">推广员信息</th>
					<th width="13%" nowrap="nowrap">佣金/积分/购物币</th>
					<th width="10%" nowrap="nowrap">类型</th>
					<th width="10%" nowrap="nowrap">支付时间</th>
					<th width="10%" nowrap="nowrap">状态</th>
					<th width="10%" nowrap="nowrap">备注</th>
					<th width="10%" nowrap="nowrap">发红包</th>
				</tr>
			</thead>
				<?php 
				

				$query  = "select l.id_new as id, l.user_id,u.weixin_name as username,l.remark,l.reward,l.paytype,l.batchcode,l.card_member_id,l.level_name,l.createtime,l.own_user_name,l.paytype,l.type from weixin_commonshop_order_promoters l,weixin_users u 
				where l.user_id = u.id and  l.isvalid = true and l.batchcode = '".$batchcode."'";

				$result = mysql_query($query) or die('Query failed3: ' . mysql_error());
				
	            while ($row = mysql_fetch_object($result)) {
					$user_id 		= $row->user_id;
					$remark 		= $row->remark;
					$reward 		= $row->reward;
					$type 			= $row->paytype;
					$id 			= $row->id;
				    $batchcode 		= $row->batchcode;
					$card_member_id = $row->card_member_id;
					$level_name 	= $row->level_name;
					$createtime 	= $row->createtime;
					$own_user_name 	= $row->own_user_name;
					$istype 		= $row->paytype;
					$username 		= $row->username;
					$paytype 		= $row->type;
				?> 
                <tr>
				  
				   <td align="center"><?php echo $user_id; ?> [<?php echo $username;?>] 
				   <br/>
				   (<?php echo $level_name;?>)</td>
				   <td align="center"><?php echo $reward; ?></td>
				   <td align="center"><?php echo $type == 1 ? "积分" : "金额"; ?></td>
				   <td align="center"><?php echo $createtime; ?></td>
				   <td align="center"><?php 
						if($istype == 0){
							echo "已支付";
						}else if($istype == 1){
							echo "已确定";
						}else if($istype == 2){
							echo "已退货";
						}else{
							echo "已发红包";
						}
				   ?></td>
				   <td align="center"><?php echo $remark; ?></td>
				   <td align="center">
					<p>
					<?php 
						if(1<=$reward&&$reward<=200&&$istype == 0  && $paytype != 10){
						?>
							<a href="../../../common_shop/jiushop/send_red_pk.php?id=<?php echo $id; ?>&&order_id=<?php echo $batchcode; ?>"><font style="color:green;">发红包</font></a>
						<?php
						}else if($istype == 3 ){
						?>
							<font style="color:red;">已发红包</font>
						<?php
						}else{
						?>							
							<font style="color:gray;">条件不满足</font>
						<?php
						}
						?>
					
					
					</p>
				   </td>
				   
                </tr>				
			   <?php } ?>

			</tbody>
		</table>
		</div>	
		<div style="width:100%;height:20px;"></div>	
	</div>
</div>
	
<?php 
mysql_close($link);
?>
</body>
</html>