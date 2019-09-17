<script>
	var provinces = new Array();
	var counts = new Array();
			
</script>

<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
$customer_id = passport_decrypt($customer_id);
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');

mysql_query("SET NAMES UTF8");
$query="SELECT name from weixin_commonshops where isvalid=true and customer_id =".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$shopname=$row->name;
	break;
}
$begintime="";
$endtime ="";
$province ="";
$city ="";
$area ="";
$search_status=-1;
if(!empty($_GET["search_status"])){
   $search_status=$configutil->splash_new($_GET["search_status"]);
}

$query="SELECT location_p,count(distinct o.batchcode) as num,location_c,location_a  from weixin_commonshop_order_addresses a inner join 
weixin_commonshop_orders o on a.batchcode = o.batchcode where o.isvalid = true and o.customer_id =".$customer_id;
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
$query = $query." group by location_p";
//echo $query;
$num=-1;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$location_p=$row->location_p;
	$location_c=$row->location_c;
	$num=$row->num;	
	if(!empty($_GET["province"])){
		$province = $configutil->splash_new($_GET["province"]);	
		$location_p=$province;	
		if(!empty($_GET["city"])){
			$city = $configutil->splash_new($_GET["city"]);	
			$location_p=$province.$city;	
			if(!empty($_GET["area"])){
				$area = $configutil->splash_new($_GET["area"]);	
				$location_p=$province.$city.$area;	
			}
		}
	}
?>
<script>

provinces.push('<?php echo $location_p;?>');
counts.push(<?php echo $num;?>);
</script>
<?php
} 
?>
<?php 
if($num==-1){
	$num=0;
?>
<script>
provinces.push('<?php echo $province.$city.$area;?>');
counts.push(<?php echo $num;?>);
</script>
<?php }?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/main.css" rel="stylesheet" type="text/css">
<link type="text/css" rel="stylesheet" rev="stylesheet" href="../css/icon.css" media="all">
<script type="text/javascript" src="../common/js/jquery-1.7.2.min.js"></script>
<link href="css/shop.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/shop.js"></script>
<link href="css/operamasks-ui.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/operamasks-ui.min.js"></script>
<script type="text/javascript" src="../common/js/highcharts.js"></script>
<script type="text/javascript" src="js/order_BarChart.js"></script>
<script type="text/javascript" src="../js/tis.js"></script>
<script language="javascript">
$(document).ready(shop_obj.orders_init);
</script>
<script>
		Array.prototype.contains = function(item,count,otherArr){
		   var length = this.length;
		   var index = -1;
		   for(var i = 0 ; i < length ; i++){
				if(this[i] == item){
					index = i;
					break;
				}
		   }
		   if(index >=0){
				otherArr[index] = (isNaN(otherArr[index])? 0 : otherArr[index]) + count;
		   }else{
				otherArr[index] = 0;
		   }
		};
			
			/*这是报表要用到的两个数组*/
			var location_p='<?php echo $province;?>';
			if(location_p !=""){
				var oldArr =['<?php echo $province.$city.$area;?>'];
			}else{
				var oldArr = ['河北省', '山东省', '辽宁省', '黑龙江省', '吉林省', '甘肃省', '青海省', '河南省', '江苏省', '湖北省', '湖南省', '江西省', '浙江省', '广东省', '云南省', '福建省', '台湾省', '海南省', '山西省', '四川省', '陕西省', '贵州省', '安徽省', '重庆市', '北京市', '上海市', '天津市', '广西壮族自治区', '内蒙古自治区', '西藏自治区', '新疆维吾尔自治区', '宁夏回族自治区', '香港特别行政区', '澳门特别行政区', '其它'];
			}
			
			var newCounts = new Array();
			for(var i = 0 ; i< oldArr.length ; i++){
				newCounts[i] = 0;
			}
			
			
			for(var i = 0 ; i< provinces.length ; i++){
				oldArr.contains(provinces[i],counts[i],newCounts);
			}
			
			
			//排序
			for(var i=0;i<newCounts.length;i++){
				for(var j=i;j<newCounts.length;j++){
					if(newCounts[i]<newCounts[j]){
						var temp=newCounts[i];
						newCounts[i]=newCounts[j];
						newCounts[j]=temp;
						var temp_2=oldArr[i];
						oldArr[i]=oldArr[j];
						oldArr[j]=temp_2;
					}
				}
			}

