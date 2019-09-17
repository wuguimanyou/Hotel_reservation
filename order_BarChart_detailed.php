<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
   $customer_id = $configutil->splash_new($_GET["customer_id"]);
   $customer_id = passport_decrypt($customer_id);
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');

mysql_query("SET NAMES UTF8");


$pagenum = 1;
$pagesize = 20;
$begintime="";
$endtime ="";
if(!empty($_GET["pagenum"])){
   $pagenum = $configutil->splash_new($_GET["pagenum"]);
}
$start = ($pagenum-1) * $pagesize;
$end = $pagesize;


$begintime="";
$endtime ="";
$province ="";
$city ="";
$area ="";
$search_status=-1;
if(!empty($_GET["search_status"])){
   $search_status=$configutil->splash_new($_GET["search_status"]);
}
$sql="SELECT  o.totalprice,o.batchcode,o.paystyle,o.paystatus,o.sendstatus,o.createtime from weixin_commonshop_order_addresses a inner join 
weixin_commonshop_orders o on a.batchcode = o.batchcode where o.isvalid = true and o.customer_id =".$customer_id;

$sql_sum="SELECT  sum(o.totalprice) as totalprices from weixin_commonshop_order_addresses a inner join 
weixin_commonshop_orders o on a.batchcode = o.batchcode where o.isvalid = true and o.customer_id =".$customer_id;

$query_count="select count(distinct o.batchcode) as tcount from weixin_commonshop_order_addresses a inner join 
weixin_commonshop_orders o on a.batchcode = o.batchcode where o.isvalid = true and o.customer_id =".$customer_id;

$query="";
if(!empty($_GET["begintime"])){
   $begintime = $configutil->splash_new($_GET["begintime"]);
    $query = $query." and UNIX_TIMESTAMP(o.createtime)>=".strtotime($begintime);
}
if(!empty($_GET["endtime"])){
   $endtime = $configutil->splash_new($_GET["endtime"]);
    $query = $query." and UNIX_TIMESTAMP(o.createtime)<".strtotime($endtime);
}
switch($search_status){
	case 1:
	//已确认
		$query = $query." and o.status=1";					   
		break;
	case 2:
	//未确认
		$query = $query." and o.status=0";					   
		break;
	case 3:
	//未确认
		$query = $query." and o.paystatus=1";					   
		break;
	case 4:
	//未确认
		$query = $query." and o.paystatus=0";					   
		break;
	case 5:
	//已发货
		$query = $query." and (o.sendstatus=1 or o.sendstatus=2)";		 			
		break;
	case 6:
	//未确认
		$query = $query." and o.sendstatus=0";					   
		break;
	case 7:
	//已退货
		$query = $query." and o.sendstatus=3";					   
		break;
	case 8:
	//已取消
		$query = $query." and o.status=-1";
		break;
}
if(!empty($_GET["province"])){
   $province = $configutil->splash_new($_GET["province"]);	   
   $query = $query." and a.location_p='".$province."'";
   if(!empty($_GET["city"])){
	   $city = $configutil->splash_new($_GET["city"]);
	   $query = $query." and a.location_c='".$city."'";
	   if(!empty($_GET["area"])){
		   $area = $configutil->splash_new($_GET["area"]);
		   $query = $query." and a.location_a='".$area."'";
	   }
   }		
}
$sql = $sql.$query;
$sql = $sql." order by o.id desc"." limit ".$start.",".$end;
$sql_sum = $sql_sum.$query;
$result = mysql_query($sql_sum) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$totalprices=$row->totalprices;
	$totalprices =round($totalprices,2); 
	
}
$query_count = $query_count.$query;
$result = mysql_query($query_count) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$tcount=$row->tcount;
	
}
?>
<!DOCTYPE html>

<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/global.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/icon.css" media="all">
<script type="text/javascript" src="../common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/global.js"></script>
<script type="text/javascript" src="../common/utility.js" charset="utf-8"></script>
<script type="text/javascript" src="../common/js/jquery.blockUI.js"></script>
<script charset="utf-8" src="../common/js/jquery.jsonp-2.2.0.js"></script>
	

