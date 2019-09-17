<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

//头文件  调试请关闭此文件----start
require('../common/common_from.php');
//头文件  调试请关闭此文件----end

$num = 0;
$query = "select count(1) as num from package_order_t where isvalid=true and paystatus=1 and customer_id=" .$customer_id. " and user_id=".$user_id;
$result = mysql_query($query) or die('Query failed1: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$num = $row -> num;
}


$package_id   = -1;//礼包id
$package_name = "";//礼包名
$batchcode    = -1;//订单号d
$totalprice   = 0;//总价
$rcount       = -1;//数量
$createtime   = -1;//创建时间
$sendstatus   = -1;//发货状态
$query = "select p_id,batchcode,package_name,rcount,totalprice,createtime,sendstatus from package_order_t where isvalid=true and paystatus=1 and customer_id=" .$customer_id. " and user_id=".$user_id." order by id desc";
$result = mysql_query($query) or die('Query failed1: ' . mysql_error());

?>
<!DOCTYPE html>
<html>
	<head>
		<title>礼包订单</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <meta content="telephone=no" name="format-detection">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="css/package/order_packages_list.css">
		<script type="text/javascript" src="../common/js/jquery-2.1.0.min.js"></script>
		<script charset="utf-8" src="../common/js/jquery.jsonp-2.2.0.js"></script>
		<script charset="utf-8" src="../common/js/layer/V2_1/layer.js"></script>
		<script>
		layer.config({
			extend: '/extend/layer.ext.js'
		});  
		$(function(){
			var num = <?php echo $num;?>;
			if( 0 == num ){
				layer.msg('你还没有礼包！');
			}   
		});
		</script> 
		<style>
        .layui-layer-dialog {margin-left: -30px;}
        </style>
	</head>
	<body>
		<?php
		while ($row = mysql_fetch_object($result)) {
			$package_id   = $row -> p_id;
			$package_name = $row -> package_name;
			$batchcode    = $row -> batchcode;
			$rcount       = $row -> rcount;
			$totalprice   = $row -> totalprice;
			$createtime   = $row -> createtime;
			$sendstatus   = $row -> sendstatus;
			
			switch($sendstatus){
				case 0:
					//未发货；
					$sendstatus_str = "未发货" ;
					break;
				case 1:
					//已发货;
					$sendstatus_str = "已发货" ;
					break;
				case 2:
					//已收货;
					$sendstatus_str = "已收货" ;
					break;
			}
			$expressnum = "";
			$sql = "SELECT expressname,expressnum from package_order_express_t  where customer_id=".$customer_id." and batchcode=".$batchcode;
			//echo $sql;
			$result_sql = mysql_query($sql) or die('Query failed1: ' . mysql_error());
			while ($row_sql = mysql_fetch_object($result_sql)) {
				$expressnum = $row_sql -> expressnum;
				$expressname = $row_sql -> expressname;
			}
			
			$query_img = "SELECT default_imgurl from package_list_t where customer_id=".$customer_id." and id=".$package_id;
			$result_img = mysql_query($query_img) or die('Query failed1: ' . mysql_error());
			while ($row_img = mysql_fetch_object($result_img)) {
				$default_imgurl = $row_img -> default_imgurl;
			}
		?>
			<div class="bg-w">
				<div class="div-box-2">
					<div class="time">
						<span><?php echo $batchcode; ?> &nbsp;<span id="status_<?php echo $batchcode; ?>"><?php echo $sendstatus_str; ?></span></span>
					</div>
					<span class="icon-p"></span>
					<img src="<?php echo $default_imgurl; ?>" class="product">
					<div class="content-box-2">
						<p class="name-2"><?php echo $package_name; ?></p>
						<p class="num-2">X<?php echo $rcount; ?></p>
						<p class="price-2">￥<?php echo $totalprice; ?></p>
						<span class="zfb"><img src="images/wechatpay.png"></span>
					</div>
				</div>
				<div class="state-box">
					<span class="g-text mr10"><?php echo $createtime; ?></span>
					<span class="g-text mr5">共<?php echo $rcount; ?>件商品</span>
					<p class="g-text">实付：<span class="r-text">￥<?php echo $totalprice; ?></span></p>
					<p class="b-text"><span class='icon-car'></span>
						<a href="http://m.kuaidi100.com/index_all.html?type=<?php echo $expressname;?>&postid=<?php echo $expressnum;?>">
							<?php echo $expressnum; ?>
						</a>
					</p>
					<?php
					if( 1 == $sendstatus ){
					?>
					<button class="r-button" id="confirm_<?php echo $batchcode; ?>" onclick="order_confirm('<?php echo $batchcode; ?>')">确认收货</button>
					<?php } ?>
				</div>    
			</div>
        <?php } ?>
    </body>
	
	<script>
	function order_confirm(batchcode){
		layer.confirm('您确定要收货 订单:'+batchcode+' 吗？<br/>确认后无法撤销！', {
		btn: ['确认','取消'] 
		}, function(confirm){	
			$.ajax({
				url: "save_order_confirm.php",
				type:"POST",
				data:{'batchcode':batchcode},
				dataType:"json",
				success: function(res){
					if(res.status==0){
						$("#confirm_"+batchcode).remove();
						$("#status_"+batchcode).html('已收货');
					}
					layer.alert(res.msg);
				},	
				error:function(res){
					layer.close(confirm);
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
		<!--引入侧边栏 start-->
<?php  include_once('float.php');?>
<!--引入侧边栏 end-->
</html>
