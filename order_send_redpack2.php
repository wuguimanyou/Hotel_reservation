<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');
//echo "customer_id : ".$customer_id;

$batchcode="";
if(!empty($_GET["batchcode"])){
	$batchcode = $configutil->splash_new($_GET["batchcode"]);
}

//代理模式,分销商城的功能项是 266
$is_distribution=0;//渠道取消代理商功能
$is_disrcount=0;
$query1="select count(1) as is_disrcount from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城代理模式' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('W_is_disrcount Query failed1: ' . mysql_error());  
while ($row = mysql_fetch_object($result1)) {
   $is_disrcount = $row->is_disrcount;
   break;
}
if($is_disrcount>0){
   $is_distribution=1;
}

//供应商模式,渠道开通与不开通
$is_supplierstr=0;//渠道取消供应商功能
$sp_count=0;//渠道取消供应商功能
$sp_query="select count(1) as sp_count from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城供应商模式' and c.id=cf.column_id";
$sp_result = mysql_query($sp_query) or die('W_is_supplier Query failed2: ' . mysql_error());  
while ($row = mysql_fetch_object($sp_result)) {
   $sp_count = $row->sp_count;
   break;
}
if($sp_count>0){
   $is_supplierstr=1;
}
?>
<!DOCTYPE html>
<!-- saved from url=(0047)http://www.ptweixin.com/member/?m=shop&a=orders -->
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" type="text/css" href="../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../common/css_V6.0/contentblue.css"><!--内容CSS配色·蓝色-->
<script type="text/javascript" src="../common/js/jquery-1.7.2.min.js"></script>
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
					<th width="13%" nowrap="nowrap">会员卡编号</th>
					<th width="13%" nowrap="nowrap">佣金(积分)</th>
					<th width="10%" nowrap="nowrap">类型</th>
					<th width="10%" nowrap="nowrap">支付时间</th>
					<th width="10%" nowrap="nowrap">状态</th>
					<th width="10%" nowrap="nowrap">备注</th>
					<th width="10%" nowrap="nowrap">发红包</th>
				</tr>
			</thead>
			  <?php 
			     //$query="select batch_code,createtime,checkresult,totalprice from weixin_commonshop_order_mulcheck where isvalid = true and cust_id = ".$customer_id." order by createtime desc ";
				 $query  = "select l.id_new as id, l.user_id,u.weixin_name as username,l.remark,l.reward,l.paytype,l.batchcode,l.card_member_id,l.level_name,l.createtime,l.own_user_name,l.paytype,
				 c.card_id from weixin_commonshop_order_promoters l,weixin_card_members c,weixin_users u 
				 where l.card_member_id =c.id and l.user_id = u.id and  l.isvalid = true and l.batchcode = '".$batchcode."'";
				
				 $result = mysql_query($query) or die('Query failed3: ' . mysql_error());
				
	             while ($row = mysql_fetch_object($result)) {
					$user_id = $row->user_id;
					$remark = $row->remark;
					$reward = $row->reward;
					$type = $row->paytype;
					$id = $row->id;
				    $batchcode = $row->batchcode;
					$card_member_id = $row->card_member_id;
					$level_name = $row->level_name;
					$createtime = $row->createtime;
					$own_user_name = $row->own_user_name;
					$istype = $row->paytype;
					$card_id = $row->card_id;
					$username = $row->username;
					
			   ?> 
                <tr>
				  
				   <td align="center"><?php echo $user_id; ?> [<?php echo $username;?>] 
				   <br/>
				   (<?php echo $level_name;?>)</td>
				   <td align="center">
				   <!-- <a href="../card_member.php?card_id=<?php echo $card_id; ?>&card_member_id=<?php echo $card_member_id; ?>&customer_id=<?php echo passport_encrypt((string)$customer_id);?>"><?php echo $card_member_id; ?></a> -->
				   <?php echo $card_member_id; ?>
				   </td>
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
						if(1<=$reward&&$reward<=200&&$istype == 0){
						?>
							<a href="../common_shop/jiushop/send_red_pk.php?id=<?php echo $id; ?>&&order_id=<?php echo $batchcode; ?>"><font style="color:green;">发红包</font></a>
						<?php
						}else if($istype == 3){
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
			  <!--tr>
				  <td colspan="8" style="text-align:right">
					<input type="button" value="红包确定" style="width:100px;line-height:30px;background-color:green;color:white;border-radius:15%"/>
				  </td>
			  </tr-->
			</tbody>
		</table>
		<div class="blank20"></div>
		<div id="turn_page"></div>
		</div>	
	</div>
</div>
	
<div style="top: 101px; position: absolute; background-color: white; z-index: 2000; left: 398px; visibility: hidden; background-position: initial initial; background-repeat: initial initial;" class="om-calendar-list-wrapper om-widget om-clearfix om-widget-content multi-1"><div class="om-cal-box" id="om-cal-4381460996810347"><div class="om-cal-hd om-widget-header"><a href="javascript:void(0);" class="om-prev "><span class="om-icon om-icon-seek-prev">Prev</span></a><a href="javascript:void(0);" class="om-title">2014年1月</a><a href="javascript:void(0);" class="om-next "><span class="om-icon om-icon-seek-next">Next</span></a></div><div class="om-cal-bd"><div class="om-whd"><span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span></div><div class="om-dbd om-clearfix"><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);">1</a><a href="javascript:void(0);">2</a><a href="javascript:void(0);">3</a><a href="javascript:void(0);">4</a><a href="javascript:void(0);">5</a><a href="javascript:void(0);">6</a><a href="javascript:void(0);">7</a><a href="javascript:void(0);">8</a><a href="javascript:void(0);" class="om-state-highlight om-state-nobd">9</a><a href="javascript:void(0);" class="om-state-disabled">10</a><a href="javascript:void(0);" class="om-state-disabled">11</a><a href="javascript:void(0);" class="om-state-disabled">12</a><a href="javascript:void(0);" class="om-state-disabled">13</a><a href="javascript:void(0);" class="om-state-disabled">14</a><a href="javascript:void(0);" class="om-state-disabled">15</a><a href="javascript:void(0);" class="om-state-disabled">16</a><a href="javascript:void(0);" class="om-state-disabled">17</a><a href="javascript:void(0);" class="om-state-disabled">18</a><a href="javascript:void(0);" class="om-state-disabled">19</a><a href="javascript:void(0);" class="om-state-disabled">20</a><a href="javascript:void(0);" class="om-state-disabled">21</a><a href="javascript:void(0);" class="om-state-disabled">22</a><a href="javascript:void(0);" class="om-state-disabled">23</a><a href="javascript:void(0);" class="om-state-disabled">24</a><a href="javascript:void(0);" class="om-state-disabled">25</a><a href="javascript:void(0);" class="om-state-disabled">26</a><a href="javascript:void(0);" class="om-state-disabled">27</a><a href="javascript:void(0);" class="om-state-disabled">28</a><a href="javascript:void(0);" class="om-state-disabled">29</a><a href="javascript:void(0);" class="om-state-disabled">30</a><a href="javascript:void(0);" class="om-state-disabled">31</a><a href="javascript:void(0);" class="om-null">0</a></div></div><div class="om-setime om-state-default hidden"></div><div class="om-cal-ft"><div class="om-cal-time om-state-default">时间：<span class="h">0</span>:<span class="m">0</span>:<span class="s">0</span><div class="cta"><button class="u om-icon om-icon-triangle-1-n"></button><button class="d om-icon om-icon-triangle-1-s"></button></div></div><button class="ct-ok om-state-default">确定</button></div><div class="om-selectime om-state-default hidden"></div></div></div><div style="top: 101px; position: absolute; background-color: white; z-index: 2000; left: 564px; visibility: hidden; background-position: initial initial; background-repeat: initial initial;" class="om-calendar-list-wrapper om-widget om-clearfix om-widget-content multi-1"><div class="om-cal-box" id="om-cal-8113757355604321"><div class="om-cal-hd om-widget-header"><a href="javascript:void(0);" class="om-prev "><span class="om-icon om-icon-seek-prev">Prev</span></a><a href="javascript:void(0);" class="om-title">2014年1月</a><a href="javascript:void(0);" class="om-next "><span class="om-icon om-icon-seek-next">Next</span></a></div><div class="om-cal-bd"><div class="om-whd"><span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span></div><div class="om-dbd om-clearfix"><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);">1</a><a href="javascript:void(0);">2</a><a href="javascript:void(0);">3</a><a href="javascript:void(0);">4</a><a href="javascript:void(0);">5</a><a href="javascript:void(0);">6</a><a href="javascript:void(0);">7</a><a href="javascript:void(0);">8</a><a href="javascript:void(0);" class="om-state-highlight om-state-nobd">9</a><a href="javascript:void(0);" class="om-state-disabled">10</a><a href="javascript:void(0);" class="om-state-disabled">11</a><a href="javascript:void(0);" class="om-state-disabled">12</a><a href="javascript:void(0);" class="om-state-disabled">13</a><a href="javascript:void(0);" class="om-state-disabled">14</a><a href="javascript:void(0);" class="om-state-disabled">15</a><a href="javascript:void(0);" class="om-state-disabled">16</a><a href="javascript:void(0);" class="om-state-disabled">17</a><a href="javascript:void(0);" class="om-state-disabled">18</a><a href="javascript:void(0);" class="om-state-disabled">19</a><a href="javascript:void(0);" class="om-state-disabled">20</a><a href="javascript:void(0);" class="om-state-disabled">21</a><a href="javascript:void(0);" class="om-state-disabled">22</a><a href="javascript:void(0);" class="om-state-disabled">23</a><a href="javascript:void(0);" class="om-state-disabled">24</a><a href="javascript:void(0);" class="om-state-disabled">25</a><a href="javascript:void(0);" class="om-state-disabled">26</a><a href="javascript:void(0);" class="om-state-disabled">27</a><a href="javascript:void(0);" class="om-state-disabled">28</a><a href="javascript:void(0);" class="om-state-disabled">29</a><a href="javascript:void(0);" class="om-state-disabled">30</a><a href="javascript:void(0);" class="om-state-disabled">31</a><a href="javascript:void(0);" class="om-null">0</a></div></div><div class="om-setime om-state-default hidden"></div><div class="om-cal-ft"><div class="om-cal-time om-state-default">时间：<span class="h">0</span>:<span class="m">0</span>:<span class="s">0</span><div class="cta"><button class="u om-icon om-icon-triangle-1-n"></button><button class="d om-icon om-icon-triangle-1-s"></button></div></div><button class="ct-ok om-state-default">确定</button></div><div class="om-selectime om-state-default hidden"></div></div></div>

<?php 

mysql_close($link);
?>
</body></html>