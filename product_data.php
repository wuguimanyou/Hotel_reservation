<?php
header("Content-type: text/html; charset=utf-8"); 
require('../../../config.php');
require('../../../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../../../back_init.php');
$link = mysql_connect(DB_HOST,DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");


$op = '';
$op = $_GET['op'];
$data = $configutil->splash_new($_POST["data"]); 
$res = array(
	'code'=>10000,
	'msg'=>'',
	'data'=>''
); 
switch($op){
	case 'check_pros_extends':
		$extends_id = -1;
		$extends_pros_id = -1;
		$query = "select id,pros_id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and relation_type_id=".$data."";
		//echo $query;
		$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
		while ($row = mysql_fetch_object($result)){			
			$extends_id 		= $row->id;
			$extends_pros_id 	= $row->pros_id;
		}
		if($extends_id<0){
			//查找分类的父类是否被关联
			$type_parent_id = -1;
			$query = "select parent_id from weixin_commonshop_types where isvalid=true and customer_id=".$customer_id." and id=".$data."";
			//echo $query;
			$result = mysql_query($query) or die('Query failed: ' . mysql_error());  
			while ($row = mysql_fetch_object($result)){			
				$type_parent_id 		= $row->parent_id;
				
				$query2 = "select id,pros_id,relation_type_id from weixin_commonshop_pros_extends where isvalid=true and customer_id=".$customer_id." and relation_type_id=".$type_parent_id."";
				//echo $query2;
				$result2 = mysql_query($query2) or die('Query failed: ' . mysql_error());  
				while ($row2 = mysql_fetch_object($result2)){			
					$extends_id 		= $row2->id;
					$extends_pros_id 	= $row2->pros_id;
				}
			}
		}
		if($extends_id>0){
			
				//查找父类属性
				$pros_id = -1;
				$pros_name = '';
				$pros_parent_id = -1;
				$query= "select id,name,parent_id from weixin_commonshop_pros where isvalid=true  and customer_id=".$customer_id. " and id=".$extends_pros_id."";
				//echo $query;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$pros_id 		= $row->id;
					$pros_name 	    = $row->name;
				}
				
				//查找子类
				$pros_child_id = -1;
				$pros_child_name = '';
				$child_arr = array();
				$query= "select id,name from weixin_commonshop_pros where isvalid=true  and customer_id=".$customer_id. " and parent_id=".$pros_id."";
				//echo $query;
				$result = mysql_query($query) or die('Query failed: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {

					$pros_child_id 	    	= $row->id;
					$pros_child_name 	    = $row->name;
					
					array_push($child_arr,array($pros_child_id,$pros_child_name));
				
				}
				
				$rtn_data = array(
					'pro_id'			=>$extends_pros_id,
					'pros_name'			=>$pros_name,
					'pros_child_data'	=>$child_arr,									
				);
				//var_dump($rtn_data);
				
				$res = array(
					'code'=>10008,
					'msg'=>'success',
					'data'=>$rtn_data
				);
				
		}
		$out = json_encode($res);
		echo $out;		
	break;
}





























?>