</script>
</head>
<style>
.search_time,.excel_orders{
	background: #1584D5;
	color: white;
	border: none;
	height: 22px;
	line-height: 22px;
	width: 80px;
}
</style>
<body>
<div id="orders" class="r_con_wrap" style="min-width:1210px;min-height:50px;padding-bottom:1px;"> 
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
		<input type="button" class="search_time" onclick="search_bar();" value="搜 索">	 
		<input type="button" class="search_time" onclick="search_area();" value="地区列表">	 
		<input type="button" class="excel_orders" onclick="excel_orders();" value="导出订单">	
		<input type="button" class="search_btn button" value="导出飞豆+" onClick="exportFeiDouRecord();" style="cursor:hand"> 		
	</div>
</div>
<?php if(!empty($province) or !empty($city)){?>
	<div id="container" style="width: 90%; height:200px; margin: 0 auto"></div>
<?php }else{?>
	<div id="container" style="width: 90%; height:2000px; margin: 0 auto"></div>
<?php }?>
<script>
function excel_orders(){
	var begintime = document.getElementById("begintime").value;
	var endtime = document.getElementById("endtime").value;
	var province = document.getElementById("province").value;
	var city = document.getElementById("city").value;
	var area = document.getElementById("area").value;
	var search_status = document.getElementById("search_status").value;
	var url='/weixin/plat/app/index.php/Excel/area_order_excel/customer_id/<?php echo $customer_id ?>/status/'+search_status+'/';
	
	if(begintime !=""){
		url=url+'begintime/'+begintime+'/';
	}
	if(endtime !=""){
		url=url+'endtime/'+endtime+'/';
	}
	if(province !=""){
		url=url+'province/'+province+'/';
	}
	if(city !=""){
		url=url+'city/'+city+'/';
	}
	if(area !=""){
		url=url+'area/'+area+'/';
	}

	console.log(url);
	goExcel(url,1,'http://<?php echo $http_host;?>/weixinpl/');
}
function exportFeiDouRecord(){
	var begintime = document.getElementById("begintime").value;
	var endtime = document.getElementById("endtime").value;
	var province = document.getElementById("province").value;
	var city = document.getElementById("city").value;
	var area = document.getElementById("area").value;
	var search_status = document.getElementById("search_status").value;
	var url='/weixin/plat/app/index.php/Excel/area_order_feidou_excel/shopname/<?php echo $shopname;?>/customer_id/<?php echo $customer_id ?>/status/'+search_status+'/';
	
	if(begintime !=""){
		url=url+'begintime/'+begintime+'/';
	}
	if(endtime !=""){
		url=url+'endtime/'+endtime+'/';
	}
	if(province !=""){
		url=url+'province/'+province+'/';
	}
	if(city !=""){
		url=url+'city/'+city+'/';
	}
	if(area !=""){
		url=url+'area/'+area+'/';
	}

	console.log(url);
	goExcel(url,1,'http://<?php echo $http_host;?>/weixinpl/');
}


function search_bar(){
	var begintime = document.getElementById("begintime").value;
	var endtime = document.getElementById("endtime").value;
	var province = document.getElementById("province").value;
	var city = document.getElementById("city").value;
	var area = document.getElementById("area").value;
	var search_status = document.getElementById("search_status").value;
	var url="order_BarChart.php?customer_id=<?php echo passport_encrypt((string)$customer_id) ?>&search_status="+search_status;
	
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

function search_area(){
	var begintime = document.getElementById("begintime").value;
	var endtime = document.getElementById("endtime").value;
	var province = document.getElementById("province").value;
	var city = document.getElementById("city").value;
	var area = document.getElementById("area").value;
	var search_status = document.getElementById("search_status").value;
	var url="order_BarChart_detailed.php?customer_id=<?php echo passport_encrypt((string)$customer_id) ?>&search_status="+search_status;
	if(province ==""){
		alert('请选择地区！');
		return;
	}
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
</body>
</html>