</head>

<body>

<style type="text/css">
body, html{background:url(images/main-bg.jpg) left top fixed no-repeat;}
.search_time{
	background: #1584D5;
	color: white;
	border: none;
	height: 22px;
	line-height: 22px;
	width: 80px;
}
</style>

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
<div id="orders" class="r_con_wrap" style="min-width:1210px;"> 
	<div class="search" id="search_form">
		下单时间：
		<span class="om-calendar om-widget om-state-default">
			<input type="text" class="input" id="begintime" name="AccTime_S" value="<?php echo $begintime; ?>" maxlength="20" id="K_1389249066532">
			<span class="om-calendar-trigger"></span></span>-<span class="om-calendar om-widget om-state-default">
			<input type="text" class="input" id="endtime" name="AccTime_E" value="<?php echo $endtime; ?>" maxlength="20" id="K_1389249066580">
			<span class="om-calendar-trigger"></span>
		</span>&nbsp;   
		省:<select name="province" id="province" style="width:125px;border: 1px solid #CFCBCB;height: 36px;margin-bottom: 5px;"></select>
		市:<select name="city" id="city" style="width:125px;border: 1px solid #CFCBCB;height: 36px;margin-bottom: 5px;"></select>
		区<select name="area" id="area" style="width:125px;border: 1px solid #CFCBCB;height: 36px;margin-bottom: 5px;"></select>
		<script src="js/region_select.js"></script>
		<script type="text/javascript">
			new PCAS('province', 'city', 'area', '<?php echo $province;?>', '<?php echo $city;?>', '<?php echo $area;?>');
		</script>
		订单状态：
		<select name="search_status" id="search_status" style="width:100px;">
			<option value="-1">--请选择--</option>
			<option value="1" <?php if($search_status==1){ ?>selected <?php } ?>>已确认</option>
			<option value="2" <?php if($search_status==2){ ?>selected <?php } ?>>待确认</option>
			<option value="3" <?php if($search_status==3){ ?>selected <?php } ?>>已支付</option>
			<option value="4" <?php if($search_status==4){ ?>selected <?php } ?>>未支付</option>
			<option value="5" <?php if($search_status==5){ ?>selected <?php } ?>>已发货</option>
			<option value="6" <?php if($search_status==6){ ?>selected <?php } ?>>未发货</option>
			<option value="7" <?php if($search_status==7){ ?>selected <?php } ?>>申请退货</option>
			<option value="8" <?php if($search_status==8){ ?>selected <?php } ?>>已取消</option>			
		</select>
		<input type="button" class="search_time" style="width:50px;" onclick="search_bar();" value="搜 索">	 
	</div>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="order_list">
			<thead>
				<tr style="background: #fff;">
					<td colspan="9">
					总共金额:<span style="color:red;font-size:22px;"><?php echo $totalprices;?></span>元
					</td>
				</tr>
				<tr>
					<td width="8%" nowrap="nowrap">订单号</td>
					<td width="8%" nowrap="nowrap">金额</td>
					<td width="8%" nowrap="nowrap">支付方式</td>
					<td width="12%" nowrap="nowrap">支付状态</td>
					<td width="8%" nowrap="nowrap">订单状态</td>
					<td width="8%" nowrap="nowrap">下单时间</td>
					<!-- <td width="8%" nowrap="nowrap">类型</td>
					<td width="8%" nowrap="nowrap">确认时间</td>
					<td width="8%" nowrap="nowrap">红包金额</td>
					<td width="12%" nowrap="nowrap">备注</td> -->
				</tr>
			</thead>
			<tbody>
			<?PHP
				
				
				//echo $sql;
				$totalprice_ye=0;
				$result1 = mysql_query($sql) or die('Query failed1: ' . mysql_error());
					while ($row = mysql_fetch_object($result1)) {
						$totalprice=$row->totalprice;
						$totalprice_ye=$totalprice_ye+$totalprice;
						$batchcode=$row->batchcode;
						$sendstatus=$row->sendstatus;
						$paystyle=$row->paystyle;
						$paystatus=$row->paystatus;
						$createtime=$row->createtime;
						$paystatus_str="未支付";
						if($paystatus==1){
							$paystatus_str="已支付";
						}
						
						$sendstatusstr="未发货";	
					switch($sendstatus){
					   case 1:
					       $sendstatusstr="已发货";	
					       break;
					   case 2:
					       $sendstatusstr="顾客已收货";	 
						   break;
					   case 3:
					       $sendstatusstr="顾客已退货";	 
						   break;
						case 4:
					       $sendstatusstr="退货已确认";	 
						   break;
						case 5:
					       $sendstatusstr="顾客申请退款";	 
						   break;
						case 6:
					       $sendstatusstr="退款完成";	 
						   break;
					}
						
			?>
				<tr>
					<td><?php echo $batchcode;?></td>
					<td><?php echo $totalprice;?></td>
					<td><?php echo $paystyle;?></td>
					<td><?php echo $paystatus_str;?></td>
					<td><?php echo $sendstatusstr;?></td>
					<td><?php echo $createtime;?></td>
					<!-- <td><?php echo $type_name;?></td>
					<td><?php echo $createtime;?></td>
					<td><?php echo $red_money;?></td>
					<td><?php echo $remark;?></td> -->
				</tr>
			<?PHP }
				$totalprice_ye =round($totalprice_ye,2); 
			?> 
				 <tr>
					<td colspan="9">
					当前页总金额:<span style="color:red;font-size:22px;"><?php echo $totalprice_ye;?></span>元
					</td>
				</tr>
			   <tr>
			      <td colspan=12>
				  <div class="tcdPageCode"></div>
				 </td>
			   </tr>
			</tbody>
			
		</table>
		<div class="blank20"></div>
		<div id="turn_page"></div>
	</div>	</div>
