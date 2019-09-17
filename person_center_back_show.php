<?php
header("Content-type: text/html; charset=utf-8"); 
$type = $_GET['type'];
$arr = explode('_',$type);

$sys_num = $arr[0];
$show_hide = $arr[1];
?>
<!DOCTYPE html>
<html>
<head>
    <title>个人中心</title>
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
    
    <script type="text/javascript" src="./assets/js/jquery.min.js"></script>    
    <script type="text/javascript" src="./assets/js/amazeui.js"></script>
    <script type="text/javascript" src="./js/global.js"></script>
    <script type="text/javascript" src="./js/loading.js"></script>
    <script src="./js/jquery.ellipsis.js"></script>
    <script src="./js/jquery.ellipsis.unobtrusive.js"></script>
    <link type="text/css" rel="stylesheet" href="./css/self_dialog.css" />
    <link type="text/css" rel="stylesheet" href="./css/personal.css" />
    
</head>

<!-- Loading Screen -->
<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif' style="width:40px;"/><p class=""></p></div>


<link type="text/css" rel="stylesheet" href="./css/basic.css" />

<body data-ctrl=true style="background:#f8f8f8;">
	<header data-am-widget="header" class="am-header am-header-default">
		<div class="am-header-left am-header-nav" onclick="goBack();">
			<img class="am-header-icon-custom" src="./images/center/nav_bar_back.png" style="vertical-align:middle;"/><span style="margin-left:5px;">返回</span>
		</div>
	    <h1 class="am-header-title" style="font-size:18px;">个人中心</h1>
	</header>
	
	
	
	
    <div class="topDiv"></div>
    <div id="wodeInfoDiv" style="position:relative; background-color:black;color:white;">
            <div class="info-one" style="width:100%;text-align: center;padding-top:15px;padding-bottom:10px;">
                    <img class="am-img-thumbnail am-circle" src="./images/temp/vic/portrait.png">
            </div>
            <div class="my_info" style="font-size:16px;font-weight:bold;">
            	<span>Daniel luis</span>
            	<img src="./images/info_image/iconfont-gerendengji7.png" style="width:18px;height:16px;">
            </div>
            <div class="my_info"style="font-size:13px;"><span>15202525225</span></div>
            <div class="my_info" style="font-size:13px;">
            	<a href="" style="text-decoration:underline;color:white;">推荐人：小一想</a>
            </div>
            <div id="wode_member">
            	<div class="mem" style="text-align:right;"><img src="./images/info_image/wode_icon1.png"></img><span>大众会员</span></div>
            	<div class="mem" style="text-align:center;"><img src="./images/info_image/wode_icon2.png"></img><span>喵喵会员</span></div>
            	<div class="mem" style="text-align:left;"><img src="./images/info_image/wode_icon3.png" ></img><span >鱼鱼会员</span></div>
            </div>
            <img id="editBanner" src="./images/vic/edit_banner.png" onclick="viewMyInfo();"/>
    </div>
    
    <div style="background-color: white;border-bottom: 1px solid #DEDBD5;border-top: 1px solid #DEDBD5;">
        <div style="clear: both;"></div>
        <div id="detail-count" style="padding:5px 0px 10px;">
            <div class="area-one">
                <div class="left">1855</div>
                <div class="right">零钱</div>
            </div>
            <div class="area-line"></div>
            <div class="area-one">
                <div class="left">1855</div>
                <div class="right">积分</div>
            </div>
            <div class="area-line"></div>
            <div class="area-one">
                <div class="left">1855</div>
                <div class="right">消费总额</div>
            </div>
        </div>
    </div>
    <div class="white-list" style="" id="type_1">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;" onclick="viewShopOrder('all');">
            <div class="left-title" style="width:10%;"><img src="./images/info_image/s_order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:65%;"><span style="color:#494949;">商城订单</span></div>
           <div class="right-action"><img src="./images/vic/right_arrow.png" class="right_arrow" alt=""></div>
        </div>
        
            <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;">
	            <div id="dai_fukuan" onclick="viewShopOrder('fukuan');" class="area-one" style="width:19%!important;">
	                <img src="./images/info_image/dai_fukuan.png" alt="">
	                <div class="wenzi_color">待付款</div>
	            </div>
	            
	            <div id="dai_fahuo" class="area-one" onclick="viewShopOrder('fahuo');"   style="width:18%!important;">
	                <img src="./images/info_image/dai_fahuo.png" alt="">
	                <div class="wenzi_color">待发货</div>
	            </div>
	            
	            <div  id="dai_shouhuo"  class="area-one" onclick="viewShopOrder('shouhuo');"   style="width:18%!important;">
	                <img src="./images/info_image/dai_shouhuo.png" alt="">
	                <div class="wenzi_color">待收货</div>
	            </div>
	            
	            <div  id="dai_pingjia"  class="area-one" onclick="viewShopOrder('pingjia');"   style="width:18%!important;">
	                <img src="./images/info_image/dai_pingjia.png"alt="">
	                <div class="wenzi_color">待评价</div>
	            </div>
	            
	            <div  id="dai_pingjia" class="area-one" onclick="viewShopOrder('houzhong');"   style="width:18%!important;">
	                <img src="./images/info_image/xiao_houzhong.png" alt="">
	                <div class="wenzi_color">售后中</div>
	            </div>
	        </div>
       
    </div>
    <div class="white-list">
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;">
            <div class="left-title" style="width:10%;"><img src="./images/info_image/order.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:60%;"><span style="color:#494949;">订单</span></div>
            <div class="right-action"  onclick="viewOrder('other');">
            	<span>更多</span>
            	<img src="./images/vic/right_arrow.png" width="8" height="14" style="width: 8px;height: 14px;margin-right:15px;" alt="">
            </div>
        </div>
        <div class="line" style="margin-right: 10px;"></div>
            <div id="middle-tab" style="background-color: white;">
	            <div id="o_shangcheng" onclick="viewOrder('shangcheng');" class="area-one" style="width:19%!important;">
	                <img src="./images/info_image/o_shangcheng.png">
	                <div class="wenzi_color">商城</div>
	            </div>
	            
	            <div id="o_daodianfu" class="area-one" onclick="viewOrder('daodianfu');"   style="width:18%!important;">
	                <img src="./images/info_image/o_daodianfu.png">
	                <div class="wenzi_color">到店付</div>
	            </div>
	            
	            <div  id="o_waimai"  class="area-one" onclick="viewOrder('waimai');"  style="width:18%!important;">
	                <img src="./images/info_image/o_waimai.png">
	                <div class="wenzi_color">外卖</div>
	            </div>
	            
	            <div  id="o_meishi"  class="area-one" onclick="viewOrder('meishi');"  style="width:18%!important;">
	                <img src="./images/info_image/o_meishi.png">
	                <div class="wenzi_color">美食</div>
	            </div>
	            
	            <div  id="o_jiudian" class="area-one" onclick="viewOrder('jiudian');"  style="width:18%!important;">
	                <img src="./images/info_image/o_jiudian.png">
	                <div class="wenzi_color">酒店</div>
	            </div>
	        </div>
        
    </div>
    <div class="white-list">
            <div id="middle-tab" style="background-color: white;padding:5px;">
	            <div id="wode_qianbao" onclick="viewOthers('qianbao');" class="area-one gongneng" >
	                <img src="./images/info_image/wode_qianbao.png" >
	                <div class="wenzi_color">我的钱包</div>
	            </div>
	            
	            <div id="wode_tequan" class="area-one gongneng" onclick="viewOthers('tequan');"   >
	                <img src="./images/info_image/wode_quanxian.png" >
	                <div class="wenzi_color">我的特权</div>
	            </div>
	            
	            <div  id="wode_tuandui"  class="area-one gongneng" onclick="viewOthers('tuandui');" >
	                <img src="./images/info_image/wode_tuandui.png" >
	                <div class="wenzi_color">我的团队</div>
	            </div>
	            
	            <div id="wode_shouyi"  class="area-one gongneng" onclick="viewOthers('shouyi');">
	                <img src="./images/info_image/wode_shouyi.png" >
	                <div class="wenzi_color">累积收益</div>
	            </div>
	            
	            <div  id="wode_fahuodizhi" class="area-one gongneng" onclick="viewOthers('fahuodizhi');">
	                <img src="./images/info_image/wode_fahuodizhi.png"  style="width:35%!important;height:auto!important;" >
	                <div class="wenzi_color">发货地址</div>
	            </div>
	            
	             <div  id="wode_weidian" class="area-one gongneng" onclick="viewOthers('weidian');" >
	                <img src="./images/info_image/wode_weidian.png">
	                <div class="wenzi_color">我的微店</div>
	            </div>
	            
	             <div  id="wode_dianfu" class="area-one gongneng" onclick="viewOthers('dianfu');" >
	                <img src="./images/info_image/wode_dianfu.png">
	                <div class="wenzi_color">我的店铺</div>
	            </div>
	             <div  id="wode_qrcode" class="area-one gongneng" onclick="viewOthers('qrcode');" >
	                <img src="./images/info_image/wode_qrcode.png">
	                <div class="wenzi_color">二维码</div>
	            </div>
	        </div>
        
    </div>
    <div class="white-list" >
        <div class="list-one" style="padding-top:7px;padding-bottom:7px;">
            <div class="left-title" style="width:10%;"><img src="./images/info_image/gongneng.png" style="width: 18px;height: 18px;" alt=""></div>
            <div class="center-content"  style="width:60%;"><span style="color:#494949;">功能</span></div>
            <div class="right-action"  style="">
            	<span>更多</span>
            	<img src="./images/vic/right_arrow.png" class="right_arrow" alt="">
            </div>
        </div>
        	<div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;">
	            <div id="wode_libao" onclick="wode_libao();" class="area-one libao" >
	                <div class="left_content"><img src="./images/info_image/wode_libao.png"  style="width: 35px;height: 30px;"></div>
	                <div class="right_content">
	                	<span style="width:100%;float:left;">我的礼包</span>
	                	<div class="product-title">消费收益,齐头并进</div>
	                    
	                </div>
	            </div>
	            <div id="wode_fanxian" onclick="wode_fanxian();" class="area-one fanxian" style="">
	                <div class="left_content"><img src="./images/info_image/wode_fanxian.png"  style="width: 35px;height: 30px;"></div>
	                <div class="right_content" style="">
	                	<span style="width:100%;float:left;">我的返现</span>
	                	<div class="product-title">让优惠进，行到底</div>
	                </div>
	            </div>
	        </div>
	        <div class="line" style="margin-right: 10px;"></div>
	        <div id="middle-tab" style="background-color: white;">
	            <div id="wode_libao" onclick="wode_libao();" class="area-one libao">
	                <div class="left_content"><img src="./images/info_image/wode_libao.png"  style="width: 35px;height: 30px;"></div>
	                <div class="right_content">
	                	<span style="width:100%;float:left;">我的礼包</span>
	                	<div class="product-title">消费收益，齐头并进</div>
	                    
	                </div>
	            </div>
	            <div id="wode_fanxian" onclick="wode_fanxian();" class="area-one fanxian" >
	                <div class="left_content"><img src="./images/info_image/wode_fanxian.png" alt="" style="width: 35px;height: 30px;"></div>
	                <div class="right_content">
	                	<span style="width:100%;float:left;">我的返现</span>
	                	<div class="product-title">让优惠进，行到底</div>
	                </div>
	            </div>
	        </div>
        </div>
    </div>
    
