<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('product_type_utlity.php');
require('../common/utility_4m.php');


$u4m = new Utiliy_4m();
$rearr = $u4m->is_4M($customer_id);

//是4m分销
$is_shopgeneral = $rearr[0]  ;
//厂家编号
$adminuser_id = $rearr[1] ;
//是否是厂家总店
$is_samelevel = $rearr[2] ;
//总店模板编号
$general_template_id = $rearr[3] ;
//总店商家编号
$general_customer_id = $rearr[4] ;

//1：厂家总店； 2：代理商总店
$owner_general = $rearr[5] ;

$orgin_adminuser_id = $rearr[6] ;

$producttype_id=-1;
$btn="添加分类";

if(!empty($_GET["producttype_id"])){
   $producttype_id=$configutil->splash_new($_GET["producttype_id"]);
   $btn="保存修改";
}
$producttype_name="";
$producttype_parent_id=-1;
$producttype_sendstyle=1;
$type_imgurl="";
if($producttype_id>0 and empty($_GET["op"])){
   //编辑属性的才读取数据，删除不需要读取数据
   
   $query="select name,parent_id,sendstyle,imgurl from weixin_commonshop_types where isvalid=true and  id=".$producttype_id;
   $result = mysql_query($query) or die('Query failed: ' . mysql_error());
   while ($row = mysql_fetch_object($result)) {
       $producttype_name = $row->name;
       $producttype_parent_id = $row->parent_id;
       $producttype_sendstyle= $row->sendstyle;	      
       $type_imgurl= $row->imgurl;	      
   }
}
$op="";
if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
   $customer_ids = $u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
     

   switch($op){
	   case "del":
		   $query="update weixin_commonshop_types set isvalid=false where id=".$producttype_id;
		   mysql_query($query) or die('Query failed67: ' . mysql_error());
		   if($owner_general and !empty($customer_ids)){
			   mysql_query("update weixin_commonshop_types set isvalid=false where customer_id in (".$customer_ids.") and create_type=".$owner_general." and create_parent_id=".$producttype_id);
		   }
		   //删除后，可以重新创建
		   $producttype_id=-1;
		   break;
	   case "no_shelves":
			//搜索下级
			$product_type_utlity = new Product_Type_Utlity();
			$re=$product_type_utlity->search_off_shelves($producttype_id);
			//将商品下架
			foreach ($re as $key => $value) {
				$query_product="update weixin_commonshop_products set isout =1 WHERE customer_id=".$customer_id." and (type_ids=".$value." or type_ids like '%,".$value.",%' or type_ids like '".$value.",%' or type_ids like '%,".$value."')";
				mysql_query($query_product) or die('Query failed45: ' . mysql_error());
				
				if($owner_general and !empty($customer_ids)){
					//厂家下面的所有商品下架
					$query_product="update weixin_commonshop_products set isout =1 WHERE customer_id in (".$customer_ids.") and (type_ids=".$value." or type_ids like '%,".$value.",%' or type_ids like '".$value.",%' or type_ids like '%,".$value."') and create_parent_id=".$producttype_id;
				    mysql_query($query_product) or die('Query failed45: ' . mysql_error());
				}
			}
			//将分类下架		
			$producttype_ids=implode(",",$re);		 
			$query="update weixin_commonshop_types set is_shelves=0 where id in (".$producttype_ids.")";		
			mysql_query($query) or die('Query failed67: ' . mysql_error());
			
			break;
	   case "is_shelves":
			//搜索上级 
			if($producttype_parent_id>0){   // 判断是否顶级
			   $is_shelves = 0;
			   $query_up="select is_shelves from weixin_commonshop_types where id=".$producttype_parent_id." limit 0,1";
			   $result = mysql_query($query_up) or die('Query failed57: ' . mysql_error());
			   while ($row = mysql_fetch_object($result)) {
				   $is_shelves= $row->is_shelves;	      
			   }
			   if($is_shelves==0){
					echo"<script>alert('上级分类未上架');window.history.go(-1)</script>"; 	
					return;
			   }
			}	   
			//搜索下级
			$product_type_utlity = new Product_Type_Utlity();
			$re=$product_type_utlity->search_off_shelves($producttype_id);
			//将商品上架
			foreach ($re as $key => $value) {
				$query_product="update weixin_commonshop_products set isout =0 WHERE customer_id=".$customer_id." and (type_ids=".$value." or type_ids like '%,".$value.",%' or type_ids like '".$value.",%' or type_ids like '%,".$value."')";
				mysql_query($query_product) or die('Query failed72: ' . mysql_error());
				
				if($owner_general and !empty($customer_ids)){
					
					 $query_product="update weixin_commonshop_products set isout =0 WHERE customer_id in (".$customer_ids.") and (type_ids=".$value." or type_ids like '%,".$value.",%' or type_ids like '".$value.",%' or type_ids like '%,".$value."') and create_parent_id=".$producttype_id;
				     mysql_query($query_product) or die('Query failed72: ' . mysql_error()); 
				}
			}
			//将分类上架			
			$producttype_ids=implode(",",$re);		
			$query="update weixin_commonshop_types set is_shelves=1 where id in (".$producttype_ids.")";  
			mysql_query($query) or die('Query failed67: ' . mysql_error());
	        break; 
		case "up":
		    //升序
			$query="update weixin_commonshop_types set asort = asort+1 where id=".$producttype_id;
			mysql_query($query);
		    break;
		case "down":
		    //降序
			$query="update weixin_commonshop_types set asort = asort-1 where id=".$producttype_id;
			mysql_query($query);
		    break;
   }
}