<div>
</div></div>
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/fenye/fenye.css" media="all">
<script src="../js/fenye/jquery.page.js"></script>
<script>
var pagenum = <?php echo $pagenum ?>;
var rcount_q = <?php echo $tcount ?>;
var end = <?php echo $end ?>;
var count =Math.ceil(rcount_q/end);//总页数

//pageCount：总页数
//current：当前页
 $(".tcdPageCode").createPage({
	pageCount:count,
	current:pagenum,
	backFn:function(p){
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;
		var province = document.getElementById("province").value;
		var city = document.getElementById("city").value;
		var area = document.getElementById("area").value;
		var search_status = document.getElementById("search_status").value;
		url= "order_BarChart_detailed.php?pagenum="+p+"&customer_id=<?php echo passport_encrypt((string)$customer_id) ?>";
		if(province !=""){
			url=url+'&province='+province;
		}
		if(city !=""){
			url=url+'&city='+city;
		}
		if(area !=""){
			url=url+'&area='+area;
		}
		if(begintime !=""){
			url=url+'&begintime='+begintime;
		}
		if(endtime !=""){
			url=url+'&endtime='+endtime;
		}
		document.location=url;
   }
}); 
function search_bar(){
	var begintime = document.getElementById("begintime").value;
	var endtime = document.getElementById("endtime").value;
	var province = document.getElementById("province").value;
	var city = document.getElementById("city").value;
	var area = document.getElementById("area").value;
	var search_status = document.getElementById("search_status").value;
	var url="order_BarChart_detailed.php?customer_id=<?php echo passport_encrypt((string)$customer_id) ?>&search_status="+search_status;
	
 	if(province !=""){
		url=url+'&province='+province;
	}
	if(city !=""){
		url=url+'&city='+city;
	}
	if(area !=""){
		url=url+'&area='+area;
	}
	if(begintime !=""){
		url=url+'&begintime='+begintime;
	}
	if(endtime !=""){
		url=url+'&endtime='+endtime;
	}
	document.location=url;
}
</script>

<?php 

mysql_close($link);
?>
</body></html>