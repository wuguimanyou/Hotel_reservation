<?php
header("Content-type: text/html; charset=utf-8");     
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD); 
mysql_select_db(DB_NAME) or die('Could not select database'); 
require('select_skin.php');

//头文件----start
require('../common/common_from.php');
//头文件----end
/* $owner_id 		= -1;
 $customer_id    = 3243;
$pid            = 1568;
$user_id 	 	= 191099 ; */
$shop_card_id   = -1;
$query	= "select shop_card_id from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('商品归属Query failed2: ' . mysql_error());
while ($row = mysql_fetch_object($result)) {
	$shop_card_id   = $row->shop_card_id;//分销会员卡
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>购物车</title>
    <!-- 模板 -->
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
    
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
    <!-- 模板 -->
    
    <!-- 页联系style-->
    <link type="text/css" rel="stylesheet" href="css/list_css/style.css" />
    <link type="text/css" rel="stylesheet" href="css/goods/global.css" />
	<link type="text/css" rel="stylesheet" href="css/order_css/global.css" />
    <link type="text/css" rel="stylesheet" href="css/goods/order_cart.css" />
    <link type="text/css" rel="stylesheet" href="./css/css_<?php echo $skin ?>.css" />        
	<!-- <link type="text/css" rel="stylesheet" href="./css/goods/product_detail.css?ver=<?php echo time(); ?>" /> -->
    <!-- 页联系style-->
    
    <script src="./js/r_global_brain.js" type="text/javascript"></script>
</head>




<body data-ctrl=true>
	<!-- header部门-->
	<!-- <header data-am-widget="header" class="am-header am-header-default header">
		<div class="am-header-left am-header-nav header-btn">
			<img class="am-header-icon-custom"  src="./images/center/nav_bar_back.png"/><span>返回</span>
		</div>
	    <h1 class="header-title">购物车</h1>
	    <div class="am-header-right am-header-nav">
		</div>
	</header>
	<div class="topDiv" style="height:49px;"></div> -->     <!-- 暂时屏蔽头部 -->
	<!-- header部门-->
	
	<div id = "menu-row1" style="background:#f8f8f8" >
    		<!-- 搜索部门-->
		    <div class = "menu-row1-wrapper">
				<div class = "menu-row1-left1" id = "row1-button1">
					<a href="personal_center.php?customer_id=<?php echo $customer_id_en; ?>">
			    		<div class = "menu-row1-left1-top1">
			    			<img src="./images/firstPage/my_tab_4_sel.png" >
			    		</div>
			    		<div class = "menu-row1-left1-top2">
							<span>个人中心</span>
						</div>
					</a>
			    </div>
			    <div class="menu-row1-left2" style = "">
			        <input id="tvKeyword" class="search-input" onfocus="search();" type="text" placeholder="搜索" >
			    </div>
		    	<div class = "menu-row1-right" id = "row1-button2">
					<a href="class_page.php?customer_id=<?php echo $customer_id_en; ?>">
						<div class = "menu-row1-right-top1">
							<img src="./images/goods_image/2016042901.png" >
						</div>
						<div class = "menu-row1-right-top2">
							<span>分类</span>
						</div>
					</a>
		    	</div>
		    </div>
			<!-- 搜索部门-->
     </div>
     <div style="height:52px;"></div> <!-- 占据搜索框的高度 -->
    <!-- content -->
    <div  class = "content-main" id="containerDiv" >
    	<ul id="resultData" style="width:100%;overflow:auto; margin-top:3px;padding-left:0px;margin-bottom:64px;">

		</ul>
	</div>
    <!-- content -->
    
    
    
    <!-- footer -->
    <div class = "content-footer">
    	<div class = "content-footer-left1">
			<img class = "all-select" src = "./<?php echo $images_skin?>/list_image/checkbox_off.png" >
		</div>
    	<span class = "content-footer-left2">全选</span>
    	
    	<div class = "content-footer-left3">
    		<div class = "content-footer-left3-item1">
    			<div class = "content-footer-left3-item1-top1">
    				<div class = "content-footer-left3-item1-top1-wrapper"><font style="font-size:15px;">总价:</font><font class = "content-footer-left3-item1-top1-font1">￥</font><font class = "content-footer-left3-item1-top1-font2" id = "zongjia">0</font></div>
    			</div>
    			<div class = "content-footer-left3-item1-top2">
    				<div class = "content-footer-left3-item1-top2-wrapper">不含运费</div>
    			</div>
    		</div>
    		<div class = "jiesan-button" onclick="statement();">
    			<span>结算</span>
    		</div>
    	</div>
    </div>
    <!-- footer -->
    
    
    <!-- dialog -->
    <div class="am-share shangpin-dialog" >
        <!-- 加入购买 -->
    	
	  	<!-- 加入购买 -->
	</div>
	
	<!-- 分类dialog-->
	<div id="leftmask" style="display:none;" data-role="none"></div>
	<div class="search_new"  id="seardiv"  style="display:none;" data-role="none">	
	    <ul class="area c-fix" id="industrydiv" style="display:none;">
            <div class="white-list" id="white-price">
            	<!-- 价格区间 List -->
		    </div>
	  	</ul>
	 </div>
    <!-- 分类dialog-->
	<!-- dialog -->
	<div id="cartGood">
	</div>
        <!--悬浮按钮-->
	<?php  include_once('float.php');?>
	<!--悬浮按钮-->
</body>		
<!-- 页联系js -->
<script>
var $skin_img='<?php echo $images_skin?>';
</script>
<script src="js/goods/global.js"></script>
<script src="js/goods/order_cart.js?ver=<?php echo time(); ?>"></script>
<!-- 页联系js -->
<script>
var user_id 		= <?php echo $user_id?>;
var customer_id		= '<?php echo $customer_id_en; ?>';
var shop_card_id	= '<?php echo $shop_card_id; ?>';

</script>
<?php require('../common/share.php');?>
</body>
</html>