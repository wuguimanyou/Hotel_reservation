<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');

$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../../../proxy_info.php');
//通过order.php传过来的参数
$batchcode="";
if(!empty($_GET["batchcode"])){//获取订单编号
	$batchcode = $configutil->splash_new($_GET["batchcode"]);
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
<title>返佣说明 订单号:<?php echo $batchcode;?></title>

</head>
<body> 
	<!--内容框架-->
	<div class="WSY_content">
		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<!--列表头部切换开始-->
			<div class="WSY_column_header">
				<div class="WSY_columnnav">
					<a class="white1">返佣说明</a>
				</div>
			</div>
			<!--列表头部切换结束-->
			<div class="WSY_remind_main">
				<form class="search" id="search_form" method="post" action="cash.php?customer_id=AzBVZ1UzVGk=">
					<div class="WSY_list" style="margin-top: 18px;">
						<li class="WSY_left"><a>订单号：<?php echo $batchcode; ?></a></li>		
						<ul class="WSY_righticon">
							<li><a style="margin-right:40px;" href="javascript:history.go(-1);"><td valign="bottom" align="right">返回</td></a></li>         
						</ul>
					</div>     
				</form>
  
				<table width="97%" class="WSY_table" id="WSY_t1">
					<thead class="WSY_table_header">
						<th width="20%" nowrap="nowrap">推广员信息</th>
						<th width="10%" nowrap="nowrap">佣金状态</th>
						<th width="10%" nowrap="nowrap">佣金/积分</th>
						<th width="10%" nowrap="nowrap">奖励说明</th>
						<th width="20%" nowrap="nowrap">返佣时间</th>
						<th width="30%" nowrap="nowrap">备注</th>
					</thead>
					<tbody>
					<?php 
					//查询用户的返佣金额
						 $query  = "select user_id,reward,card_member_id,createtime,paytype,remark,type from weixin_commonshop_order_promoters where isvalid=true and batchcode='".$batchcode."'";
						
						 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
						$total_money = 0;
						 while ($row = mysql_fetch_object($result)) {
							$user_id = $row->user_id;
							//查询用户是否推广员,是否满足消费无限级奖励
							$sql="select isAgent,is_consume from promoters where isvalid=true and user_id=".$user_id." limit 1";
							$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
							while ($row3 = mysql_fetch_object($result3)) {
								$isAgent = $row3->isAgent;
								$is_consume = $row3->is_consume;	//判断 0:不是无限级奖励 1:无限级奖励
							}
							//查询是否开启团队奖励,是否开启股东分红奖励
							$sql="select is_team,is_shareholder from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
							$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
							while ($row3 = mysql_fetch_object($result3)) {
								$is_team = $row3->is_team;					//是否开启团队奖励
								$is_shareholder = $row3->is_shareholder;	//是否开启股东分红奖励
								break;
							}
							//查询1-4级自定义代理名称
							$query4="select a_name,b_name,c_name,d_name from weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id." limit 0,1";
							$result4 = mysql_query($query4);
							while($row4 = mysql_fetch_object($result4)){
								$a_name=$row4->a_name;
								$b_name=$row4->b_name;
								$c_name=$row4->c_name;
								$d_name=$row4->d_name;
							}
							
							$consume_name ="";
							//如果开启团队奖励并且不开启股东分红
							if($is_team==1 && $is_shareholder==0){
								if($is_consume>0){
									$consume_name = "(无限级奖励)";
								}
							}else if($is_shareholder==1){//否则开启股东分红
								switch($is_consume){
									case 1: $consume_name = "(股东分红-".$d_name.")"; break;
									case 2: $consume_name = "(股东分红-".$c_name.")"; break;
									case 3: $consume_name = "(股东分红-".$b_name.")"; break;
									case 4: $consume_name = "(股东分红-".$a_name.")"; break;
								}
							}
								
							
							switch($isAgent){
								case 0:
								$user_name="推广员";
								break;
								case 1:
								$user_name="代理商";
								break;
								case 2:
								$user_name="顶级推广员";
								break;
								case 3:
								$user_name="供应商";
								break;
								case 4:
								$user_name="技师";
								break;
								case 5:
								$user_name="区代";
								break;
								case 6:
								$user_name="市代";
								break;
								case 7:
								$user_name="省代";
								break;
							}
							
							$reward = $row->reward;//返佣金额
							$card_member_id = $row->card_member_id;
							$createtime = $row->createtime;//时间
							$paytype = $row->paytype;//0:支付；1确定；2：退货；3：红包；4：退款
							$remark = $row->remark;//备注
							$type = $row->type;//团队奖励
							switch($type){
								case 0:
								$type_name="分销奖励";
								break;
								case 1:
								$type_name="团队奖励";
								break;
								case 2:
								if($is_shareholder==1){
									$type_name="股东奖励";
								}else if($is_shareholder==0){
									$type_name="无限级奖励";
								}
								break;
								case 3:		
								$type_name="分销奖励 ";								
								break;
								case 4:
								$type_name="团队奖励";	
								break;
								case 5:
								$type_name="股东奖励";	
								break;
								case 6:		
								$type_name="商圈金融分销奖励 ";								
								break;
								case 7:
								$type_name="商圈金融团队奖励";	
								break;
								case 8:
								$type_name="商圈金融股东奖励";	
								break;
								case 9:
								$type_name="全球分红奖励";	
								break;
								case 10:
								$type_name="购物币奖励";	
								break;
							}
							$sql="select customer_red_id from weixin_red_log where isvalid=true and deal_id='".$batchcode."'";
							$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
							while ($row3 = mysql_fetch_object($result3)) {
								$customer_red_id = $row3->customer_red_id;
							}
							if($paytype == 0){
								$paytpyestr= "已支付";
							}else if($paytype == 1){
								//$paytpyestr= "<span style='color:red'>已到会员卡</span>";
								$paytpyestr= "<span style='color:red'>已到账</span>";
							}else if($paytype == 2){
								$paytpyestr= "已退货";
								$remark = "(撤销)".$remark;
							}else if($paytype == 4){
								$paytpyestr= "已退款";
								$remark = "(撤销)".$remark;
							}else{
								$paytpyestr= "<span style='color:red'>已发红包</span>";
							}
							$weixin_name="";
							$name="";
							$sql = "select name,weixin_name from weixin_users where isvalid=true and id=".$user_id." and customer_id=".$customer_id." limit 0,1";
							$result2 = mysql_query($sql) or die('Query failed: ' . mysql_error()); 
							while ($row2 = mysql_fetch_object($result2)) { 
								$name = $row2->name; 
								$weixin_name = $row2->weixin_name;  
							} 
							 
							$total_money=$total_money+$reward;
							if($type==9){
								$weixin_name='';
								$user_name	='';
								$consume_name='';
								$user_id 	='-';
								$card_member_id='-';
							}
					?> 
						<tr>
							<td>
								<a href="../../Users/promoter/promoter.php?search_user_id=<?php echo $user_id; ?>&customer_id=<?php echo $customer_id_en; ?>"><?php echo $weixin_name; ?>(<?php echo $user_id; ?>)</a>
								</br><?php echo $user_name;?>
								</br><?php echo $consume_name;?>						   
							</td>
							<td>
								<?php echo $paytpyestr; ?>
								<?php if($paytype==3){?>
								</br>(<?php echo $customer_red_id?>)
								<?php } ?>							
							</td>
							
							<td><?php echo round($reward,2); ?></td>
							
							<td><?php echo $type_name; ?></td>
							<td><?php echo $createtime; ?></td>
							<td><?php echo $remark; ?></td>
						</tr>
					   <?php } ?>
						<tr>
							<td colspan="7">
								总返佣：<span style="font-size:16px;color:red;"><?php echo round($total_money,2); ?>元
							</td>
						</tr>					    
					
					</tbody>					
				</table>
				<div class="blank20"></div>
				<div id="turn_page"></div>
				<!--翻页开始-->
				<div class="WSY_page">
        	
				</div>
				<!--翻页结束-->
			</div>
		</div>
	</div>


<?php mysql_close($link);?>	


</body>
</html>