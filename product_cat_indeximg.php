<?php
//首页分类图
header("Content-type: text/html; charset=utf-8"); 
require('../config.php');
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');

require('../proxy_info.php');

mysql_query("SET NAMES UTF8");


//$keyid = $configutil->splash_new($_POST["keyid"]);
$keyid = $configutil->splash_new($_GET["keyid"]);
$cat_op=$_POST["cat_op"];
//echo "keyid=".$keyid;
$type_imgurl="";

if(!empty($_GET["type_imgurl"])){
    $type_imgurl=$configutil->splash_new($_GET["type_imgurl"]);
}else{
	 if($customer_id>0){
	   $query2="select cat_index_imgurl from weixin_commonshop_types where isvalid=true and id=".$keyid." and customer_id=".$customer_id;
	   //echo $query2;
		$result2 = mysql_query($query2) or die('Query failed2: ' . mysql_error());
		while ($row2 = mysql_fetch_object($result2)) {
		   $type_imgurl=$row2->cat_index_imgurl;   
		}
	}
}
$op="";
if(!empty($_GET["op"])){
   $op = $configutil->splash_new($_GET["op"]);
   if($op=="del"){  
   $keyid = $configutil->splash_new($_GET["keyid"]);
		  $query="update weixin_commonshop_types set cat_index_imgurl='' where id=".$keyid;	
//echo $query;  
		  mysql_query($query);
	  
	  $type_imgurl="";
   }  
}
//$new_baseurl = BaseURL."back_commonshop/";
$new_baseurl ="http://".$http_host."/weixinpl/up/"; 

$n_width=500;
$n_height=250;
 
?>

<!DOCTYPE html>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title></title>
<link href="css/global.css" rel="stylesheet" type="text/css">
<link href="css/main.css" rel="stylesheet" type="text/css">
<link href="operamasks-ui.css" rel="stylesheet" type="text/css">
<link href="css/shop.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery.uploadify-3.1.min.js"></script>
<link href="css/uploadify.css" rel="stylesheet" type="text/css" />

</head>
<body style="font-size:12px;">
<div id="products" class="r_con_wrap" style="background:none">
<span class="input">
<span class="upload_file">
	<div>
	   <form action="save_pro_catimg.php?customer_id=<?php echo $customer_id_en; ?>" id="frm_img" enctype="multipart/form-data" method="post">
			<div class="up_input">
			<input name="upfile" id="upfile" type="file"  width="120" height="30" value="Submit" onchange="uploading()">
			<div id="xianshi" style="display:none;margin:50px 0px 50px 135px;"><img src="images/upload.gif"></div>
			<div id="PicUploadQueue" class="om-fileupload-queue"></div>
			</div>
			<input type=hidden name="customer_id" id="customer_id" value="<?php echo $customer_id_en; ?>" />
			<input type=hidden name="keyid" id="keyid" value="<?php echo $keyid; ?>" />
		</form>
		<div class="tips" style="font-size:12px;"></div>
		<div class="clear"></div>
	</div>
</span>


<div class="img" id="PicDetail">
  
        <?php if(!empty($type_imgurl)){ ?>
		<div style="width:100%;height:120px;">
			 <a href="<?php echo $new_baseurl.$type_imgurl; ?>" target="_blank">
			 <img style="width:100%;height:120px;" src="<?php echo $new_baseurl.$type_imgurl; ?>"></a>
			 <span style="top:100px;width:100%;" onclick="delImg(<?php echo $keyid; ?>);">删除</span>
		</div>
        <?php } ?>
  
</div>
</span>
<div class="clear"></div>
</div>
<?php 

mysql_close($link);
?>
<script type="text/javascript">  
  function uploading(){   
	  	document.getElementById("xianshi").style.display="block";
	  }	
    function upload(){  
        var element = document.getElementById("upfile");  
        if("\v"=="v")  
        {  
            element.onpropertychange = uploadHandle;  
        }  
        else  
        {  
            element.addEventListener("change",uploadHandle,false);  
        }  
  
        function uploadHandle()  
        {  
            if(element.value)  
            {  
              
			  $("#frm_img").submit();
  
            }  
        }  
  
    } 
	parent.setParentDefaultimgurl_cat('<?php echo $type_imgurl; ?>');
	
	
	function delImg(id){
	   url = "product_cat_indeximg.php?op=del&keyid="+id+"&customer_id=<?php echo $customer_id_en; ?>";
	   document.location= url;
	}
  
</script>  
  
<script type="text/javascript">  
    upload();  
</script>  
</body>
</html>