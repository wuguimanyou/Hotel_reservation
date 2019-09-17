<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link =mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../../../proxy_info.php');
$head	  	 = 0;
$pagenum  	 = 1;
if(!empty($_GET["pagenum"])){
	$pagenum = $configutil->splash_new($_GET["pagenum"]);	
}
$product_id = $configutil->splash_new($_GET["product_id"]);

$baseurl = "http://".$http_host;

$query = "select pr.pid,wcp.name,wcp.default_imgurl from products_relation_t pr right JOIN weixin_commonshop_products wcp on wcp.id = pr.pid  where wcp.isvalid = true and pr.parent_pid=".$product_id;
$result = mysql_query($query) or die("query3 error : ".mysql_error());
$pro_num = mysql_num_rows($result);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>产品关联</title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Product/product/product_relation.css">
<script type="text/javascript" src="../../../common/js_V6.0/assets/js/jquery.min.js"></script>
<script charset="utf-8" src="../../../common/js/jquery.jsonp-2.2.0.js"></script>
<script charset="utf-8" src="../../../common/js/layer/V2_1/layer.js"></script>
<script type="text/javascript" src="../../Common/js/Product/product/product_relation.js"></script>
<script>
layer.config({
    extend: '/extend/layer.ext.js'
});  
</script> 
</head>

<body>
<!-- <div class="batchFinish"></div>
<div class="wait_div">
	<i class="wx_loading_icon"></i>
	<p class="wait">请等待...</p>
</div> -->
<div id="add_pro_div" style="display:none" class="div_out">
	<div class="sel_pro">
		<p>
			<span>产品分类:</span>
			<select id="parent_types_select" name="parent_types_select"  onchange="parentTypesSelect(this.value);">
				<option value=-1>--请选择--</option>
				<?php 
				//取一级菜单
				$query2="select id,name from weixin_commonshop_types where isvalid=true and is_shelves=1 and customer_id=".$customer_id;
				$result_2 = mysql_query($query2) or die('Query failed: ' . mysql_error());

				while ($row_2 = mysql_fetch_object($result_2)) {
					$type_id 	= $row_2->id;
					$type_name	= $row_2->name;
				?>
				<option value=<?php echo $type_id; ?> ><?php echo $type_name; ?></option> 

				<?php } ?>
			</select>
		</p>
		<p class="sel_pro_p">
			<span>产&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;品:</span>
			<select id="parent_pid_select" name="parent_pid_select">
			</select>
		</p>
	</div>
	<input type="button" value="确定" class="btn" id="btn_savered" onclick="relation_pro()" style="margin-top:5%"/>&nbsp;&nbsp;&nbsp;
	<input type="button" value="取消" class="btn graybtn" name="btn_cancel" onclick="cancelBtn2(this)" style="margin-top:5%"/>&nbsp;&nbsp;&nbsp;
</div>
<div class="WSY_columnbox">
	<?php require('public/head.php');?>
	<div class="WSY_list">	
			<div class="material">
				<div class="material_left">
					<dl class="material_dlcon">
						<?php
						if( $pro_num < 13 ){
						?>
						<dd class="dd06">
							<button id="add_pro_button" onclick="add_pro()">添加产品</button>
						</dd>
						<?php 
						}
						?>
					</dl>
					<dl class="material_con">
					<?php
						$pid			= -1;
						$name			= "";
						$default_imgurl	= "";
						$query = "select pr.pid,wcp.name,wcp.default_imgurl from products_relation_t pr right JOIN weixin_commonshop_products wcp on wcp.id = pr.pid  where wcp.isvalid = true and pr.isvalid=true and pr.parent_pid=".$product_id;
						$result=mysql_query($query) or die ("query faild" .mysql_error());
						while ($row = mysql_fetch_object($result)) {
							$pid			=  $row->pid;
							$name			=  $row->name;
							$default_imgurl	=  $row->default_imgurl;
					?>
					
						<dd id="dd_pic" class="dd_<?php echo $pid;?>">
							<span class="pic" >
								<img src="<?php echo $default_imgurl;?>" title="">
							</span>
							<span class="span_input">
								<p class="crew_name" id="em_<?php echo $pid;?>"><?php echo $name;?></p>
							</span>
							<div class="div_imga">
								<a>
									<img src="../../../common/images_V6.0/operating_icon/icon04.png" title="删除" onclick="doDeleteCrew(<?php echo $pid;?>,<?php echo $product_id;?>)">
								</a>
							</div>
						</dd>
					
					<?php
						}
					?>
					</dl>
				</div>
			</div>
		</div>
</div>
<script>
page_index = 0;
var customer_id = '<?php echo $customer_id_en; ?>';
var product_id = '<?php echo $product_id; ?>';
</script>


</body>
</html>