$is_distribution=0;//渠道取消代理商功能
//代理模式,分销商城的功能项是 266
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scdl' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_distribution=1;
}
$is_supplierstr=0;//渠道取消供应商功能
//供应商模式,渠道开通与不开通
$query1="select cf.id,c.filename from customer_funs cf inner join columns c where c.isvalid=true and cf.isvalid=true and cf.customer_id=".$customer_id." and c.filename='scgys' and c.id=cf.column_id";
$result1 = mysql_query($query1) or die('Query failed: ' . mysql_error());  
$dcount= mysql_num_rows($result1);
if($dcount>0){
   $is_supplierstr=1;
}


?> 
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>无标题文档</title>
<link rel="stylesheet" type="text/css" href="shop/css/content.css">
<link rel="stylesheet" type="text/css" href="shop/css/contentblue.css"><!--内容CSS配色·蓝色-->
<!--<link rel="stylesheet" type="text/css" href="shop/css/contentGreen.css">--><!--内容CSS配色·绿色-->
<!--<link rel="stylesheet" type="text/css" href="shop/css/contentOrange.css">--><!--内容CSS配色·橙色-->
<!--<link rel="stylesheet" type="text/css" href="shop/css/contentOrange1.css">--><!--内容CSS配色·粉色色-->
<!--<link rel="stylesheet" type="text/css" href="shop/css/contentbgreen.css">--><!--内容CSS配色·蓝绿-->
<!--<link rel="stylesheet" type="text/css" href="shop/css/contentGGreen.css">--><!--内容CSS配色·草绿-->
<script type="text/javascript" src="shop/js/assets/js/jquery.min.js"></script>
<link href="css/main.css" rel="stylesheet" type="text/css">
</head>

<body>
	<!--内容框架-->
	<div class="WSY_content">

		<!--列表内容大框-->
		<div class="WSY_columnbox">
			<!--列表头部切换开始-->
			
			<div class="r_nav">
		<ul>
			<li id="auth_page0" class=""><a href="base.php?customer_id=<?php echo $customer_id; ?>">基本设置</a></li>
			<li id="auth_page1" class=""><a href="fengge.php?customer_id=<?php echo $customer_id; ?>">风格设置</a></li>
			<li id="auth_page2" class=""><a href="defaultset.php?customer_id=<?php echo $customer_id; ?>&default_set=1">首页设置</a></li>
			<li id="auth_page3" class="cur"><a href="product.php?customer_id=<?php echo $customer_id; ?>">产品管理</a></li>
			<li id="auth_page4" class=""><a href="order.php?customer_id=<?php echo $customer_id; ?>&status=-1">订单管理</a></li>
			<?php if($is_supplierstr){?><li id="auth_page5" class=""><a href="supply.php?customer_id=<?php echo $customer_id; ?>">供应商</a></li><?php }?>
			<?php if($is_distribution){?><li id="auth_page6" class=""><a href="agent.php?customer_id=<?php echo $customer_id; ?>">代理商</a></li><?php }?>
			<li id="auth_page7" class=""><a href="qrsell.php?customer_id=<?php echo $customer_id; ?>">推广员</a></li>
			<li id="auth_page8" class=""><a href="customers.php?customer_id=<?php echo $customer_id; ?>">顾客</a></li>
			<li id="auth_page9"><a href="shops.php?customer_id=<?php echo $customer_id; ?>">门店</a></li>
		</ul>
	</div>
			<div class="WSY_column_header">
				<div class="WSY_columnnav">
					<a class="white1">产品分类管理</a>
				</div>
			</div>
			<!--列表头部切换结束-->

  		<!--关注用户开始-->
		<div class="WSY_data">
        	<div class="WSY_productfl">
            	<dl class="WSY_productfl_left">
                	<dd>
                        <span>连衣裙</span>
                        <span>
                            <a href="#" title="编辑"><img src="shop/images/operating_icon/icon05.png"></a>
                            <a title="删除"><img src="shop/images/operating_icon/icon04.png"></a>
                        </span>
                    </dd>
                    <dd>
                        <span>短袖</span>
                        <span>
                            <a href="#" title="编辑"><img src="shop/images/operating_icon/icon05.png"></a>
                            <a title="删除"><img src="shop/images/operating_icon/icon04.png"></a>
                        </span>
                    </dd>
                    <dd>
                        <span>短裤</span>
                        <span>
                            <a href="#" title="编辑"><img src="shop/images/operating_icon/icon05.png"></a>
                            <a title="删除"><img src="shop/images/operating_icon/icon04.png"></a>
                        </span>
                    </dd>
                </dl>
                <dl class="WSY_productfl_right">
                	<dt>添加产品分类</dt>
                    <dd><span>类别名称：</span><input type="text" value=""></dd>
                    <dd><span>隶属关系：</span>
                    	<select>
                        	<option>-- 请选择 --</option>
                            <option>连衣裙</option>
                            <option>短袖</option>
                            <option>短裤</option>
                        </select>
                    </dd>
                    <dd><span>显示方式：</span>
                    	<i class="WSY_productfl_i01"></i>
                        <i class="WSY_productfl_i02"></i>
                    </dd>
                    <div class="WSY_text_input02">
                        <button class="WSY_buttontj">添加分类</button>
                        <button class="WSY_buttonfh">返回</button>
                    </div>
                </dl>
            </div>
		</div>
	</div>
</div>
<script type="text/javascript" src="shop/js/content.js"></script>
</body>
</html>
