<?php

header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]1
require('../../../back_init.php');

$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../../../common/utility_shop.php');

require('../../../proxy_info.php');

mysql_query("SET NAMES UTF8");
require('../../../auth_user.php');

$shopMessage_Utlity = new shopMessage_Utlity;
$begintime="";
$endtime ="";
$op="";
if(!empty($_GET["op"])){
   $op = $configutil->splash_new($_GET["op"]);
   $id = $configutil->splash_new($_GET["id"]);
   $user_id = $configutil->splash_new($_GET["user_id"]);
   if($op=="resetpwd"){
	   $sql="update promoters set pwd='888888' where user_id=".$user_id;
	   mysql_query($sql);
   }else if($op=="p_tupian"){
		$sql='update promoters set foreverimg="",exp_map_url="",imgcreatime=NULL  where  user_id='.$user_id.' and isvalid=true and customer_id='.$customer_id;
		mysql_query($sql);
		
		$sql_del = "select id from weixin_qr_infos where isvalid=true and foreign_id=".$user_id." and customer_id=".$customer_id;
		$result = mysql_query($sql_del) or die('Query failed: ' . mysql_error());
		$del_qr_info_id = -1;
		while ($row = mysql_fetch_object($result)) {		
			$del_qr_info_id = $row -> id;
		}
		$sql='update weixin_qrs set imgurl_qr="" where  qr_info_id='.$del_qr_info_id.' and isvalid=true and customer_id='.$customer_id;
		mysql_query($sql);
   }else if($op=="p_tupian2"){
		$sql='update promoters set foreverimg="",exp_map_url="",imgcreatime=NULL  where  isvalid=true and customer_id='.$customer_id;
		mysql_query($sql);
		
		$sql='update weixin_qrs set imgurl_qr="" where isvalid=true and customer_id='.$customer_id;
		mysql_query($sql);
   }
}

$query ="select isOpenPublicWelfare,is_team,is_shareholder,is_ncomission,exp_name,shop_card_id,qrsell_orderothers from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$isOpenPublicWelfare =0;
$is_team             =0;
$is_shareholder      =0;
$exp_name="分销商";
$shop_card_id        =-1;
$qrsell_orderothers  ="";
$is_ncomission       =0;//是否开启3*3分佣 1:开启 0:关闭
while ($row = mysql_fetch_object($result)) {		
   $isOpenPublicWelfare     = $row->isOpenPublicWelfare;
   $is_team                 = $row->is_team;
   $is_shareholder          = $row->is_shareholder;
   $is_ncomission           = $row->is_ncomission;
   $shop_card_id            = $row->shop_card_id;
   $exp_name                = $row->exp_name;	
   $open_qrsell_orderothers = $row->qrsell_orderothers;	
}

$exp_user_id=-1;

if(!empty($_GET["exp_user_id"])){
    $exp_user_id = $configutil->splash_new($_GET["exp_user_id"]);
}

$search_generation=-1;
if(!empty($_GET["search_generation"])){
    $search_generation = $configutil->splash_new($_GET["search_generation"]);
}
if(!empty($_POST["search_generation"])){
    $search_generation = $configutil->splash_new($_POST["search_generation"]);
}

$search_name="";
if(!empty($_GET["search_name"])){
    $search_name = $configutil->splash_new($_GET["search_name"]);
}
if(!empty($_POST["search_name"])){
    $search_name = $configutil->splash_new($_POST["search_name"]);
}

$search_user_id="";
if(!empty($_GET["search_user_id"])){
    $search_user_id = $configutil->splash_new($_GET["search_user_id"]);
}
if(!empty($_POST["search_user_id"])){
    $search_user_id = $configutil->splash_new($_POST["search_user_id"]);
}
if(!empty($_GET["begintime"])){ 
   $begintime = $configutil->splash_new($_GET["begintime"]);
}
if(!empty($_GET["endtime"])){  
   $endtime = $configutil->splash_new($_GET["endtime"]);
}
$search_phone="";
if(!empty($_GET["search_phone"])){
    $search_phone = $configutil->splash_new($_GET["search_phone"]);
}
if(!empty($_POST["search_phone"])){
    $search_phone = $configutil->splash_new($_POST["search_phone"]);
}



//新增客户
$new_customer_count =0;
//今日销售
$today_totalprice=0;
//新增订单
$new_order_count =0;
//新增推广员
$new_qr_count =0;

$nowtime = time();
$year = date('Y',$nowtime);
$month = date('m',$nowtime);
$day = date('d',$nowtime);

$query="select count(distinct batchcode) as new_order_count from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_order_count = $row->new_order_count;
   break;
}

$query="select sum(totalprice) as today_totalprice from weixin_commonshop_orders where paystatus=1 and sendstatus!=4 and isvalid=true and customer_id=".$customer_id." and year(paytime)=".$year." and month(paytime)=".$month." and day(paytime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $today_totalprice = $row->today_totalprice;
   break;
}
$today_totalprice = round($today_totalprice,2);

$query="select count(1) as new_customer_count from weixin_commonshop_customers where isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_customer_count = $row->new_customer_count;
   break;
}

$query="select count(1) as new_qr_count from promoters where status=1 and isvalid=true and customer_id=".$customer_id." and year(createtime)=".$year." and month(createtime)=".$month." and day(createtime)=".$day;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
 //  echo $query;
while ($row = mysql_fetch_object($result)) {
   $new_qr_count = $row->new_qr_count;
   break;
}

 
//代理模式,分销商城的功能项是 266
$is_distribution=0;//渠道取消代理商功能
$is_disrcount=0;
$query1="select count(1) as is_disrcount from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.sys_name='商城代理模式' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('W_is_disrcount Query failed: ' . mysql_error());  
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
$sp_result = mysql_query($sp_query) or die('W_is_supplier Query failed: ' . mysql_error());  
while ($row = mysql_fetch_object($sp_result)) {
   $sp_count = $row->sp_count;
   break;
}
if($sp_count>0){
   $is_supplierstr=1;
}
$pagenum = 1;

if(!empty($_GET["pagenum"])){
	$pagenum = $_GET["pagenum"];
}
$pagecount = 20;
if(!empty($_GET["pagecount"])){
	$pagecount = intval($_GET["pagecount"]);
}
$start = ($pagenum-1) * $pagecount;
$end = $pagecount;		

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>分销商管理</title>
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content.css">
<link rel="stylesheet" type="text/css" href="../../../common/css_V6.0/content<?php echo $theme; ?>.css">
<link rel="stylesheet" type="text/css" href="../../Common/css/Product/product.css"><!--内容CSS配色·蓝色-->
<link rel="stylesheet" type="text/css" href="../../Common/css/Users/promoter/promoter.css">
<script type="text/javascript" src="../../../common/js/jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="../../../common/js_V6.0/jscolor.js"></script><!--拾色器js-->
<script type="text/javascript" src="../../../js/WdatePicker.js"></script> 

<script type="text/javascript" src="../../Distribution/express/js/LodopFuncs.js"></script>
<script type="text/javascript" src="../../Distribution/express/js/print_delivery.js"></script>
<link rel="stylesheet" href="percent/jquery.percentageloader.0.2.css">
<script src="percent/jquery.percentageloader.0.2.js"></script>

