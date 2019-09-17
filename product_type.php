<?php
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../proxy_info.php');
require('../auth_user.php');
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
$query ="select isOpenPublicWelfare from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
	$result = mysql_query($query) or die('Query failed: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
	   $isOpenPublicWelfare = $row->isOpenPublicWelfare;
	}


$op="";
if(!empty($_GET["op"])){
   $op=$configutil->splash_new($_GET["op"]);
   $customer_ids = $u4m->getAllSubCustomers($adminuser_id,$orgin_adminuser_id,$owner_general);
     

   switch($op){
	   case "del":
	       //包括下级分类
	       $typeids= $u4m->getAllSubTypeIds($producttype_id);
		   $query="update weixin_commonshop_types set isvalid=false where id in (".$typeids.")";
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

<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/global.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="css/btn.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="awesome/css/font-awesome.min.css"> 
<!--[if IE 7]>
<link rel="stylesheet" href="awesome/css/font-awesome-ie7.min.css">
<![endif]-->
 
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script>
 var sendtype = <?php echo $producttype_sendstyle; ?>;
</script>
<script type="text/javascript" src="js/product.js"></script>

</head>

<body>

<style type="text/css">
body, html{background:url(images/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
	<div class="iframe_content">
			<link href="css/shop.css" rel="stylesheet" type="text/css">

	<div class="r_nav">
		<ul>
			<li id="auth_page0" class=""><a href="base.php?customer_id=<?php echo $customer_id_en; ?>">基本设置</a></li>
			<li id="auth_page1" class=""><a href="fengge.php?customer_id=<?php echo $customer_id_en; ?>">风格设置</a></li>
			<li id="auth_page2" class=""><a href="defaultset.php?customer_id=<?php echo $customer_id_en; ?>&default_set=1">首页设置</a></li>
			<li id="auth_page3" class="cur"><a href="product.php?customer_id=<?php echo $customer_id_en; ?>">产品管理</a></li>
			<li id="auth_page4" class=""><a href="order.php?customer_id=<?php echo $customer_id_en; ?>&status=-1">订单管理</a></li>
			<?php if($is_supplierstr){?><li id="auth_page5" class=""><a href="supply.php?customer_id=<?php echo $customer_id_en; ?>">供应商</a></li><?php }?>
			<?php if($is_distribution){?><li id="auth_page6" class=""><a href="agent.php?customer_id=<?php echo $customer_id_en; ?>">代理商</a></li><?php }?>
			<li id="auth_page7" class=""><a href="qrsell.php?customer_id=<?php echo $customer_id_en; ?>">推广员</a></li>
			<li id="auth_page8" class=""><a href="customers.php?customer_id=<?php echo $customer_id_en; ?>">顾客</a></li>
			<li id="auth_page9"><a href="shops.php?customer_id=<?php echo $customer_id_en; ?>">门店</a></li>
			<?php if($isOpenPublicWelfare){?><li id="auth_page10" class=""><a href="publicwelfare.php?customer_id=<?php echo $customer_id_en; ?>">公益基金</a></li><?php }?>
		</ul>
	</div>
<div id="products" class="r_con_wrap">

<div class="category">
	<div class="m_lefter">
		<dl data-listidx="0">
		    <?php 
			   $query= "select id,name,parent_id,sendstyle,is_shelves,create_type,asort from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and parent_id=-1 order by asort desc,id desc";
			   $result = mysql_query($query) or die('Query failed: ' . mysql_error());
			   while ($row = mysql_fetch_object($result)) {
				   $pt_id = $row->id;
				   $pt_name = $row->name;
				   $pt_parent_id = $row->parent_id;
				   $pt_sendstyle= $row->sendstyle;
				   $pt_is_shelves= $row->is_shelves;
				   $create_type = $row->create_type;
				   $asort = $row->asort;
				   $p_type_num=-1;
				   $sql="select count(1) as p_type_num from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and parent_id=".$pt_id;
				   $result_sql = mysql_query($sql) or die('Query failed: ' . mysql_error());
					while ($row_sql = mysql_fetch_object($result_sql)) {
						$p_type_num = $row_sql->p_type_num;
					}
				   
			?>
			<dd cateid="<?php echo $pt_id; ?>" style="cursor: pointer;">
					<div class="category no_ext">
					  <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2)) or ($create_type==3)){ ?>
						<?php if($pt_is_shelves){ ?>
						<a class="btn btn-blue" onclick="if(!confirm('您确认要下架本分类以及本分类所有商品吗？'))return false; else goUrl('product_type.php?op=no_shelves&customer_id=<?php echo $customer_id_en; ?>&producttype_id=<?php echo $pt_id;?>');" title="下架"><i class="fa fa-arrow-circle-up"></i> 已上架</a>
						<?php }else{ ?>
						<a class="btn btn-info" onclick="if(!confirm('您确认要重新上架本分类以及本分类所有商品吗？'))return false; else goUrl('product_type.php?op=is_shelves&customer_id=<?php echo $customer_id_en; ?>&producttype_id=<?php echo $pt_id;?>');" title="上架"><i class="fa fa-arrow-circle-down"></i> 已下架</a>
						<?php }
					    }
						?>	
                      <?php if((($owner_general==1 and $create_type==1) or ($owner_general==2 and $create_type==2)) or ($create_type==3)){ ?>
							<a class="btn" href="product_type.php?customer_id=<?php echo $customer_id_en; ?>&producttype_id=<?php echo $pt_id;?>" title="编辑"><i class="fa fa-gear"></i> 编辑</a>
							<a class="btn" onclick="type_del(<?php echo $p_type_num; ?>,<?php echo $pt_id; ?>)" title="删除"><i class="fa fa-trash-o"></i> 删除</a>
							
						<?php } ?>
						<a class="btn btn-success" ><?php echo $pt_name; ?></a>
						<a title="向上移动" href="javascript:void(0)" onClick="up2(<?Php echo $pt_id; ?>)"><img style="width:20px;height:20px;" src="../common/images_V6.0/operating_icon/icon32.png"></a>&nbsp;<a title="向下移动" href="javascript:void(0)" onClick="down2(<?php echo $pt_id; ?>)"><img src="../common/images_V6.0/operating_icon/icon33-1.png" style="width:20px;height:20px;"></a>
				   </div>
			 </dd>
			 <?php 
			       $str = $u4m->getSubProductTypes($pt_id,$customer_id,1,$owner_general);
				   echo $str;
			   } ?>
			
		</dl>
	</div>

	<div class="m_righter">
		<form id="frm_producttype" class="" action="save_producttype.php?customer_id=<?php echo $customer_id_en; ?>&adminuser_id=<?php echo $adminuser_id; ?>&owner_general=<?php echo $owner_general; ?>&orgin_adminuser_id=<?php echo $orgin_adminuser_id; ?>" method="post" enctype="multipart/form-data">
			<h1>添加产品分类</h1>
			<div class="opt_item">
				<label>类别名称：</label>
				<span class="input">
				<input type="text" name="name" value="<?php echo $producttype_name; ?>" class="form_input" size="15" maxlength="30" notnull="" id="name"> 
				<font class="fc_red">*</font></span>
				<div class="clear"></div>
			</div>
			<div class="opt_item">
				<label>隶属关系：</label>
				<span class="input"><select name="parent_id" id="parent_id">
				<option value="-1">顶级</option>
				<?php
				  $query = "select id, name from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id;
				  $result = mysql_query($query) or die('Query failed: ' . mysql_error());
				   while ($row = mysql_fetch_object($result)) {
					   $pt_id = $row->id;
					   $pt_name = $row->name;
				 ?>
				   <option value="<?php echo $pt_id; ?>" <?php if($producttype_parent_id==$pt_id){?>selected <?php } ?>><?php echo $pt_name; ?></option>
				<?php } ?>
				</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="opt_item">
			
				<span class="upload_file">
					<div>
						<iframe src="product_type_logo.php?customer_id=<?php echo $customer_id_en; ?>&type_imgurl=<?php echo $type_imgurl; ?>&keyid=<?php echo $producttype_id; ?>" height=200 width=100% FRAMEBORDER=0 SCROLLING=no></iframe>
						
					</div>
				</span>
				<input type=hidden name="type_imgurl" id="type_imgurl" value="<?php echo $type_imgurl ; ?>" />
			</div>
				<div class="opt_item">
					<span>显示方式：</span>
					<ul id="pro-list-type">
						<li>
							<div class="item<?php if($producttype_sendstyle==1){?> item_on<?php } ?>"  id="sendtype1" onclick="sel_sendtype(1);">
								<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-0.jpg"></div>
								<div class="filter"></div>
								<div class="bg"></div>
							</div>
						</li>
						<li>
							<div class="item<?php if($producttype_sendstyle==2){?> item_on<?php } ?>" id="sendtype2" onclick="sel_sendtype(2);">
								<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-1.jpg"></div>
								<div class="filter"></div>
								<div class="bg"></div>
							</div>
						</li>
						<li>
							<div class="item<?php if($producttype_sendstyle==3){?> item_on<?php } ?>" id="sendtype3" onclick="sel_sendtype(3);">
								<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-2.jpg"></div>
								<div class="filter"></div>
								<div class="bg"></div>
							</div>
						</li>	
						<li>
							<div class="item<?php if($producttype_sendstyle==4){?> item_on<?php } ?>" id="sendtype4" onclick="sel_sendtype(4);">
								<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-3.jpg"></div>
								<div class="filter"></div>
								<div class="bg"></div>
							</div>
						</li>							
				      </ul>
					<input type="hidden" id="keyid" name="keyid" value="<?php echo $producttype_id;?>">
					<input type="hidden" id="sendstyle" name="sendstyle" value="<?php echo $producttype_sendstyle; ?>" />
					<div class="clear"></div>
				</div>
						<div class="opt_item">
				<label></label>
				<span class="input">
				<input type="button" class="btn_green btn_w_120" name="submit_button" value="<?php echo $btn;?>" onclick="addType();" title="<?php echo $btn;?>" />
				<a href="product.php" class="btn" title="返回">返回</a></span>
				<div class="clear"></div>
			</div>
		</form>
	</div>
	<div class="list_right">
		<form id="frm_producttype" class="" action="save_producttype_list.php?customer_id=<?php echo $customer_id_en; ?>" method="post" enctype="multipart/form-data">
			<?php 
				$sql="select list_type from weixin_commonshops where customer_id=".$customer_id;
				$result = mysql_query($sql) or die('Query failed_sql_name: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$list_type = $row->list_type; 
				}
			?>
			<div class="opt_item">
				<span>所有产品显示方式：</span>
				<ul id="pro-list-type2">
					<li>
						<div class="item<?php if($list_type==1){?> item_on<?php } ?>"  id="sendtypes1" onclick="sel_sendtype2(1);">
							<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-0.jpg"></div>
							<div class="filter"></div>
							<div class="bg"></div>
						</div>
					</li>
					<li>
						<div class="item<?php if($list_type==2){?> item_on<?php } ?>" id="sendtypes2" onclick="sel_sendtype2(2);">
							<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-1.jpg"></div>
							<div class="filter"></div>
							<div class="bg"></div>
						</div>
					</li>
					<li>
						<div class="item<?php if($list_type==3){?> item_on<?php } ?>" id="sendtypes3" onclick="sel_sendtype2(3);">
							<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-2.jpg"></div>
							<div class="filter"></div>
							<div class="bg"></div>
						</div>
					</li>	
					<li>
						<div class="item<?php if($list_type==4){?> item_on<?php } ?>" id="sendtypes4" onclick="sel_sendtype2(4);"> 
							<div class="img"><img style="width:100px;height:135px;" src="images/pro-list-3.jpg"></div>
							<div class="filter"></div>
							<div class="bg"></div>
						</div>
					</li>							
				  </ul>
				  <input type="submit" class="btn_green btn_w_120" name="submit_button" value="保存修改" />
				<input type="hidden" id="sendstyle2" name="sendstyle2" value="<?php echo $list_type; ?>" />
				<div class="clear"></div>
			</div>
		</form>
	</div>
	<div class="clear"></div>
</div></div>	</div>
<div>
</div></div>
<script>
function type_del(num,pt_id){
	if(num>0){
		alert("还有子分类没有删除！");
		return;
	}else{
		if(confirm('您确认要删除吗？')){
			var url="product_type.php?op=del&customer_id=<?php echo $customer_id_en; ?>&producttype_id="+pt_id;
			goUrl(url);
		}
	}
}

function setParentDefaultimgurl(type_imgurl){
    document.getElementById("type_imgurl").value=type_imgurl;
}

function up2(pt_id){
	document.location ="product_type.php?op=up&producttype_id="+pt_id;
	
}

function down2(pt_id){
	document.location ="product_type.php?op=down&producttype_id="+pt_id;
}

</script>
<?php 

mysql_close($link);
?>
</body></html>