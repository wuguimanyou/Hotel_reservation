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
/*
$customer_id=""; 
if(!empty($_GET["customer_id"])){ 
	$customer_id = $configutil->splash_new($_GET["customer_id"]); 
} 
*/ //引入的文件中有获取
$is_distribution=0;//渠道取消代理商功能
//代理模式,分销商城的功能项是 266
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scdl' and c.id=cf.column_id";
//echo $query1;
$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_distribution=1;
}
$is_supplierstr=0;//渠道取消供应商功能
//供应商模式,渠道开通与不开通
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scgys' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('Query failed1: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_supplierstr=1;
}
?>
<style type="text/css">
.r{border-collapse:collapse; width:100%;}
.r td{padding:10px 5px; border:1px solid #d8d8d8; text-align:center; empty-cells:show;  font-size:14px;}
.r td.left{text-align:left;}
.r td.last{border-right:none;}
.r td a{color:#4D88D3;}
.r thead{background:#06a7e1; font-weight:bold;}
.r thead td{color:#ffffff;border:1px solid #06a7e1;}
.r tbody tr:hover{background:#e4f1fc;}
.r tbody td, .r tbody td *{font-size:12px;}
.r tbody td .upd_txt input{width:80px; height:24px; line-height:24px; text-align:center; }

</style>
<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>返佣说明 订单号:<?php echo $batchcode;?></title>
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
	
<link href="css/operamasks-ui.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/operamasks-ui.min.js"></script>
<script type="text/javascript" src="../js/tis.js"></script>
<script language="javascript">

$(document).ready(shop_obj.orders_init);
</script> 
<div id="orders" class="r_con_wrap">
        <div class="search">
		订单号：<?php echo $batchcode; ?>
		</div>
		<table border="0" cellpadding="5" cellspacing="0" class="r" id="order_list">
			<thead>
				<tr>
					<td width="20%" nowrap="nowrap">推广员信息</td>
					<td width="10%" nowrap="nowrap">会员卡编号</td>
					<td width="10%" nowrap="nowrap">佣金状态</td>
					<td width="10%" nowrap="nowrap">佣金/积分</td>
					<td width="10%" nowrap="nowrap">奖励说明</td>
					<td width="20%" nowrap="nowrap">返佣时间</td>
					<td width="15%" nowrap="nowrap">备注</td>
					<!-- <td width="10%" nowrap="nowrap">状态</td> -->
					<!-- <td width="15%" nowrap="nowrap">备注</td> -->
					<!--td width="15%" nowrap="nowrap">发红包</td-->
				</tr>
				
			</thead>
			<tbody>
			   <?php 
			     $query  = "select user_id,reward,card_member_id,createtime,paytype,remark,type from weixin_commonshop_order_promoters where isvalid=true and batchcode=".$batchcode;
				
				 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
				$total_money = 0;
	             while ($row = mysql_fetch_object($result)) {
					$user_id = $row->user_id;
					$sql="select isAgent,is_consume from promoters where isvalid=true and user_id=".$user_id;
					$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
					while ($row3 = mysql_fetch_object($result3)) {
						$isAgent = $row3->isAgent;
						$is_consume = $row3->is_consume;	//判断 0:不是无限级奖励 1:无限级奖励
						break;
					}
					$sql="select is_team,is_shareholder from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
					$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
					while ($row3 = mysql_fetch_object($result3)) {
						$is_team = $row3->is_team;					//是否开启团队奖励
						$is_shareholder = $row3->is_shareholder;	//是否开启股东分红奖励
						break;
					}
					$query4="select a_name,b_name,c_name,d_name from weixin_commonshop_shareholder where isvalid=true and customer_id=".$customer_id." limit 0,1";
					$result4 = mysql_query($query4);
					while($row4 = mysql_fetch_object($result4)){
						$a_name=$row4->a_name;
						$b_name=$row4->b_name;
						$c_name=$row4->c_name;
						$d_name=$row4->d_name;
					}
					
					$consume_name ="";
					if($is_team==1 && $is_shareholder==0){
						if($is_consume>0){
							$consume_name = "(无限级奖励)";
						}
					}else if($is_shareholder==1){
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
					
					$reward = $row->reward;
					$card_member_id = $row->card_member_id;
					$createtime = $row->createtime;
					$paytype = $row->paytype;
					$remark = $row->remark;
					$type = $row->type;//团队奖励
					switch($type){
						case 0:
						$type_name="分销奖励";
						break;
						case 1:
						$type_name="团队奖励";
						break;
						case 2:
						$type_name="股东奖励";
						break;
					}
					$sql="select customer_red_id from weixin_red_log where isvalid=true and deal_id=".$batchcode;
					$result3 = mysql_query($sql) or die('Query failed: ' . mysql_error());
					while ($row3 = mysql_fetch_object($result3)) {
						$customer_red_id = $row3->customer_red_id;
					}
					if($paytype == 0){
						$paytpyestr= "已支付";
					}else if($paytype == 1){
						$paytpyestr= "<span style='color:red'>已到会员卡</span>";
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
				    $sql = "select name,weixin_name from weixin_users where isvalid=true and id=".$user_id." and customer_id=".$customer_id;
					$result2 = mysql_query($sql) or die('Query failed: ' . mysql_error()); 
					while ($row2 = mysql_fetch_object($result2)) { 
						$name = $row2->name; 
						$weixin_name = $row2->weixin_name;  
					} 
					 
					$total_money=$total_money+$reward;
					
			   ?> 
                <tr>
				   <td>
					   <a href="qrsell.php?exp_user_id=<?php echo $user_id; ?>&customer_id=<?php echo $customer_id_en; ?>"><?php echo $weixin_name; ?>(<?php echo $user_id; ?>)</a>
					   </br><?php echo $user_name;?>
					   </br><?php echo $consume_name;?>
					   </td>  
				   <td><?php echo $card_member_id; ?></td>
				   <td><?php echo $paytpyestr; ?>
				   <?php if($paytype==3){?>
				   </br>(<?php echo $customer_red_id?>)
				   <?php } ?>
				   </td>
				   <td><?php echo $reward; ?></td>
				   <td><?php echo $type_name; ?></td>
				   <td><?php echo $createtime; ?></td>
				   <td><?php echo $remark; ?></td>
				   
                </tr>				
			   <?php } ?>
			  <tr>
				  <td colspan="8" style="text-align:left;padding:10px 0px 10px 10px">
					总返佣：<span style="font-size:20px;color:red;"><?php echo $total_money; ?>元
				  </td>
			  </tr>
			</tbody>
		</table>
		<div class="blank20"></div>
		<div id="turn_page"></div>
	</div>	</div>
<div>
</div></div><div style="top: 101px; position: absolute; background-color: white; z-index: 2000; left: 398px; visibility: hidden; background-position: initial initial; background-repeat: initial initial;" class="om-calendar-list-wrapper om-widget om-clearfix om-widget-content multi-1"><div class="om-cal-box" id="om-cal-4381460996810347"><div class="om-cal-hd om-widget-header"><a href="javascript:void(0);" class="om-prev "><span class="om-icon om-icon-seek-prev">Prev</span></a><a href="javascript:void(0);" class="om-title">2014年1月</a><a href="javascript:void(0);" class="om-next "><span class="om-icon om-icon-seek-next">Next</span></a></div><div class="om-cal-bd"><div class="om-whd"><span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span></div><div class="om-dbd om-clearfix"><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);">1</a><a href="javascript:void(0);">2</a><a href="javascript:void(0);">3</a><a href="javascript:void(0);">4</a><a href="javascript:void(0);">5</a><a href="javascript:void(0);">6</a><a href="javascript:void(0);">7</a><a href="javascript:void(0);">8</a><a href="javascript:void(0);" class="om-state-highlight om-state-nobd">9</a><a href="javascript:void(0);" class="om-state-disabled">10</a><a href="javascript:void(0);" class="om-state-disabled">11</a><a href="javascript:void(0);" class="om-state-disabled">12</a><a href="javascript:void(0);" class="om-state-disabled">13</a><a href="javascript:void(0);" class="om-state-disabled">14</a><a href="javascript:void(0);" class="om-state-disabled">15</a><a href="javascript:void(0);" class="om-state-disabled">16</a><a href="javascript:void(0);" class="om-state-disabled">17</a><a href="javascript:void(0);" class="om-state-disabled">18</a><a href="javascript:void(0);" class="om-state-disabled">19</a><a href="javascript:void(0);" class="om-state-disabled">20</a><a href="javascript:void(0);" class="om-state-disabled">21</a><a href="javascript:void(0);" class="om-state-disabled">22</a><a href="javascript:void(0);" class="om-state-disabled">23</a><a href="javascript:void(0);" class="om-state-disabled">24</a><a href="javascript:void(0);" class="om-state-disabled">25</a><a href="javascript:void(0);" class="om-state-disabled">26</a><a href="javascript:void(0);" class="om-state-disabled">27</a><a href="javascript:void(0);" class="om-state-disabled">28</a><a href="javascript:void(0);" class="om-state-disabled">29</a><a href="javascript:void(0);" class="om-state-disabled">30</a><a href="javascript:void(0);" class="om-state-disabled">31</a><a href="javascript:void(0);" class="om-null">0</a></div></div><div class="om-setime om-state-default hidden"></div><div class="om-cal-ft"><div class="om-cal-time om-state-default">时间：<span class="h">0</span>:<span class="m">0</span>:<span class="s">0</span><div class="cta"><button class="u om-icon om-icon-triangle-1-n"></button><button class="d om-icon om-icon-triangle-1-s"></button></div></div><button class="ct-ok om-state-default">确定</button></div><div class="om-selectime om-state-default hidden"></div></div></div><div style="top: 101px; position: absolute; background-color: white; z-index: 2000; left: 564px; visibility: hidden; background-position: initial initial; background-repeat: initial initial;" class="om-calendar-list-wrapper om-widget om-clearfix om-widget-content multi-1"><div class="om-cal-box" id="om-cal-8113757355604321"><div class="om-cal-hd om-widget-header"><a href="javascript:void(0);" class="om-prev "><span class="om-icon om-icon-seek-prev">Prev</span></a><a href="javascript:void(0);" class="om-title">2014年1月</a><a href="javascript:void(0);" class="om-next "><span class="om-icon om-icon-seek-next">Next</span></a></div><div class="om-cal-bd"><div class="om-whd"><span>日</span><span>一</span><span>二</span><span>三</span><span>四</span><span>五</span><span>六</span></div><div class="om-dbd om-clearfix"><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);" class="om-null">0</a><a href="javascript:void(0);">1</a><a href="javascript:void(0);">2</a><a href="javascript:void(0);">3</a><a href="javascript:void(0);">4</a><a href="javascript:void(0);">5</a><a href="javascript:void(0);">6</a><a href="javascript:void(0);">7</a><a href="javascript:void(0);">8</a><a href="javascript:void(0);" class="om-state-highlight om-state-nobd">9</a><a href="javascript:void(0);" class="om-state-disabled">10</a><a href="javascript:void(0);" class="om-state-disabled">11</a><a href="javascript:void(0);" class="om-state-disabled">12</a><a href="javascript:void(0);" class="om-state-disabled">13</a><a href="javascript:void(0);" class="om-state-disabled">14</a><a href="javascript:void(0);" class="om-state-disabled">15</a><a href="javascript:void(0);" class="om-state-disabled">16</a><a href="javascript:void(0);" class="om-state-disabled">17</a><a href="javascript:void(0);" class="om-state-disabled">18</a><a href="javascript:void(0);" class="om-state-disabled">19</a><a href="javascript:void(0);" class="om-state-disabled">20</a><a href="javascript:void(0);" class="om-state-disabled">21</a><a href="javascript:void(0);" class="om-state-disabled">22</a><a href="javascript:void(0);" class="om-state-disabled">23</a><a href="javascript:void(0);" class="om-state-disabled">24</a><a href="javascript:void(0);" class="om-state-disabled">25</a><a href="javascript:void(0);" class="om-state-disabled">26</a><a href="javascript:void(0);" class="om-state-disabled">27</a><a href="javascript:void(0);" class="om-state-disabled">28</a><a href="javascript:void(0);" class="om-state-disabled">29</a><a href="javascript:void(0);" class="om-state-disabled">30</a><a href="javascript:void(0);" class="om-state-disabled">31</a><a href="javascript:void(0);" class="om-null">0</a></div></div><div class="om-setime om-state-default hidden"></div><div class="om-cal-ft"><div class="om-cal-time om-state-default">时间：<span class="h">0</span>:<span class="m">0</span>:<span class="s">0</span><div class="cta"><button class="u om-icon om-icon-triangle-1-n"></button><button class="d om-icon om-icon-triangle-1-s"></button></div></div><button class="ct-ok om-state-default">确定</button></div><div class="om-selectime om-state-default hidden"></div></div></div>

<?php 

mysql_close($link);
?>
</body></html>