<style>
.aright {    
    margin-right:5px!important;;
}
.left{
	
    margin-top: 10px;
    padding-left: 20px;
    font-size: 14px;
    color: #2eade8;
    background-image: url(../../../common/images_V6.0/table_icon/icon01.png);
    background-repeat: no-repeat;
    background-position: left 0%;
    margin-left: 20px;
}
#caozuo a img{
	width: 18px;
    height: 18px;
	vertical-align: baseline;	
}
#caozuo{
	height: 80px;
	padding-top: 20px !important;
    padding-bottom: 20px !important;
	
}
.WSY_table a{
	color:#06a7e1;
	
}	
.time {
	background-color: #fefefe !important;
    width: 140px!important;
    color: black!important;
}
.time input{
	border:solid 1px #ccc !important;
}
.WSY_searchdiv{
	display: inline-block;
    overflow: hidden;
    line-height: 30px;
}
.WSY_searchdiv .aright{float:left !important;margin-top:10px;}
#topLoader {
	width: 256px;
	height: 256px;
	margin-bottom: 32px;
	position:absolute;width:400px; left:50%; top:50%; margin-left:-200px; height:auto; z-index:100; padding:1px;
}
#per_container {
	width: 500px;
	padding: 10px;
	margin-left: auto;
	margin-right: auto;
}
#BgDiv{background-color:#e3e3e3; position:absolute; z-index:99; left:0; top:0; display:none; width:100%; height:1000px;opacity:0.5;filter: alpha(opacity=50);-moz-opacity: 0.5;}

#DialogDiv{position:absolute;width:400px; left:50%; top:50%; margin-left:-200px; height:auto; z-index:100;background-color:#fff; border:1px #8FA4F5 solid; padding:1px;}
</style>
</head>

