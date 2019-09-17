<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('../common/jssdk.php');
require('../common/common_from.php'); 
$CF = new CheckFrom();
$CF->isFrom($customer_id);
$user_id = $_SESSION['user_id_'.$customer_id];
$user_id = 196272;

$batchcode = -1;
$batchcode = $_POST['batchcode'];
$batchcode = '1962821463635062';

$i = 0;
$pid 		= array();	//产品ID
$is_discuss = -1;		//是否已评论：0未评论，1已评论，2追加评论
$query = "select pid,is_discuss from weixin_commonshop_orders where isvalid=true and batchcode=".$batchcode;
$result = mysql_query($query) or die('query failed'.mysql_error());
while($row = mysql_fetch_object($result)){
	$pid[$i] = $row->pid;
	$is_discuss = $row->is_discuss;
	$i++;
}

$is_pic = 1;	//评论是否上传图片：0否，1是
$query2 = "select is_pic from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result2 = mysql_query($query2) or die('query failed2'.mysql_error());
while($row2 = mysql_fetch_object($result2)){
	$is_pic = $row2->is_pic;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>评价</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta content="no" name="apple-touch-fullscreen">
    <meta name="MobileOptimized" content="320"/>
    <meta name="format-detection" content="telephone=no">
    <meta name=apple-mobile-web-app-capable content=yes>
    <meta name=apple-mobile-web-app-status-bar-style content=black>
    <meta http-equiv="pragma" content="nocache">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
    
    <link type="text/css" rel="stylesheet" href="./assets/css/amazeui.min.css" />
    <link type="text/css" rel="stylesheet" href="./css/order_css/global.css" /> 
    <link type="text/css" rel="stylesheet" href="./css/css_orange.css" />    
    
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
  
</head>

<link rel="stylesheet" href="./css/order_css/style.css" type="text/css" media="all">
<link type="text/css" rel="stylesheet" href="./css/order_css/pingjia.css" />

<body data-ctrl=true>
	<!-- <header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav" onclick="history.go(-1)">
			<img class="am-header-icon-custom icon_back" src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="am-header-title" style="font-size:18px;">评价</h1>
	</header>
    <div class="topDiv"></div> --> <!-- 暂时屏蔽头部 -->
	
	<!-- 基本地区-开始 -->
	<form id="pingjiaForm" action="./pingjiaProcess.php" method="post" enctype="multipart/form-data">
		<div id="mainArea">
			<input type="hidden" name="batchcode" value="<?php echo $batchcode;?>">
			<input type="hidden" name="is_pic" value="<?php echo $is_pic;?>">
			<?php
				$p_num = count($pid);
				for($j=0;$j<$p_num;$j++){
					$query2 = "select default_imgurl,is_supply_id from weixin_commonshop_products where isvalid=true and id=".$pid[$j];
					$result2 = mysql_query($query2) or die('query failed2'.mysql_error());
					while($row2 = mysql_fetch_object($result2)){
						$product_default_imgurl = $row2->default_imgurl;	//产品封面图
						$supply_id = $row2->is_supply_id;					//代理商ID
					}
			?>
			<div class="itemComment" style="width:100%;">
				<input type="hidden" name="pid[]" value="<?php echo $pid[$j];?>">
				<input type="hidden" name="supply_id[]" value="<?php echo $supply_id;?>">
				<div id="middle-tab" style="background-color: white;">		
					<div class="area-one ">			
						<img class="border_goods" onclick="gotoProductDetail(<?php echo $pid[$j];?>)" src="<?php echo $product_default_imgurl;?>">		
					</div>		
					<div class="area-one comment-mark sel" onclick="clickPingjiaMark(this);" data-level="1">			
						<img class="imgSel" src="./images/order_image/icon_comment_good_sel-orange.png">
						<img class="imgDef" src="./images/order_image/icon_comment_good.png">	
						<div>好评</div>		
					</div>		
					<div class="area-one comment-mark" onclick="clickPingjiaMark(this);" data-level="2">			
						<img class="imgSel"  src="./images/order_image/icon_comment_middle_sel-orange.png">
						<img class="imgDef"  src="./images/order_image/icon_comment_middle.png">			
						<div>中评</div>		
					</div>		
					<div class="area-one comment-mark" onclick="clickPingjiaMark(this);" data-level="3">			
						<img class="imgSel"  src="./images/order_image/icon_comment_bad_sel-orange.png"> 
						<img class="imgDef"  src="./images/order_image/icon_comment_bad.png">	
						<div>差评</div>		
					</div>	
					<input type="hidden" name="level[<?php echo $j;?>]" id="level" value="1" />
				</div>	
				<div class="line_gray1"></div>	
				<div class="frame_comment">		
					<div style="padding:15px;">
						<span style="font-weight:bold;">评论</span>
					</div>		
					<div class="frame_textarea">			
						<textarea name="discuss[<?php echo $j;?>]" placeholder="这次商品满意吗？写点心得5-100字。"></textarea>
					</div>	
					<?php if($is_pic){?>
					<div id="image-area<?php echo $j;?>_0" class="frame_image">			
						<div id="add-image<?php echo $j;?>_0" class="area-one">				
							<img id="image<?php echo $j;?>_0" src="./images/order_image/icon_image_add.png" width="90" height="90">	<input type="file" id="addFile<?php echo $j;?>_0" accept="image/*" class="frame_image_select" name="Filedata_<?php echo $j;?>[]" index="<?php echo $j;?>">
						</div>		
					</div>
					<div id="image-area<?php echo $j;?>_1" class="frame_image" style="display:none;">			
						<div id="add-image<?php echo $j;?>_1" class="area-one">				
							<img id="image<?php echo $j;?>_1" src="./images/order_image/icon_image_add.png" width="90" height="90">	<input type="file" id="addFile<?php echo $j;?>_1" accept="image/*" class="frame_image_select" name="Filedata_<?php echo $j;?>[]" index="<?php echo $j;?>">
						</div>		
					</div>	
					<div id="image-area<?php echo $j;?>_2" class="frame_image" style="margin-left:5px;display:none;">			
						<div id="add-image<?php echo $j;?>_2" class="area-one">				
							<img id="image<?php echo $j;?>_2" src="./images/order_image/icon_image_add.png" width="90" height="90">	<input type="file" id="addFile<?php echo $j;?>_2" accept="image/*" class="frame_image_select" name="Filedata_<?php echo $j;?>[]" index="<?php echo $j;?>">
						</div>		
					</div>
					<div class="frame_text">
						<span>最多可上传3张图片</span>
					</div>
					<?php }?>
				</div>	
				<div class="line_gray10"></div>
			</div>
				<?php }?>
		</div>
	
	<!-- 基本地区-终结 -->
		
	<!-- 下面的按钮地区-开始 -->
    <div class="white-list">
        <div class="list-one">
            <div class="left-title"><span >匿名评价</span></div>
            <div class="center-content"></div>
            <div class="right-action"><input type="checkbox" onclick="checkNicheng();" id="checkbox_c1" class="chk_3"/><label for="checkbox_c1"></label></div>
			<input type="hidden" id="is_anonymous" name="is_anonymous" value="0">
        </div>
		<div class="list-one" style="background-color:#eee;">
			<div onclick="tijiao();" class="btn_type1">提交</div>
        </div>
    </div>
	<!-- 下面的按钮地区-终结 -->
  </form>
</body>		
<script type="text/javascript">
	var f_NiCheng = false;					//匿名评价
	var imageCount = new Array();	//Image Count Array
	var p_num = <?php echo $p_num;?>;
	var is_pic = <?php echo $is_pic;?>;
	
	$(function() {
		if(is_pic){
			for (var i=0; i<p_num; i++) {
				imageCount.push(0);
				document.getElementById('addFile' + i + '_0').addEventListener('change', fileSelect_banner, false);
			}
		}
	});
	
	//点击【好评】,【中评】,【差评】
	function clickPingjiaMark(obj){
		var level = $(obj).data("level");
		$(obj).parent().find(".comment-mark").removeClass("sel");
		$(obj).addClass("sel");
		$(obj).parent().find("#level").val(level);
	}
	
	//匿名评价
    function checkNicheng(){
    	if(f_NiCheng){
    		f_NiCheng = false;
			$('#is_anonymous').val(0);
    	}else{
    		f_NiCheng = true;
			$('#is_anonymous').val(1);
    	}
    }
	
	//点击【商品图片】
	function gotoProductDetail(productID){
		window.location.href = "product_detail.php?pid="+productID;
	}
	
	//点击【提交】
	function tijiao(){	
		$("#pingjiaForm").submit();
	}

	//获取本地的图片
	function fileSelect_banner(evt) {
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            var files = evt.target.files;
			var goodsIndex = $(evt.target).attr("index");	//现在选择的商品的index
            var file;
            file = this.files[0];
            if (!file.type.match('image.*')) {
                return;
            }      
            reader = new FileReader();    
            reader.onload = (function (tFile) {
                return function (evt) {
                    dataURL = evt.target.result;
					if(imageCount[goodsIndex] < 3){
						imageCount[goodsIndex]++;
						if(imageCount[goodsIndex] < 3){
							/*var html = $("#image-area"+goodsIndex).html();
							var content = '<div id="add-image' + goodsIndex + '_' + imageCount[goodsIndex] + '" class="area-one" style="left:'+ 10*(imageCount[goodsIndex]+1) +'px;">';
							content += '	<img id="image' + goodsIndex + '_' + imageCount[goodsIndex] + '" onclick="" src="./images/order_image/icon_image_add.png" width="90" height="90">';
							content += '	<input type="file" id="addFile' + goodsIndex + '_' + imageCount[goodsIndex] + '" accept="image/*"  name="Filedata_'+goodsIndex+'[]" index="' + goodsIndex + '" class="frame_image_select" ">';
							content += ' <input type="hidden" class="image" name="img_'+goodsIndex+'_'+imageCount[goodsIndex]+'" value=""/> ';
							content += '</div>';
							$("#image-area" + goodsIndex).html(html+content);*/
							document.getElementById('addFile' + goodsIndex + '_' + imageCount[goodsIndex]).addEventListener('change', fileSelect_banner, false);
						}
						
						$("#image" + goodsIndex + '_' + (imageCount[goodsIndex]-1)).attr("src",dataURL);
						$("#addFile" + goodsIndex + '_' + (imageCount[goodsIndex]-1)).hide();						
						$("#image-area" + goodsIndex + '_' + imageCount[goodsIndex]).show();						
					}
                };
            }(file));
            reader.readAsDataURL(file);
            sendFile = file;
        } else {
            alert('该浏览器不支持文件管理。');
        }
    }
 	
</script>
</body>
</html>