</body>		

<script type="text/javascript">


   var winWidth = $(window).width();
   var winheight = $(window).height();
   var qrcode_width = winWidth*2/3;
   var tequan_type = 0; //0:股东，1：区域代理商，2：推广员
   function viewOthers(type){
   	 if(type=='fahuodizhi'){
   	 	window.location.href = "dizhiguanli.html";    //有收货地址的情况下
   	 	//window.location.href = "tianxiedizhi.html"; //空的情况下
   	 }else if(type=='dianfu')//跳转我的店铺
   	 {
   	 	window.location.href = "wodedianpu.html";
   	 
   	 }else if(type=='weidian')//跳转我的微店
   	 {
   	 	window.location.href = "wodeweidian.html";
   	 }else if(type=='qrcode')// 显示我的二维码
   	 {
   	 	showPersonQrCode();
        //showShopQrCode();
   	 }else if(type=='shouyi')
   	 {
   	 	window.location.href = "leijishouyi.html";
   	 }else if(type=='qianbao')
     {
        window.location.href = "wodeqianbao1-1.html";
     }else if(type=='tuandui')
     {
        window.location.href = "wodetuandui.html";
     }else if(type=='tequan')
     {
        if(tequan_type==0)  //股东
            window.location.href = "dailishang1-1.html";
        else if(tequan_type==1) //区域代理商
            window.location.href = "quyudailishang1-1.html";
        else if(tequan_type==1) //推广员
            window.location.href = "tuiguangyuan1-1tuiguangyuan.html";
     }
   }
   
	function viewMyInfo()
	{
        //showAlertMsg("提示：","您好，您的退货信息已经发送给商家。请耐心等待商家审核！","知道了");
        
		window.location.href="wode_jiben_1.html";
	}
	    
</script>

<script>


$(function(){	
	var show_hide = '<?php echo $show_hide?>';
	if(show_hide==1){
		window.location.hash= '#type_<?php echo $sys_num?>';
	}else{
		$('#type_<?php echo $sys_num?>').hide();
	}
	
	
});




</script>


<div id='loading' class='loadingPop'style="display: none;"><img src='./images/loading.gif' style="width:40px;"/><p class=""></p></div>

</html>