<body>
<div id="BgDiv"></div>
<div id="per_container">
<div style="display:none" id="topLoader"></div>
</div>
<!--内容框架开始-->
<div class="WSY_content" id="WSY_content_height">

       <!--列表内容大框开始-->
	<div class="WSY_columnbox">	
	<!--头部导航start-->
	<?php require('head.php');?>	
	<!--头部导航end-->
	
    <!--产品管理代码开始-->
    <div class="WSY_data">
    	<div class="WSY_agentsbox">
			<form class="search" id="search_form">		
				<div class="WSY_search_q">					
					<li class="left"><a>分销商管理</a></li>
					<li style="margin: 0 40px 0 0;float:right;"><a href="javascript:history.go(-1);" class="WSY_button" style="margin-top: 0;width: 60px;height: 28px;vertical-align: middle;line-height: 28px;">返回</a></li>

				</div>
				<div class="WSY_search_q search" id="search_form">
					<div class="WSY_searchdiv" >
						<li>分销商代数：
							<select name="search_generation" id="search_generation"  style="width:100px;" >
								<option value="-1">--请选择--</option>	
								<?php

									$query = "SELECT max(generation) as max_generation FROM weixin_users WHERE isvalid=true and customer_id=".$customer_id;		
									$result = mysql_query($query) or die('Query failed: ' . mysql_error());
									while ($row = mysql_fetch_object($result)) {
										$max_generation = $row->max_generation;
									}
									for ($i = 1; $i <= $max_generation; $i++){
								?>
								<option value="<?php echo $i; ?>" <?php if($search_generation==$i){ ?>selected <?php } ?>><?php echo $i;?>代分销商</option>
								<?php
									}
								?>
							</select>
						</li>
						<li>分销商编号： <input type=text name="search_user_id" id="search_user_id" value="<?php echo $search_user_id; ?>" style="width:80px;" /></li>	
						<li>姓名： <input type=text name="search_name" id="search_name" value="<?php echo $search_name; ?>" style="width:80px;" /></li>	
						<li>电话： <input type=text name="search_phone" id="search_phone" value="<?php echo $search_phone; ?>"  style="width:80px;" /></li>
						
						<li class="WSY_bottonliss aright ">时间：					
						<span class="time">
							<input class="date_picker time" type="text" name="AccTime_S" id="begintime" value="<?php echo $begintime; ?>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});">
							<span class="om-calendar-trigger"></span>
						</span>
						-
						<span class="time" >
							<input class="date_picker time" type="text" name="AccTime_E" id="endtime" value="<?php echo $endtime; ?>" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm'});">
							
						</span>&nbsp;  
						</li>
						<li>每页记录数： <input type=text name="pagecount" id="pagecount" value="<?php echo $pagecount; ?>"  style="width:80px;" /></li>
						<li class="WSY_bottonliss"><input type="button" class="search_btn"  onclick="searchForm();" value="搜索"></li>
					</div>
					<div class="WSY_searchdiv">
						
						<li class="WSY_bottonliss aright" ><input type="button" style="width:100px" class="search_btn" value="导出分销商+" onClick="exportRecord();"></li>
						<li class="WSY_bottonliss aright" ><input type="button" style="width:100px" class="search_btn" value="导出金额详情+" onClick="exportRecord1();"></li>
						<li class="WSY_bottonliss aright" ><input type="button" style="width:100px" class="search_btn" value="一键删除推广图片" onClick="del_tupian();"></li>
						<li class="WSY_bottonliss aright" ><input type="button" style="width:180px" class="search_btn" value="导出全部分销商(三级推广粉丝)+" onClick="exportRecord_All();"></li>
					</div>
				</div>
				
			</form> 

            <table width="97%" class="WSY_table" id="WSY_t1">
			  <thead class="WSY_table_header">
					
					<th width="9%" nowrap="nowrap">编号</th>
					<th width="9%" nowrap="nowrap">姓名</th>
					<th width="7%" nowrap="nowrap">角色</th>
					<th width="5%" nowrap="nowrap">所在省</th>
					<th width="5%" nowrap="nowrap">所在市</th>
					<th width="13%" nowrap="nowrap">推广</th>
					<th width="13%" nowrap="nowrap">钱包</th>
					<th width="8%" nowrap="nowrap">直接推广金额</th>
					<!--<th width="8%" nowrap="nowrap">总获奖积分</th>	暂时屏蔽-->
					<th width="8%" nowrap="nowrap">总获奖金额</th>
					<th width="8%" nowrap="nowrap">状态</th>
					<th width="7%" nowrap="nowrap">推荐人</th>
					<th width="8%" nowrap="nowrap">总消费金额</th>
					<th width="10%" nowrap="nowrap">申请时间</th>
					<?php
					if(!empty($open_qrsell_orderothers)){
					?>
					<th width="12%" nowrap="nowrap">自定义</th>
					<?php } ?>
					<th width="10%" nowrap="nowrap" class="last">操作</th>
			  </thead>
			  
			    <?php 
			   
			   
				$weixin_fromuser="";
			   
			   $query="";
			   $query_count=0;
	
				$query=" select distinct(ps.user_id), wu.id as user_id,wu.name as name,wu.weixin_name as weixin_name,wu.phone as phone,wu.parent_id as parent_id ,ps.fans_count,ps.promoter_count,weixin_fromuser ,ps.status
				from weixin_users wu 
				inner join promoters  ps	
				on ps.user_id=wu.id 
				where wu.isvalid=true and ps.isvalid=true and wu.customer_id=".$customer_id." ";
				 
				$query_count=" select count(1) as tcount
				from weixin_users wu 
				inner join promoters  ps	
				on ps.user_id=wu.id 
				where wu.isvalid=true and ps.isvalid=true and wu.customer_id=".$customer_id."";
				 
				 $query3="";
				 if($exp_user_id>0){
				     $query3 = $query3." and wu.id=".$exp_user_id;
				 }
				 switch($search_status){		//查promoters status:0待审核，1通过，-1驳回
				    case 1:
					
						break;
				    case 2:
					   $query3 = $query3." and ps.status=0";
					   break;
					case 3:
					   $query3 = $query3." and ps.status=1";
					   break;
					
					case -1:
					   $query3 = $query3." and ps.status=-1";
					   break;
					
				     
				 }
				 switch($search_generation){
					case -1:
						break;
					default:
						$query3 = $query3." and wu.generation=".$search_generation;
				 }
				 
				 if(!empty($search_name)){
				   
					$query3 = $query3." and (wu.name like '%".$search_name."%' or wu.weixin_name like '%".$search_name."%')";
				 }
				 
				 if(!empty($search_phone)){
				   
					$query3 = $query3." and wu.phone like '%".$search_phone."'";
				 }
				 
				 if(!empty($search_user_id)){
				   
					$query3 = $query3." and wu.id like '%".$search_user_id."%'";
				 }
				 if(!empty($begintime)){
				   
					$query3 = $query3." and UNIX_TIMESTAMP(ps.createtime)>=".strtotime($begintime);
				 }
				 if(!empty($endtime)){
				   
					$query3 = $query3." and UNIX_TIMESTAMP(ps.createtime)<=".strtotime($endtime);
				 }	
				  $query = $query.$query3;
				 $query_count = $query_count.$query3;
				 /* 输出数量开始 */
				 $wcount = 0;
				 $result2 = mysql_query($query_count) or die('Query failed28: ' . mysql_error());
				 while ($row2 = mysql_fetch_object($result2)) {
					$wcount=$row2->tcount;
				 }
				 $page=ceil($wcount/$end);
				 
				 /* 输出数量结束 */
				 $query = $query." order by ps.id desc"." limit ".$start.",".$end;
				 $result = mysql_query($query) or die('Query failed: ' . mysql_error());
				
				//echo $query;
	             while ($row = mysql_fetch_object($result)) {
				 
				    
				    
					$weixin_fromuser = "";
					//$qr_info_id = $row->qr_info_id;
					$user_id =$row->user_id;
					$status = $row->status; 
					$username       = "";
					$weixin_name    = "";
					$userphone      = "";
					$user_parent_id = -1;
					$query2="select weixin_fromuser,name,weixin_name,phone,weixin_headimgurl,parent_id,generation,province,city  from weixin_users where isvalid=true and id=".$user_id;
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
	                while ($row2 = mysql_fetch_object($result2)) {
					    $weixin_fromuser        = $row2->weixin_fromuser;
						$username               = $row2->name;
						$weixin_name            = $row2->weixin_name;
						$username               = $username."(".$weixin_name.")";
						$userphone              = $row2->phone;
						$user_parent_id         = $row2->parent_id;
						$user_weixin_headimgurl = $row2->weixin_headimgurl;
						$generation             = $row2->generation;
						$province               = $row2->province;
						$city                   = $row2->city;
					}	
					if(empty($user_weixin_headimgurl)){
						$user_weixin_headimgurl = "http://".$_SERVER['SERVER_NAME']."/weixinpl/common/images_V6.0/contenticon/no-pic.jpg";
					}
					
					/* 查询个人总VP值 start */
					$my_vpscore     =  0; //个人vp值
					$query_vp = "SELECT my_vpscore from weixin_user_vp where isvalid=true and customer_id=" . $customer_id . " and user_id=" . $user_id . " limit 0,1";
					$result_vp = mysql_query($query_vp) or die('W447 Query failed: ' . mysql_error());
					while ($row_vp = mysql_fetch_object($result_vp)) {
						$my_vpscore  	 = $row_vp->my_vpscore;
					}
					/* 查询个人总VP值 end */
					
					//查weixin_qr_infos和weixin_qrs
					$query5="select id from weixin_qr_infos where isvalid=true and foreign_id=".$user_id." order by id desc limit 0,1";
					$result5=mysql_query($query5)or die('Query failed55'.mysql_error());
					while($row5=mysql_fetch_object($result5)){
						$qr_info_id = $row5->id;
						
					 	$query6="select id,reason,imgurl_qr,reward_score from weixin_qrs where isvalid=true and qr_info_id=".$qr_info_id."";
						$result6=mysql_query($query6)or die('Query failed555'.mysql_error());
						while($row6=mysql_fetch_object($result6)){
							
							$id = $row6->id;
							$reward_score = $row6->reward_score;
							$reason = $row6->reason;
							$imgurl_qr=$row6->imgurl_qr;
						}  
					}
					
					if(empty($imgurl_qr)){
					$imgurl_qr = "http://".$_SERVER['SERVER_NAME']."/weixinpl/common/images_V6.0/contenticon/no-pic.jpg";
					}
					$fans_count = 0;
					$promoter_count = 0;
					
					$team_fans = 0 ;
					$team_prom = 0;
					$commision_level = 0;
					$query2="select fans_count,promoter_count,parent_id,team_fans,team_prom,commision_level from promoters where user_id=".$user_id." and isvalid=true";
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					while ($row2 = mysql_fetch_object($result2)) {
					//一级推广员数跟粉丝数	
					   //$fans_count = $row2->fans_count;
					   //$promoter_count = $row2->promoter_count; 
					   $parent_id = $row2->parent_id;	
					 //总的推广员数跟粉丝数
						$team_fans = $row2->team_fans;
					    $team_prom = $row2->team_prom;	
					    $commision_level = $row2->commision_level;	
					   break;
					}
					//开启3*3分佣
					if(1 == $is_ncomission){
						//3*3等级推广员自定义名称
						$query_commisions="select exp_name from weixin_commonshop_commisions where isvalid=true and customer_id=".$customer_id." and level=".$commision_level." limit 0,1";
						$result_commisions = mysql_query($query_commisions) or die('w94 Query failed: ' . mysql_error());
						$exp_name = ""; //3*3等级推广员自定义名称 
						while ($row = mysql_fetch_object($result_commisions)) {	
							$exp_name = $row->exp_name;
							break;
						}
					}
					
					
					$sum_totalprice=0;
					//直接推广金额
					$query2="select sum(totalprice) as sum_totalprice from weixin_commonshop_orders where isvalid=true and status =1 and paystatus=1 and aftersale_state!=4 and sendstatus!=4 and exp_user_id>0 and exp_user_id=".$user_id;
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					//echo $query2;
					while ($row2 = mysql_fetch_object($result2)) {
					   $sum_totalprice = $row2->sum_totalprice;
					   break;
					}
					if(empty($sum_totalprice)){
					   $sum_totalprice = 0;
					}
					
				    $sum_totalprice = round($sum_totalprice, 2);
						
					//$status = $row->status; echo '$status'.$status;
					$statusstr="待审核";
					switch($status){
					   case 1:
					   
					     $statusstr="已确认";
						 break;
					   case -1:
					     $statusstr="已驳回/暂停";
						 break;
					}
					
					
					$parent_name = "";
					
					if($parent_id<0){
						$parent_id = $user_parent_id;
					}

					$query2="select createtime,isAgent,is_consume,generation,qrsell_orderothers from promoters where isvalid=true and user_id=".$user_id;
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					$isAgent = 0;
					$is_consume = 0;
					
					$qrsell_orderothers = "";
					while ($row2 = mysql_fetch_object($result2)) {
					    $createtime = $row2->createtime;
						$isAgent    = $row2->isAgent;	//判断 0为推广员 1为代理商 2为顶级推广员
						$is_consume = $row2->is_consume;	//判断 0:不是无限级奖励 1:无限级奖励
						
						$qrsell_orderothers = $row2->qrsell_orderothers;	//推广员申请自定义自动
						break;
					}
					$generation=$generation."代";//推广员代数
					
					//查询区域
					$query2="select all_areaname from weixin_commonshop_team_area where isvalid=true and area_user=".$user_id." and customer_id=".$customer_id;//团队奖励 此人分配的区域
					$all_areanames = ""; 
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					$all_areaname = "";//团队奖励 分配区域的全称
					while ($row2 = mysql_fetch_object($result2)) {
					    $all_areaname = $row2->all_areaname;
						$all_areanames .= " ".$all_areaname;
					}
					

					$is_showcustomer    =  1; //是否开启区域代理自定义
					$p_customer         =  '(省代)';
					$c_customer         =  '(市代)';
					$a_customer         =  '(区代)';		
					$diy_customer       =  '(自定义级别)';
					$QUERY_BASE = "SELECT p_customer,c_customer,a_customer,diy_customer from weixin_commonshop_team WHERE isvalid = true AND customer_id = ".$customer_id." limit 0,1";
					$RESULT_BASE = mysql_query($QUERY_BASE) or die (" Wrong_1 : QUERY ERROR : ".mysql_error());
					if($row = mysql_fetch_object($RESULT_BASE)){
						$diy_customer      = $row->diy_customer;	        //自定义级别自定义名称
						$is_showcustomer   = $row->is_showcustomer;			//是否开启区域代理自定义
						if($is_showcustomer){
							$p_customer        = $row->p_customer;				//省代自定义名称
							$c_customer        = $row->c_customer;				//市代自定义名称
							$a_customer        = $row->a_customer;				//区代自定义名称
						}
					}
					
					//查询区域 End
					
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
					$agentname="";
					switch($isAgent){
						case 1:
							$agentname = "(代理商)";
							break;
						case 2:
							$agentname = "(顶级".$exp_name.")";
							break;
						case 3:
							$agentname = "(供应商)";
							break;
						case 5:
							$agentname = $a_customer;
							break;
						case 6:
							$agentname = $c_customer;
							break;
						case 7:
							$agentname = $p_customer;
							break;
						case 8:
							$agentname = $diy_customer;
							break;							
						
					}
						
					$parent_weixin_fromuser="";
					if($parent_id>0 and $parent_id!=$user_id){
					   
						$query2="select id from promoters where  status=1 and isvalid=true and user_id=".$parent_id;
						$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
						$promoter_id = -1;
						while ($row2 = mysql_fetch_object($result2)) {    
						    $promoter_id = $row2->id;
							break;
						}
                       						
						if($promoter_id>0){
							$query2= "select name,phone,parent_id,weixin_name,weixin_fromuser from weixin_users where isvalid=true and id=".$parent_id; 
							$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
							while ($row2 = mysql_fetch_object($result2)) {
								$parent_name=$row2->name;
								$weixin_name = $row2->weixin_name;
								$parent_weixin_fromuser = $row2->weixin_fromuser;
								$parent_name = $parent_name."(".$weixin_name.")";
								break;
							}
						}
					}
					//查找账户和支付宝
					
					$query2="select account,account_type,bank_open,bank_name from weixin_card_members where isvalid=true and user_id=".$user_id;
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					$account = "";
					$account_type="";
					$bank_open="";
					$bank_name="";
					while ($row2 = mysql_fetch_object($result2)) {
					    $account= $row2->account;
						$account_type =$row2->account_type;
						$bank_open = $row2->bank_open;
						$bank_name = $row2->bank_name;
					}
					$account_type_str="";
					switch($account_type){
					    case 1:
						   $account_type_str="支付宝";
						   break;
					    case 2:
						   $account_type_str="财付通";
						   break;
						case 3:
						   $account_type_str="银行账户";
						   break;
					}
			
					//查找推广员的会员卡号
					$Membership_Card=-1;
					$query_m="SELECT id from weixin_card_members where isvalid=true and card_id=".$shop_card_id." and user_id=".$user_id;
					$result_m = mysql_query($query_m) or die('Query failed: ' . mysql_error());
					while ($row_m = mysql_fetch_object($result_m)) {
					   $Membership_Card = $row_m->id;
					   break;
					}					
			
					//显示该推广员已经购买的商品总金额(已经付款的,没退货,没退款)
					/* $query2="select sum(totalprice) as total_money from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and user_id =".$user_id." and paystatus=1  and sendstatus<3 and return_status in(0,3,9)";
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					$s_totalprice=0;
					while ($row2 = mysql_fetch_object($result2)) {
					    $s_totalprice = $row2->total_money;
					} */
					$reward_money = 0;
					$query_t = "SELECT SUM(reward) as reward_money FROM weixin_commonshop_order_promoters WHERE paytype=1 AND user_id=$user_id";
					$result_t= mysql_query($query_t) or die('Query failed 744: ' . mysql_error());
					while( $row = mysql_fetch_object($result_t) ){
						$reward_money = round($row->reward_money,2);
					}

					$s_totalprice=0;
					$query2 = "SELECT total_money FROM my_total_money WHERE isvalid=true AND user_id=$user_id LIMIT 1";
					$result2 = mysql_query($query2) or die('Query failed23: ' . mysql_error());
					while( $row2 = mysql_fetch_object($result2) ){
						$s_totalprice = $row2->total_money;
					}
					
					if($s_totalprice==''){
						$s_totalprice = 0;
					}else{
						$s_totalprice = sprintf("%.3f", $s_totalprice); 
					}
					
					//城市商圈金融订单总金额
					$query_finance="select sum(totalprice) as f_totalprice from weixin_finance_orders where isvalid=true and paystatus=1 and  user_id=".$user_id." and return_status not in(1,2)";
					$result_finance = mysql_query($query_finance) or die('Query failed: ' . mysql_error());
					$f_totalprice=0;;
					while ($row_finance = mysql_fetch_object($result_finance)) {
					    $f_totalprice = $row_finance->f_totalprice;
					}
					
					$s_totalprice = $s_totalprice + $f_totalprice;
					$s_totalprice = round($s_totalprice,2);
					
					$query2="select title,online_qq from weixin_commonshop_owners where isvalid=true and user_id=".$user_id;
					$mystore_title="";
					$mystore_qq="";
					$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());
					
					while ($row2 = mysql_fetch_object($result2)) {
					    $mystore_title=$row2->title;
						$mystore_qq = $row2->online_qq;
						break;
					}
					
					$tmp = $id.'_'.$user_id.'_'.$parent_id.'_'.$isAgent.'_'.$customer_id_en.'_'.$pagenum.'_'.$qr_info_id.'_'.$is_consume.'_'.$is_shareholder ;  // 数据是用于操作 type 1:成为顶级推广员,2:推广员通过 3:驳回推广员 4:删除推广员 5:取消上下级关系
					/*** 钱包零钱 start***/
					$balance = 0;
					$query_moneybag = "SELECT balance FROM moneybag_t WHERE isvalid=true AND user_id=".$user_id." AND customer_id=".$customer_id." LIMIT 1";
					$result_moneybag= mysql_query($query_moneybag) or die('query_moneybag failed 37: ' . mysql_error());
					while( $row_moneybag = mysql_fetch_object($result_moneybag) ){
						$balance = $row_moneybag->balance;
					}
					$balance = substr(sprintf("%.3f", $balance),0,-1); 
					/*** 钱包零钱 end***/
					/*** 购物币 start***/
					$currency 		= 0;
					$currencyCustom = "";
					$isOpenCurrency = 0;
					$shopMessage_Utlity->CheckCurrency($user_id,$customer_id);
					$query_currency = "SELECT u.currency,c.custom,c.isOpen FROM weixin_commonshop_user_currency u right JOIN weixin_commonshop_currency c ON u.customer_id=c.customer_id WHERE c.isvalid=TRUE AND u.user_id=".$user_id." AND c.customer_id=".$customer_id."  LIMIT 1";
					$result_currency = mysql_query($query_currency) or die('query_currency failed 28: ' . mysql_error());
					while($row_currency = mysql_fetch_object($result_currency)){
						$currency 		= $row_currency->currency;
						$currencyCustom	= $row_currency->custom;
						$isOpenCurrency = $row_currency->isOpen;
					}
					$currency = substr(sprintf("%.3f", $currency),0,-1); 
					/*** 购物币 end***/
			   ?>
			  <tr>	
				   <td  style="padding-top: 10px;padding-bottom: 5px;">
						<span style="display:block"><img src="<?php echo $user_weixin_headimgurl?>" style="width:50px;height:50px;"></span>
						<span style="display:block;margin-top: 5px;">ID:<?php echo $user_id;?></span>
						
					</td>
				   <td style="padding:2px;">
				   
				   <!-- <a title="会员卡号:<?php echo $Membership_Card; ?>" href="../../../card_member.php?card_id=<?php echo $shop_card_id; ?>&card_member_id=<?php echo $Membership_Card; ?>&customer_id=<?php echo passport_encrypt((string)$customer_id);?>"><?php echo $username; ?></a> -->
				  <?php echo $username; ?></a> 
				   <?php echo $userphone; ?><br/>
				   <?php if(!empty($weixin_fromuser)){
							 ?>  
							   <a  class="btn"  href="../../../weixin_inter/send_to_msg.php?fromuserid=<?php echo $weixin_fromuser; ?>&customer_id=<?php echo passport_encrypt($customer_id)?>"  title="对话"><i  class="icon-comment"></i></a>
							<?php   
						   }  ?>
				   
				   <br/>
				       
			<!-- 		   收款类型:<?php echo $account_type_str; ?><br/>
					   收款账户:<?php echo $account; ?>
					   <?php if($account_type==3){ ?>
					   <br/>开户银行：<?php echo $bank_open; ?>
					   <br/>开户姓名：<?php echo $bank_name; ?>
					   <?php } ?>
					   <?php if(!empty($mystore_title)){ ?>
					     <br/>微店名称:<?php echo $mystore_title; ?><br/>
						 在线QQ:<?php echo $mystore_qq; ?> 
					   <?php } ?> -->
				   </td>
				 <!--二维码-->
				  <!--  <td ><a href="<?php echo $imgurl_qr; ?>" target="_blank"><img src="<?php echo $imgurl_qr; ?>" style="width:70px;height:70px;" /></a></td> -->
				 <!--二维码--> 
				 <td >
						<span style="display:block"><?php if($status==1){echo $generation;}?></span>
						<span style="display:block"><?php if($status==1){echo $exp_name;}else{echo "粉丝";}?></span>
						<span style="display:block" title="<?php echo $all_areanames;?>"><?php echo $agentname;?></span>
						<span style="display:block"><?php echo $consume_name;?></span>
				 </td>
				 <td><?php echo $province ;?></td>
				 <td><?php echo $city ;?></td>
				 <td  style="text-align: left;padding-left: 30px;">
					<dt>粉丝数:&nbsp;<a <a class="fans_<?php echo $user_id; ?>" href="qrsell_detail_member.php?customer_id=<?php echo $customer_id_en; ?>&scene_id=<?php echo $user_id; ?>&rcount=<?php echo $team_fans; ?>"><i class="wx_loading_icon"></i></a> </dt>
					<dt> 分销商数:&nbsp;<a class="prom_<?php echo $user_id; ?>" href="qrsell_detail.php?customer_id=<?php echo $customer_id_en; ?>&scene_id=<?php echo $user_id; ?>&rcount=<?php echo $team_prom; ?>"><i class="wx_loading_icon"></i></a></dt>
					<script>
					$(document).ready(function(){
						$.ajax({
							type: "post",
							url: "get_fans.php",
							dataType:"json",
							data: { user_id: <?php echo $user_id; ?>,customer_id: <?php echo $customer_id; ?>},
							success: function (result) {
								var prom = result.p_tcount;
								var fans = result.f_tcount;
								$(".fans_<?php echo $user_id; ?>").html(fans);
								$(".prom_<?php echo $user_id; ?>").html(prom);
							}    
						})
					});
					</script>
				   </td>
				   <td >
					   <dt><span>vp：</span><a href="vp_detail.php?customer_id=<?php echo $customer_id_en ;?>&user_id=<?php echo passport_encrypt($user_id) ;?>&pagenum=<?php echo $pagenum;?>"><?php echo $my_vpscore ;?></a></dt>
					   <dt><span>零钱：</span><a href="../../Base/moneybag/user_detail.php?customer_id=<?php echo $customer_id_en;?>&user_id=<?php echo $user_id;?>">￥<?php echo $balance;?></a></dt>
					   <?php if($isOpenCurrency){?>

					   <dt><span><?php echo $currencyCustom;?>：</span><a href="../../Base/pay_set/pay_currency_log.php?customer_id=<?php echo $customer_id_en;?>&promoter=<?php echo $user_id;?>"><?php echo $currency;?></a></dt>

					   <?php }?>
				   </td>
 				   <td ><a href="qrsell_money.php?customer_id=<?php echo $customer_id_en; ?>&scene_id=<?php echo $user_id; ?>&sum_totalprice=<?php echo $sum_totalprice; ?>"><?php echo $sum_totalprice; ?>元</a></td>
				   <!--<td >

				     <a href="qrsell_rewardmoney.php?customer_id=<?php echo $customer_id_en; ?>&scene_id=<?php echo $user_id; ?>&type=1&sum_totalscore=<?php echo $reward_score; ?>"><?php echo $reward_score; ?></a>
				   </td> 暂时屏蔽-->
				   <td >

				     <a href="qrsell_rewardmoney.php?customer_id=<?php echo $customer_id_en; ?>&scene_id=<?php echo $user_id; ?>&type=2&sum_totalprice=<?php echo $reward_money; ?>"><?php echo $reward_money; ?>	元</a>
				   </td>
				   <td >
				     <?php echo $statusstr; ?><br/>
					 <?php if(!empty($reason)){ ?>
					 (<span style="font-size:12px;"><?php echo $reason; ?></span>)
					 <?php } ?>
				   </td>
				   <td >

				     <a href="promoter.php?exp_user_id=<?php echo $parent_id; ?>&customer_id=<?php echo $customer_id_en; ?>"><?php echo $parent_name; ?></a>
					  <?php if(!empty($parent_weixin_fromuser)){
							 ?>  
							   <a class="btn"  href="../../../weixin_inter/send_to_msg.php?fromuserid=<?php echo $parent_weixin_fromuser; ?>&customer_id=<?php echo passport_encrypt($customer_id)?>"  title="对话"><i class="icon-comment"></i></a>
							<?php   
						   }  ?>
				   </td >
				   <td ><a href="customers.php?search_user_id=<?php echo $user_id; ?>"><?php echo $s_totalprice; ?></a></td>
				   <td><?php echo $createtime; ?></td>
				   <?php
					$qrsell_orderothers=str_replace(",","</br>",$qrsell_orderothers);
				   if(!empty($open_qrsell_orderothers)){?>
				   <td style="text-align: left;word-break:break-all;"><?php echo $qrsell_orderothers; ?></td>
				   <?php } ?>
				   <td id="caozuo">
				      

						<a href="add_qrsell_account.php?customer_id=<?php echo $customer_id_en; ?>&isAgent=<?php echo $isAgent; ?>&user_id=<?php echo $user_id; ?>&parent_id=<?php echo $parent_id; ?>&status=<?php echo $status; ?>&pagenum=<?php echo $pagenum; ?>"><img src="../../../common/images_V6.0/operating_icon/icon05.png" align="absmiddle" alt="编辑分销商" title="编辑分销商"></a>
						<?php if($is_shareholder==1){?>
						<a href="change_shareholder.php?customer_id=<?php echo $customer_id_en; ?>&user_id=<?php echo $user_id; ?>&parent_id=<?php echo $parent_id; ?>&status=<?php echo $status; ?>&pagenum=<?php echo $pagenum; ?>"><img src="../../../common/images_V6.0/operating_icon/icon53.png" align="absmiddle" alt="修改股东等级" title="修改股东等级"></a>
						<?php }?>  
						<?php if(1 == $is_ncomission){?>
						<a href="change_pro_level.php?customer_id=<?php echo $customer_id_en ;?>&user_id=<?php echo passport_encrypt($user_id);?>&parent_id=<?php echo $parent_id;?>&pagenum=<?php echo $pagenum;?>&from=pro"><img src="../../../common/images_V6.0/operating_icon/icon52.png" align="absmiddle" alt="编辑分销商等级" title="编辑分销商等级"></a>
						<?php }?>
					 <a href="promoter.php?customer_id=<?php echo $customer_id_en; ?>&id=<?php echo $id; ?>&op=resetpwd&user_id=<?php echo $user_id; ?>&pagenum=<?php echo $pagenum; ?>" onclick="if(!confirm(&#39;重置后密码为：888888。继续？&#39;)){return false};"><img src="../../../common/images_V6.0/operating_icon/icon01.png" align="absmiddle" alt="重置密码" title="重置密码"></a>
					 <a href="reset_paypassword.php?customer_id=<?php echo $customer_id_en;?>&user_id=<?php echo $user_id;?>" onclick="if(!confirm(&#39;重置后支付密码为：888888。继续？&#39;)){return false};"><img src="../../../common/images_V6.0/operating_icon/icon61.png" align="absmiddle" alt="重置支付密码" title="重置支付密码"></a> 
					
				    <?php if($status==0){?>	
					    <?php if($parent_id>0){ ?>
							<a  class=""  onclick="qrsell_confirm(1,'<?php echo $tmp;?>')"  title="成为顶级分销商">
							  <img src="../../../common/images_V6.0/operating_icon/icon32.png" align="absmiddle" alt="成为顶级分销商" title="成为顶级分销商">
							</a> 
						<?php }?>
						<a  class=""  onclick="qrsell_confirm(2,'<?php echo $tmp;?>')"  title="通过">
						<!--   <i  class="icon-ok"></i> -->
						<img src="../../../common/images_V6.0/operating_icon/icon07.png" />
						</a>
						<?php if($isAgent!=1){ ?>
						 <a  class=""  onclick="qrsell_confirm(3,'<?php echo $tmp;?>')"  title="驳回/暂停">
						 <!--  <i  class="icon-minus"></i> -->
						  <img src="../../../common/images_V6.0/operating_icon/icon25.png" />
						</a>
						<?php }?>
					<?php }else if($status==1){ ?>
						<?php if($parent_id>0){ ?>
							<a  class=""  onclick="qrsell_confirm(1,'<?php echo $tmp;?>')"  title="成为顶级分销商">
							  <img src="../../../common/images_V6.0/operating_icon/icon32.png" align="absmiddle" alt="成为顶级分销商" title="成为顶级分销商">
							</a> 
						<?php }?>
						<?php if($isAgent!=1){ ?>
					    <a  class=""  onclick="qrsell_confirm(3,'<?php echo $tmp;?>')"  title="驳回/暂停">
						 <!--  <i  class="icon-minus"></i> -->
						  <img src="../../../common/images_V6.0/operating_icon/icon25.png" />
						</a>
						<?php }?>
						 <a  class="" onclick="qrsell_confirm(5,'<?php echo $tmp;?>')" onclick="if(!confirm(&#39;确认取消上下级关系后不可恢复，继续吗？&#39;)){return false};"  title="取消上下级关系">
						 <!--  <i  class="icon-minus"></i> -->
						  <img src="../../../common/images_V6.0/operating_icon/icon26.png" />
						</a>
						
					<?php }else if($status==-1){ ?>
					   <?php if($parent_id>0){ ?>
							<a  class=""  onclick="qrsell_confirm(1,'<?php echo $tmp;?>')"  title="成为顶级分销商">
							  <img src="../../../common/images_V6.0/operating_icon/icon40.png" align="absmiddle" alt="成为顶级分销商" title="成为顶级分销商">
							</a> 
						<?php }?>
						
						<a  class=""  onclick="qrsell_confirm(2,'<?php echo $tmp;?>')"  title="通过">
						  <!--   <i  class="icon-ok"></i> -->
						<img src="../../../common/images_V6.0/operating_icon/icon07.png" />
						</a>
					<?php } ?>
					<a href="<?php echo $imgurl_qr; ?>" target="_blank">
						<img src="../../../common/images_V6.0/operating_icon/icon09.png" align="absmiddle" alt="二维码" title="二维码" onMouseOver="toolTip('<img src=<?php echo $imgurl_qr; ?>>')" onMouseOut="toolTip()"/>
					</a>
					<a onclick="qrsell_confirm(4,'<?php echo $tmp;?>')">
						<img src="../../../common/images_V6.0/operating_icon/icon04.png" align="absmiddle" alt="删除" title="删除"/>
					</a>
						
					<a href="promoter.php?customer_id=<?php echo $customer_id_en; ?>&id=<?php echo $id; ?>&op=p_tupian&user_id=<?php echo $user_id; ?>&pagenum=<?php echo $pagenum; ?>" onclick="if(!confirm(&#39;删除分销商二维码图片可重新获取&#39;)){return false};"><img src="../../../common/images_V6.0/operating_icon/icon54.png" align="absmiddle" alt="删除推广图片" title="删除推广图片"></a>					
					<a href="wu_consume_money.php?customer_id=<?php echo $customer_id_en; ?>&user_id=<?php echo $user_id; ?>"><img src="../../../common/images_V6.0/operating_icon/icon76.png" align="absmiddle" alt="无限级消费总额" title="无限级消费总额"></a>					
				   
				   </td>
				   
                </tr>			
				<?php } ?>
			</table>
		</div>
        <!--翻页开始-->
        <div class="WSY_page">
        	
        </div>
        <!--翻页结束-->
    </div>
    <!--产品管理代码结束-->
	</div>

	<div style="width:100%;height:20px;"></div>
</div>
<?php 

mysql_close($link);
?>
<!--内容框架结束-->
<script type="text/javascript" src="../../Common/js/Base/mall_setting/ToolTip.js"></script>
<script src="../../../js/fenye/jquery.page1.js"></script>
<script type="text/javascript">
pagenum  = <?php echo $pagenum; ?>;
rcount_q = <?php echo $wcount;?>; 
end      = <?php echo $end ?>;
count    = Math.ceil(rcount_q/end);//总页数
page    = Math.ceil(rcount_q/end);//总页数
    $(".WSY_data .WSY_searchdiv li").hover(function(){
        $(this).addClass("WSY_t3")
    },function(){
        $(this).removeClass("WSY_t3")
    })


//var page = count;
  	//pageCount：总页数
	//current：当前页
	$(".WSY_page").createPage({
        pageCount:count,
        current:pagenum,
        backFn:function(p){
			
		var search_status = '<?php echo $search_status;?>'; 
		var search_user_id = document.getElementById("search_user_id").value; 
		var search_name = document.getElementById("search_name").value; 
		var search_phone = document.getElementById("search_phone").value; 
		var search_generation = document.getElementById("search_generation").value; 
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;
		var url = "promoter.php?pagenum="+p+"&pagecount="+end+"&search_user_id="+search_user_id+"&search_status="+search_status+"&search_name="+search_name+"&search_phone="+search_phone+"&search_generation="+search_generation+"&customer_id=<?php echo $customer_id_en;?>"
		if(begintime !=""){
			url=url+"&begintime="+begintime;
		}
		if(endtime !=""){
			url=url+"&endtime="+endtime;
		}

		
		 document.location = url;
	   }
    });

  function jumppage(){
	var a=parseInt($("#WSY_jump_page").val()); 
	if((a<1) || (a==pagenum) || (a>page) || isNaN(a)){
		return false;
	}else{
		
		var search_status = '<?php echo $search_status;?>';  
		var search_user_id = document.getElementById("search_user_id").value; 
		var search_name = document.getElementById("search_name").value; 
		var search_phone = document.getElementById("search_phone").value; 
		var search_generation = document.getElementById("search_generation").value; 
		
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;
		var url = "promoter.php?pagenum="+a+"&pagecount="+end+"&search_user_id="+search_user_id+"&search_status="+search_status+"&search_name="+search_name+"&search_phone="+search_phone+"&search_generation="+search_generation+"&customer_id=<?php echo $customer_id_en;?>"
		if(begintime !=""){
			url=url+"&begintime="+begintime;
		}
		if(endtime !=""){
			url=url+"&endtime="+endtime;
		}
		
		 document.location = url;
	}
  }
  
    function del_tupian(){
		if(confirm("清除所有推广图片。确认？")){
			document.location="promoter.php?customer_id=<?php echo $customer_id_en; ?>&op=p_tupian2&pagenum=<?php echo $pagenum; ?>";
		}
    }


    function searchForm(){
		var search_user_id = document.getElementById("search_user_id").value; 
		var search_status = <?php echo $search_status;?>; 
		var search_generation = document.getElementById("search_generation").value; 
		var search_name = document.getElementById("search_name").value; 
		var search_phone = document.getElementById("search_phone").value; 
		
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;	
		
		var pagecount = document.getElementById("pagecount").value;	
		var url = "promoter.php?pagenum=1&pagecount="+pagecount+"&search_user_id="+search_user_id+"&search_status="+search_status+"&search_generation="+search_generation+"&search_name="+search_name+"&search_phone="+search_phone+"&customer_id=<?php echo $customer_id_en;?>"
		if(begintime !=""){
			url=url+"&begintime="+begintime;
		}
		if(endtime !=""){
			url=url+"&endtime="+endtime;
		}
		document.location= url;
   }
  
 
	function exportRecord(){
		var search_generation 	= document.getElementById("search_generation").value;//代数
		var search_status 		= '<?php echo $search_status;?>'; 
		var search_user_id 		= document.getElementById("search_user_id").value;
		var search_name 		= document.getElementById("search_name").value;
		var search_phone 		= document.getElementById("search_phone").value;
		var begintime 			= document.getElementById("begintime").value;
		var endtime 			= document.getElementById("endtime").value;		

		if(search_user_id==""){
		search_user_id="0";
		}
		if(search_name==""){
		search_name="0";
		// alert('name=====');

		}
		if(search_phone==""){
		search_phone="0";
		}
		var url='/weixin/plat/app/index.php/Excel/commonshop_excel_qrsell/customer_id/<?php echo $customer_id; ?>/status/'+search_status+'/search_user_id/'+search_user_id+'/search_name/'+search_name+'/search_phone/'+search_phone+'/exp_user_id/<?php echo $exp_user_id; ?>/pagenum/'+pagenum+'/pagecount/'+end;
		if(begintime !=""){
			url=url+'/begintime/'+begintime;
		}
		if(endtime !=""){
			url=url+'/endtime/'+endtime;
		}
		if(search_generation > 0 ){
			url=url+'/search_generation/'+search_generation;
		}
		console.log(url);
		document.location=url;
	}
	
	
	function exportRecord_All(){
		var search_status = '<?php echo $search_status;?>'; 
		var search_user_id =document.getElementById("search_user_id").value;
		var search_name =document.getElementById("search_name").value;
		var search_phone =document.getElementById("search_phone").value;
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;		

		if(search_user_id==""){
		search_user_id="0";
		}
		if(search_name==""){
		search_name="0";
		// alert('name=====');
		}
		if(search_phone==""){
		search_phone="0";
		}
		var url_base='/weixin/plat/app/index.php/Excel/commonshop_excel_qrsell_all/customer_id/<?php echo $customer_id; ?>/status/'+search_status+'/exp_user_id/<?php echo $exp_user_id; ?>/search_user_id/'+search_user_id+'/search_name/'+search_name+'/search_phone/'+search_phone;
		if(begintime !=""){
			url_base=url_base+'/begintime/'+begintime;
		}
		if(endtime !=""){
			url_base=url_base+'/endtime/'+endtime;
		}
		
		inti_per();
		ShowDIV('topLoader');
		
		if (topLoaderRunning) {
			return;
		}		
		topLoaderRunning = true;
		
		var oFunc = function () {
			url = url_base + '/limit_count/20/limit_p/'+obj_json.page+'/page_count/'+obj_json.page_count+'/count/'+obj_json.count+'/';
			console.log(url);
			$.ajax({type:'GET', async:false, url:url,
				success:function(data){
					obj_json = eval('('+data+')');
					
					if(obj_json.page_count<obj_json.page){
						closeDiv('topLoader');
						window.location.href=url+'output/go/';	
						
					}else{ }			
					
					
					console.log(obj_json.code);
				}		
			});
			
			glo_add = glo_add + glo_per;
			$topLoader.percentageLoader({progress: glo_add});
			$topLoader.percentageLoader({value: ('导出中，请勿刷新和关闭页面！')});
			//console.log('nothing'+obj_json.page);
			if(glo_add<1){
				setTimeout(oFunc, 200);
			}else{
				topLoaderRunning = false;
			}
		}		
			
		if(obj_json.length==0){
			$topLoader.percentageLoader({progress: glo_add});
			$topLoader.percentageLoader({value: ('导出中，请勿刷新和关闭页面！')});
			url = url_base + '/limit_count/20/limit_p/0/';
			$.ajax({type:'GET', async:false, url:url,
				success:function(data){
					obj_json = eval('('+data+')');
					glo_per = 1 / obj_json.page_count;
					//console.log(obj_json.code);
					setTimeout(oFunc, 1000);
					
				}		
			});	
		}else{ }
	
	}	

	var glo_add;
	var glo_per;
	var obj_json;
	var topLoaderRunning;
	var $topLoader;
	$(function() {
		inti_per();
	});

	function inti_per(){
		glo_add = 0.0;
		glo_per = 0.0;
		obj_json = new Array(); 
		$topLoader = $("#topLoader").percentageLoader({
			width: 256, height: 256, controllable: true, progress: glo_add, onProgressUpdate: function (val) {
			  this.setValue(Math.round(val * 100.0) + '%初始化中，请勿刷新和关闭页面！');
			}
		});
		topLoaderRunning = false;	
	}
	
	function ShowDIV(thisObjID) {
		$("#BgDiv").css({ display: "block", height: $(document).height() });
		var yscroll = document.documentElement.scrollTop;
		$("#" + thisObjID).css("top", "100px");
		$("#" + thisObjID).css("display", "block");
		document.documentElement.scrollTop = 0;
	}	
	
	function closeDiv(thisObjID) {
		$("#BgDiv").css("display", "none");
		$("#" + thisObjID).css("display", "none");
	}	
	
    function exportRecord1(){
		var search_status = '<?php echo $search_status;?>'; 
		var search_user_id =document.getElementById("search_user_id").value;
		var search_name =document.getElementById("search_name").value;
		var search_phone =document.getElementById("search_phone").value;
		var begintime = document.getElementById("begintime").value;
		var endtime = document.getElementById("endtime").value;		
		if(search_user_id==""){
			search_user_id="0";
	
		}
		if(search_name==""){
			search_name="0";
	
		// alert('name=====');
		}
		if(search_phone==""){
			search_phone="0";

		}
    
     var url='/weixin/plat/app/index.php/Excel/commonshop_excel_qrsell_detail/customer_id/<?php echo $customer_id; ?>/scene_id/<?php echo $user_id; ?>/type/2/status/'+search_status+'/search_user_id/'+search_user_id+'/search_name/'+search_name+'/search_phone/'+search_phone+'/pagenum/'+pagenum+'/pagecount/'+end;
	 if(begintime !=""){
		url=url+'/begintime/'+begintime;
	}
	if(endtime !=""){
		url=url+'/endtime/'+endtime;
	}
	 console.log(url);
	 document.location=url;

	 
  }
  
  function qrsell_confirm(status,tmp){
	
	  var search_status = '<?php echo $search_status;?>';
	  switch(status){
		  case 1:
			 var reason = "";
			 var i = window.confirm("确认成为顶级分销商，不会再建立上级，继续吗");
		  break;
		  case 2:
			 var reason = "";
			 var i = window.confirm("确认成为分销商，继续吗");
		  break; 
		  case 3:
			 var reason = prompt("请输入驳回/暂停理由","您不符合<?php echo $exp_name; ?>条件，请联系客服");
			 if(reason!=null){
					var i = true;
			 }else{
					var i = false;
			 }
		  break;
		  case 4:
			 var reason = "";
			 var i = window.confirm("删除后不可恢复，继续吗？");
		  break;
		  case 5:
			 var reason = "";
			 var i = window.confirm("确认取消上下级关系后不可恢复，继续吗？");
		  break;

	  }
	  //console.log(status+'='+reason+'='+i);
	  var strs= new Array();  
	  strs=tmp.split("_"); 
	  if(strs[3]==1){
		  alert("您还是代理商,请先删除代理商身份");
		  return;
	  }
	  if(strs[3]==3){
		  alert("您还是供应商,请先删除供应商身份");
		  return;
	  }
	  if(strs[3]==5){
		  alert("您还是区级代理,请先删除区级代理身份");
		  return;
	  }
	  if(strs[3]==6){
		  alert("您还是市级代理,请先删除市级代理身份");
		  return;
	  }
	  if(strs[3]==7){
		  alert("您还是省级代理,请先删除省级代理身份");
		  return;
	  }
	  //股东
	  if(strs[7]>0 && strs[8]>0){
		  alert("您还是股东,请先删除股东身份");
		  return;
	  }

	  if(i===true){	
		 $.ajax({
				type: 'POST',
				url: "qrsell_status.php",
				data: {
					type:status, 
					status:'<?php echo $status; ?>', 
					id:strs[0], 
					user_id:strs[1], 
					parent_id:strs[2], 
					isAgent:strs[3], 
					customer_id:strs[4], 
					pagenum:strs[5],
					qr_info_id:strs[6],
					reason:reason
				},
				dataType: "json",
				success:function(data){
					
					url="promoter.php?customer_id=<?php echo $customer_id_en;?>&pagenum="+pagenum+"&search_status="+search_status;
			
					location.href=url;
				} 

			}); 
		 	location.reload();
	  }else{ 
		
		location.reload();
	  }


  } 
</script>	

</body>
</html>