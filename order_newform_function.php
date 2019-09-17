<?php 
header("Content-type: text/html; charset=utf-8");




//组装固定键名数组

function make_arr($fromtype,$data,$type){
	
	/*参数说明：
	fromtype:		//1立即购买2购物车
	data:			//购物车数据
	type:			//1组装带键名的数据2默认数字键名数组

	*/
	
	
	if($fromtype ==1 ){
		$bug_now_array = array();		
				
			if($type == 1){
			
				$bug_now_array[$data[0]][] = array(
				'pid'=>$data[1][0],
				'prvalues'=>$data[1][1],
				'rcount'=>$data[1][2]
				);
				
			}else{
				$bug_now_array[$data[0]][] = array( $data[1][0],$data[1][1],$data[1][2] );
				
			}
		
	
	}else{
		$bug_now_array = array();	
		if(!empty($data)){
			if($type ==1){
				$bug_now_array = group_arr($data,1);	//商铺is_supply_id分组				
			}else{
				$bug_now_array = group_arr($data,2);	//不带自定义键名数组	
			}
		}
	
	}

	return $bug_now_array;
}


//数组分组
function group_arr($array,$type){
	
	/*参数说明：
	array:			//购物车数据
	type:			//1根据供应商ID分组成带自定义键名数组 2根据供应商ID分组成带默认数字键名的数组

	重组前数据
		"-1":["1510","1253_1413","10"],
		"191155":["1510","1253_1413","10"],
		"191566":["1510","1253_1413","10"],
		"-1":["1510","1253_1413","10"]


	重组后的数据：
	"-1":[["1510","1253_1413","10"],["1510","1253_1413","10"]]		//根据第一个值分组
	"191566":["1510","1253_1413","10"]
	"191155":["1510","1253_1413","10"]

	*/	
	$res = array();
	
	foreach($array as $k=>$v){
		if($type ==1 ){
			
			$temp_array = array();
			$temp_array['pid'] = $v[1][0];
			$temp_array['prvalues'] = $v[1][1];
			$temp_array['rcount'] = $v[1][2];
			$res[$v[0]][] = $temp_array; 
			
		}else{
			$temp_array = array();
			$temp_array = array($v[1][0],$v[1][1],$v[1][2]);			
			$res[$v[0]][] = $temp_array; 
			
		}	
		$i++;			
	}

	
	return $res;
}

//重组数据
function regroup_data_array($array){	//二维数组
	/*函数说明：在循环查出购买的产品详情return_data的过程同时；对旧的buy_array_add_express动态加入对应的邮费详情pro_express_data，形成新的数组buy_array_add_express
	函数中数组：
	array						：原始购物车数组
	return_data					：用于显示到页面的有关产品详情的数组；
	buy_array_add_express		：此数据是（立即购买/购物车）的原始数组，在此函数中会加入每个产品对应的运费详情，组成新的数组，用于提交订单使用

	bug_array_child_val         ：修改buy_array_add_express的每个产品的数组
	bug_array_child_val[0]		: 产品ID（本来就有）
	bug_array_child_val[1]		: 产品属性ID（本来就有）
	bug_array_child_val[2]		: 产品数量（本来就有）
	bug_array_child_val[3]		: 邮费	-- PS:在此函数中加入
	bug_array_child_val[4]		: 必填信息	--  PS:此数据会在order_form.js 中加入必填信息的数据
	bug_array_child_val[5]		: 产品属性字符串 -- PS:在此函数中加入


	*/
	//file_put_contents ( "log0809.txt", "postStr====".var_export ( $array, true ) . "\r\n", FILE_APPEND );

	global $customer_id,$user_id,$total_is_Pinformation;	//获取全局参数
	global $buy_array_add_express;							//需要把运费添加到每个产品的数组
	global $location_p; 									//获取用户所属区
	global $tmp_cout; 										//查询有无设置运费模板
	global $supply_express; 								//获取全局参数 快递数据

	$query="select name,init_reward,issell_model,advisory_telephone,advisory_flag,pro_card_level,shop_card_id,isshowdiscount,is_identity,is_showdiscuss,isOpenSales,issell "
        . "from weixin_commonshops where isvalid=true and customer_id=".$customer_id;

	$init_sell_again = 0;
	$result = mysql_query($query) or die('商品归属Query failed2: ' . mysql_error());
	while ($row = mysql_fetch_object($result)) {
		$issell			 	= $row->issell;//是否开启分销
		$pro_card_level 	= $row->pro_card_level;//购买产品需要会员卡开关
		$shop_card_id   	= $row->shop_card_id;//分销会员卡
		$is_showdiscuss 	= $row->is_showdiscuss;
		$isOpenSales 		= $row->isOpenSales;
		$isshowdiscount 	= $row->isshowdiscount;
		$is_identity 		= $row->is_identity;
		$advisory_flag		= $row->advisory_flag;
		$brand_supply_name 	= $row->name;
		$brand_tel		 	= $row->advisory_telephone;
		$init_reward 		= $row->init_reward;
		$issell_model 		= $row->issell_model;
	} 
	$Plevel = -1;
	$is_promoters = -1;
	$is_consume 	= -1;
 
	if( $user_id > 0 ){
		$sql = "select commision_level,is_consume from promoters where isvalid=true and user_id=".$user_id." and status=1";
		//file_put_contents ( "log0720.txt", "postStr====".var_export ( $sql, true ) . "\r\n", FILE_APPEND );
		$result = mysql_query($sql) or die('Query failed: ' . mysql_error());
		while ($row = mysql_fetch_object($result)) {
			$Plevel 	= $row->commision_level;
			$is_consume = $row->is_consume;
			$is_promoters = 1; 
		}
	} 


	$return_data = array();		//最终返回数据
	//file_put_contents ( "log0809.txt", "postStr====".var_export ( $array, true ) . "\r\n", FILE_APPEND );
	// file_put_contents("order_newform_function_array.txt","desc====".date("Y-m-d H:i:s")."==array159==>".var_export($array,true)."\r\n",FILE_APPEND);
	foreach($array as $k =>$val){									//每个供应商产品组遍历
		$new_supply_express = &$supply_express;
		$bug_array_par_val  = &$buy_array_add_express[$k];			//修改每个产品的邮费		
		$arr_data 			= $val;									//拆分出每个购物车产品数组	
		$buy_all_data 		= array();								//初始化-产品数组组合后的数据
		$buy_data 			= array();								//初始化-每个产品数组	
		$express_supply_array	= array(); 							//每个供应商所有产品的运费规则
		//	file_put_contents ( "log0809112.txt", "postStr====".var_export ( $arr_data, true ) . "\r\n", FILE_APPEND );
		foreach($arr_data as $key =>$values){	
			// file_put_contents("order_newform_function_array.txt","desc====".date("Y-m-d H:i:s")."==arr_data168==>".var_export($arr_data,true)."\r\n",FILE_APPEND);					
			//相同供应商下的产品遍历
			//	file_put_contents ( "log080911.txt", "postStr====".var_export ( $values, true ) . "\r\n", FILE_APPEND );	
			$bug_array_child_val = &$bug_array_par_val[$key];			//修改每个产品的邮费
			//var_dump($bug_array_child_val);
			$buy_now_supply_data 	= array();			 			//初始化-供应商信息			
			$pros_arr 				= array();					    //初始化-产品属性信息
			$express_arr_tem 		= array();						//初始化-快递信息
				
			//获取产品所需信息
			$query = 'SELECT 
			id,
			name,
			description,
			orgin_price,
			now_price,
			type_id,
			is_supply_id,
			weight,is_QR,
			default_imgurl,
			is_Pinformation,
			freight_id,
			express_type,
			pro_discount,
			is_identity,
			isvp,
			vp_score,
			is_virtual,
			need_score,
			is_free_shipping, 					
			is_invoice					
			FROM weixin_commonshop_products where id='.$values['pid'];//换数组里面的
			//echo $query2.'<br>';	
		
			$product_name        = "";		//产品名称
			$product_description = "";		//产品描述
			$orgin_price         = 0;		//产品原价
			$type_id             = -1;		
			$is_supply_id        = -1;		//供应商ID
			$weight              = 0;		//产品重量
			$is_QR               = 0; 		//是否卷 
			$imgurl              = "";  	//产品封面图
			$is_Pinformation	 = 0;  		//产品必填信息开关
			$freight_id	 		 = 0;  		//运费模板ID
			$express_type	 	 = 0;  		//邮费计费方式:0没有选择，1按件数，2按按重量
			$now_price	 	 	 = 0;  		//产品现价
			$pro_discount	 	 = 0;  		//产品折扣率
			$is_identity         = 1;		//产品是否需要身份证购买开关
			$donation_rate       = 0;		//单品捐赠比率
			$isvp	      		 = 0;		//是否为vp产品
			$vp_score      	     = 0;		//单个vp值
			$is_virtual    		 =  0;		//是否为虚拟产品 
			$is_invoice    		 =  0;		//产品发票开关 0：关闭 1：开启
			$pros_need_score     =  0;		//产品需要积分
			$is_free_shipping    =  0;		//产品是否包邮 1是，0否'
			$result = mysql_query($query) or die('W232 Query failed: ' . mysql_error());
			while ($row = mysql_fetch_object($result)) {   
				$product_name        = $row->name;      //名称
				$product_description = $row->description;  //描述
				$type_id             = $row->type_id;       
				$is_supply_id        = $row->is_supply_id;   //供应商id
				$weight              = $row->weight;  //重量
				$is_QR               = $row->is_QR;  //是否二维码产品 
				$imgurl              = $row->default_imgurl;  
				$is_Pinformation	 = $row->is_Pinformation;  
				$freight_id	 		 = $row->freight_id;  
				$express_type	 	 = $row->express_type;  
				$now_price	 	 	 = $row->now_price;  
				
				$pro_discount	 	 = $row->pro_discount;  
				$p_is_identity	 	 = $row->is_identity;  
				$donation_rate	 	 = $row->donation_rate;  
				$isvp	 	 		 = $row->isvp;  
				$vp_score	 		 = $row->vp_score;  
				$is_virtual	 		 = $row->is_virtual;  
				$is_invoice	 		 = $row->is_invoice;  
				$pros_need_score	 = $row->need_score;  
				$is_free_shipping	 = $row->is_free_shipping;  
				
			} 
			//-----存入返回数组-----//
			$buy_data['pid'] 			 = $values['pid'];		
			$buy_data['name'] 			 = $product_name;		
			$buy_data['rcount'] 		 = $values['rcount'];
			$buy_data['is_supply_id'] 	 = $is_supply_id;
			$buy_data['p_is_identity'] 	 = $p_is_identity;
			$buy_data['donation_rate'] 	 = $donation_rate;
			$buy_data['isvp'] 	 		 = $isvp;
			$buy_data['vp_score'] 	 	 = $vp_score;
			$buy_data['is_virtual'] 	 = $is_virtual;
			$buy_data['is_invoice'] 	 = $is_invoice;
			$buy_data['is_free_shipping']= $is_free_shipping;
			
			$imgurl = $new_baseurl.$imgurl;	//产品图片
			$buy_data['imgurl'] 	 	 = $imgurl;
			//-----存入返回数组-----//
					
			/*---------查找产品 多属性---------*/
			$buy_data['prvalues'] 	 = $values['prvalues'];
			$prvarr= explode("_",$values['prvalues']);  //换数组里面 
			$prvstr="";
			$pros_arr_tem = array();	//临时属性
			$pros_name_str = '';
			
			for($i=0;$i<count($prvarr);$i++){
				$prvid = $prvarr[$i];
				if($prvid==""){
					continue;
				}

				$prname = '';
				$pros_parent_id = 0;
				$query = "SELECT name,parent_id from weixin_commonshop_pros where isvalid=true and id=".$prvid;
				//echo $query.'<br>';
				$result = mysql_query($query) or die('w336 Query failed: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$prname = $row->name;
					$pros_parent_id = $row->parent_id;
					$prvstr = $prvstr.$prname."";
					
					//-----存入属性数组-----//
					$pros_arr_tem['child_id'] = $prvid;
					$pros_arr_tem['child_name'] = $prname;
					$pros_arr_tem['child_parent_id'] = $pros_parent_id;
					
					
					//----查询父类属性 start						   
					$query_pro = "SELECT name,parent_id from weixin_commonshop_pros where isvalid=true and id=".$pros_parent_id;
					$pro_parent_name = '';
					$result_pro=mysql_query($query_pro)or die('Query failed'.mysql_error());
					while($row_pro=mysql_fetch_object($result_pro)){
						$pro_parent_name = $row_pro->name;
					}
					
					$pros_arr_tem['pro_parent_name'] = $pro_parent_name;
					
					
					//----查询父类属性 end	
					
				}
				
				$pros_name_str .= $pro_parent_name.':'.$prname.'  ';
				
				//组装成一个整体
					array_push($pros_arr,$pros_arr_tem);
				
			}

			//-----存入返回数组-----//
			$buy_data['pros']  = $pros_arr;
			$buy_data['pros_name_str']  = $pros_name_str;
			$bug_array_child_val['5'] = $pros_name_str;	//把邮费重组到立即购买和购物车原始数据
						
					
			/*---------查找产品 多属性---------*/
					
			/*-----------------计算总重量和价格----------------*/
			if(!empty($values['prvalues'])){
				$query_weight = "SELECT weight,now_price,need_score from weixin_commonshop_product_prices where proids='".$values['prvalues']."' and product_id=".$values['pid']."";
				//echo $query_weight.'<br>';
				$pros_now_price = 0;
				$weight = 0;
				$result_weight = mysql_query($query_weight) or die('Query_weight failed: ' . mysql_error()); 
				while ($row_weight = mysql_fetch_object($result_weight)) {
					$weight = $row_weight->weight;
					$pros_now_price = $row_weight->now_price;
					$pros_need_score = $row_weight->need_score;
				}
			}

			//计算总重量
			$allWeights = 0;
			$allWeights = $weight*$values['rcount'];  
			
			
			//-----存入返回数组-----//	
			$buy_data['pros_need_score'] 	 = $pros_need_score;
			$buy_data['allWeights'] 	 	 = $allWeights;
			
			
			//----计算价格----//
			$totalprice = 0;
			if(!empty($values['prvalues'])){	//选择属性价格
				$totalprice = $pros_now_price * $values['rcount'];
				$now_price  = $pros_now_price;
			}else{
				$totalprice = $now_price * $values['rcount'];//假如价格表没有数据就用产品默认现价
			}		
		
			//if($issell and $pro_discount>0){	//假如使用折扣
				//$totalprice = $totalprice * $pro_discount /100;
			//} 
			if( $is_promoters == 1 && $pro_discount >0){
				$totalprice = $totalprice*$pro_discount/100;
			} 	
					 
			//----计算价格----//
					
			//-----存入返回数组-----//							
			$buy_data['now_price'] 	 =  $now_price;
			$buy_data['totalprice']  =  $totalprice;
					
					
			/*-----------------计算总重量和价格----------------*/
					
			/*----品牌供应商名称----*/
			$shop_name = '';
			$brand_logo = '';
			if($is_supply_id>0){
				$isbrand_supply = 0;	//普通供应商标识
				$supply_apply_id = 0;						
				$query_is_supply = "select id,shopName,isbrand_supply from weixin_commonshop_applysupplys where isvalid=true and user_id=".$is_supply_id." ";
				$result_is_supply=mysql_query($query_is_supply)or die('Query failed'.mysql_error());
				
				while($row_is_supply=mysql_fetch_object($result_is_supply)){
					$supply_apply_id = $row_is_supply->id;
					$shop_name = $row_is_supply->shopName;
					$isbrand_supply = $row_is_supply->isbrand_supply;
				}
				if($isbrand_supply){		//品牌供应商
					
					
					$query_supply = "select brand_name,brand_logo from weixin_commonshop_brand_supplys where isvalid=true and customer_id=".$customer_id." and user_id=".$is_supply_id."";
					$result_supply=mysql_query($query_supply)or die('Query failed'.mysql_error());
					
					while($row_supply=mysql_fetch_object($result_supply)){
						$shop_name = $row_supply->brand_name;
						$brand_logo = $row_supply->brand_logo;
					}
					
				}
			}else{							//平台
				$query="select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
				$result = mysql_query($query) or die('Query failed2: ' . mysql_error());
				while ($row = mysql_fetch_object($result)) {
					$shop_name = $row->name;
				}
			}
			/*----品牌供应商名称----*/

			//----存入供应商数组----//
			$buy_now_supply_data['supply_apply_id'] 	= $supply_apply_id;
			$buy_now_supply_data['shop_name'] 	 		= $shop_name;
			$buy_now_supply_data['brand_logo'] 	 		= $brand_logo;
			$buy_now_supply_data['isbrand_supply'] 	 	= $isbrand_supply;
								
			//----存入返回数组----//
			$buy_data['supply'] = $buy_now_supply_data;
										
			//必填信息总开关开关
			if( $is_Pinformation == 1 and $total_is_Pinformation == 1 ){
				$query2 = 'SELECT name from weixin_commonshop_product_information_t where isvalid=true and p_id='.$values['pid'];
				$i = 0;
				//echo $query2.'<br>';
				$result2 = mysql_query($query2) or die('W232 Query failed: ' . mysql_error());
				$information_name = "";
				while ($row2 = mysql_fetch_object($result2)) {   
					$information_name = $row2->name;      //名称
					$i = 1;	
					
				}
			}
			$buy_data['is_Pinformation']  =  $is_Pinformation;
			$buy_data['information_name']  =  $information_name;
			//必填信息总开关开关 

			/*----配送方式 start----*/
					
				
			//先计算单个产品筛选快递规则
					
			//非免邮产品则计算运费
			if(!$is_free_shipping){
				
				$pro_express_temp = pro_express_template($freight_id,$express_type,$totalprice,$customer_id,$k,$location_p,1);
				//var_dump($pro_express_temp);
				$tem_id 		= $pro_express_temp[0];			//运费模板
				$temp_product_express = array($tem_id,$weight,$values['rcount'],$totalprice,$express_type);
				// file_put_contents ( "order_newform_function_express_temp.txt", "desc==448==>".var_export ( $temp_product_express, true ) . "\r\n", FILE_APPEND );
				array_push($express_supply_array,$temp_product_express);
				// file_put_contents ( "order_newform_function_express_temp.txt", "desc==450==>".var_export ( $express_supply_array, true ) . "\r\n", FILE_APPEND );
			}
					
			//$buy_data['express'] =  $pro_express;				//存入产品数组
		
			/*----配送方式 end----*/
											 
			array_push($buy_all_data,$buy_data);
		}
		
		$return_data[$k] = $buy_all_data;									//组装同一个供应商下的所有产品信息	
	
		/******计算每个供应商的运费 start*****/
		//计算同一个供应商下的同一个运费模板下的产品，累计重量，件数，金额在筛选出快递规则
		// file_put_contents ( "order_newform_function_express_supply.txt", "desc==456==>".var_export ( $express_supply_array, true ) . "\r\n", FILE_APPEND );
		$rtn_express_tem_arr = pro_express_new($express_supply_array,$customer_id,$location_p,$k);	//获取供应商产品的的最优快递	
		$supply_express_price = 0;	
		// file_put_contents ( "order_newform_function_express_supply.txt", "desc==459==>".var_export ( $rtn_express_tem_arr, true ) . "\r\n", FILE_APPEND );		
		if($rtn_express_tem_arr!='failed'){	
			$shop = new shopMessage_Utlity();		
			//计算单个供应商的所有运费
			$supply_express_price = $shop->New_change_freight_direct($rtn_express_tem_arr,$customer_id,$k);	
			// file_put_contents ( "order_newform_function_express_supply.txt", "desc==464==>".var_export ( $supply_express_price, true ) . "\r\n", FILE_APPEND );	
		}	
		$new_supply_express[$k][] = $rtn_express_tem_arr;					//方便外部JS调用，用于合并购物车数据
		//var_dump($new_supply_express);
		//合并到供应商数组中
		$return_data[$k]['supply_express'][0] = 'no_use';
		$return_data[$k]['supply_express'][1] = $supply_express_price;
		
		//var_dump($express_supply_array);
		//var_dump($supply_express_price);
		//var_dump($rtn_express_tem_arr);
		/******计算每个供应商的运费 end*****/	
		
	}
	
	// file_put_contents("order_newform_function_return_data.txt","desc====".date("Y-m-d H:i:s")."==return_data482==>".var_export($return_data,true)."\r\n",FILE_APPEND);
	return $return_data;
}




//查找产品的快递模板或规则
function pro_express_template($freight_id,$express_type,$totalprice,$customer_id,$is_supply_id=-1,$location_p,$type){	
	
	/*
	@freight_id 	 ：产品绑定的快递模板
	@express_type	 ：邮费计费方式:0没有选择，1按件数，2按按重量
	@customer_id 	 ：商家ID
	@is_supply_id	 ：供应商ID
	@type 			 ：1返回快递模板 2返回快递规则ID

	*/
	$debug = false;
	
	$tem_id = -1;
	$select_express_id = -1;
	$pro_express_data = array(
		'select_express_id'=>-1,
		'is_express'=>-1,								//-1表示无配送方式， 0表示有配送方式				
		'remark'=>'no_use'								//该运费模板未添加运费规则	
	);
	
	//非免邮
	$tem_id = 0;					//运费模板ID	
	if($freight_id>0){				//大于0则选择了运费模板
		$tem_id = $freight_id;
	}else{							//小于0则使用默认模板
		
		$query2="select id from express_template_t where isvalid=true and is_default=1 and customer_id=".$customer_id." and supply_id=".$is_supply_id."";
		//echo $query2;
		//file_put_contents ( "log0303.txt", "postStr====".var_export ( $postStr, true ) . "\r\n", FILE_APPEND );
		$result=mysql_query($query2)or die('Query failed'.mysql_error());
		while($row=mysql_fetch_object($result)){
			$tem_id = $row->id;
		}
	}
			
	if($type ==1 ){				//返回快递模板
		return array($tem_id);
	}else{						//返回快递规则ID
		if($tem_id>0){
		
			$express_type_sql = '';
			if($express_type>0){		//当选择了计费类型则假如条件中
				$express_type_sql = " and type=".$express_type." ";
			}
								
			//查出运费模板中的所有规则
			$express_id	= 0;
			$express_ids = '';
			$query3 = "select express_id from express_relation_t where isvalid=true and customer_id=".$customer_id." and tem_id=".$tem_id."";	
			$result=mysql_query($query3)or die('L445 Query failed'.mysql_error());
			while($row=mysql_fetch_object($result)){
				$express_id = $row->express_id;
				$express_ids .= $express_id.',';
			}
			$express_ids = substr($express_ids,0,-1);	
			$select_express_id = -1; 			//筛选出最便宜的运费规则ID		
			if($express_ids !='' ){				//找到运费规则		
				
				if($is_supply_id>0){	//供应商快递
				
						//在运费规则中找出最优的运费规则			
						$query4 = "select id,name,FirstNum,ContinueNum,min(price) as price,ContinuePrice,type,FreeNum from weixin_expresses_supply where isvalid=true and customer_id=".$customer_id." and ((is_include=0 and region like '%".$location_p."%' ) or (is_include=1 and region not like '%".$location_p."%') or region='')  and cost<=".$totalprice." and supply_id=".$is_supply_id.$express_type_sql." and id in(".$express_ids.") group by name ORDER BY price asc  limit 1"; 

				}else{					//平台快递						
					
						//在运费规则中找出最优的运费规则				
						$query4 = "select id,name,FirstNum,ContinueNum,min(price) as price,ContinuePrice,type,FreeNum from weixin_expresses where isvalid=true and customer_id=".$customer_id." and ((is_include=0 and region like '%".$location_p."%' ) or (is_include=1 and region not like '%".$location_p."%') or region='')  and cost<=".$totalprice." ".$express_type_sql." and id in(".$express_ids.") group by name ORDER BY price asc  limit 1";											
				}	
				//file_put_contents ( "log0802.txt", "postStr====".var_export ( $query4, true ) . "\r\n", FILE_APPEND );
				//查出最优的运费规则	
				if($debug){
					echo 	$query4.'<br>';
				}							
				// file_put_contents("order_newform_function_rule.txt","desc====".date("Y-m-d H:i:s")."==568==>".$query4."\r\n",FILE_APPEND);
				$result = mysql_query($query4) or die('L768  Query failed: ' . mysql_error());		
				while ($row = mysql_fetch_object($result)) {	
					$select_express_id      = $row->id;							
				}
				// file_put_contents ( "order_newform_function_express_express_id.txt", "desc==select_express_id==>".$select_express_id. "\r\n", FILE_APPEND );
				if($select_express_id>0){
						$pro_express_data = array(
							'select_express_id'=>$select_express_id,
							'is_express'=>0,								//-1表示无配送方式， 0表示有配送方式				
							'remark'=>'ok'									//该运费模板未添加运费规则	
						);
				}else{	//无合适快递规则适用
						$pro_express_data = array(
							'select_express_id'=>$select_express_id,
							'is_express'=>-1,								//-1表示无配送方式， 0表示有配送方式				
							'remark'=>'no_fit_express_rule_select'			//无合适运费规则适用
						);
				}	
				
					
			}else{		//运费模板没有快递规则	
						$pro_express_data = array(
							'select_express_id'=>$select_express_id,
							'is_express'=>-1,								//-1表示无配送方式， 0表示有配送方式				
							'remark'=>'no_express_rule'						//无合适运费规则适用
						);	
			}
		}
	}

	$rtn_array = array($tem_id,$select_express_id,$pro_express_data);
	return $rtn_array;
	
}	



//产品的最优快递规则（累加按重，累计按件，累加产品金额）
function pro_express_new($express_array,$customer_id,$location_p,$is_supply_id){
	/*函数说明：最终返回各种不同的快递规则ID为key的数组
	express_array：[运费模板ID,单品重量,数量,产品总金额,邮费计费方式]

	*/


	$debug = false;
	
	$express_array_count 	= count($express_array);
	$tel_arr 	= array();//组合成新的数组
	// desc====2019-08-20 16:54:52==express_array==>array (
	// 	0 => 
	// 	array (
	// 	  0 => '589',
	// 	  1 => '0',
	// 	  2 => '2',
	// 	  3 => 400,
	// 	  4 => '0',
	// 	),
	// 	1 => 
	// 	array (
	// 	  0 => '599',
	// 	  1 => '212',
	// 	  2 => '3',
	// 	  3 => 264,
	// 	  4 => '0',
	// 	),
	// 	2 => 
	// 	array (
	// 	  0 => '599',
	// 	  1 => '122',
	// 	  2 => '6',
	// 	  3 => 540,
	// 	  4 => '0',
	// 	),
	//   )
	for( $i = 0; $i < $express_array_count; $i++ ){
		$tem_id  		= $express_array[$i][0];
		$Pweight 		= $express_array[$i][1];
		$Pnum    		= $express_array[$i][2];
		$totalprice 	= $express_array[$i][3];
		$express_type   = $express_array[$i][4];
		
		if( array_key_exists( $tem_id,$tel_arr ) ){		//累加同一个模板下的重量，数量，产品金额			
			
			$tel_arr[$tem_id][$express_type][0]= $tel_arr[$tem_id][$express_type][0] +( $Pweight * $Pnum );
			$tel_arr[$tem_id][$express_type][1]= $tel_arr[$tem_id][$express_type][1] + $Pnum;
			$tel_arr[$tem_id][$express_type][2]= $tel_arr[$tem_id][$express_type][2] + $totalprice;						
			
		}else{											//只计算一个模板下的重量，数量，产品金额
			$tel_arr[$tem_id][$express_type][0]= $Pweight * $Pnum ;
			$tel_arr[$tem_id][$express_type][1]= $Pnum;
			$tel_arr[$tem_id][$express_type][2]= $totalprice;
			
		}
						
	}

	if(!$debug){
	
		//var_dump($tel_arr);
	}
	
	//选出模板下的适用的最优快递规则
	foreach( $tel_arr as $t_id=>$tem_arr ){		//遍历不同的运费模板
		
	   $result = array();
	  
	   $tel_arr_tem_id 		    	= $t_id;	//模板ID
		
		foreach($tem_arr as $key=>$value){		//遍历同一个运费模板的不同模板类型
		
			$$rtn_array = array(); 
			$tel_arr_express_type 	   		= $key;		
			$tel_arr_express_weight 		= (float)$value[0];
			$tel_arr_express_num 			= (float)$value[1];
			$tel_arr_express_totalprice 	= (float)$value[2];
			
			//计算出统计后的（按重，按件，金额）快递规则ID	
			$pro_express_template_data = pro_express_template($tel_arr_tem_id,$tel_arr_express_type,$tel_arr_express_totalprice,$customer_id,$is_supply_id,$location_p);	
			
			
			if($debug){
				var_dump($pro_express_template_data);
			
			}
			$select_express_id = $pro_express_template_data[1];
			
			if($select_express_id>0){
				//数组格式：数组[快递规则ID]：array(累计重量，累计件数)
				$rtn_array[$select_express_id] = array($tel_arr_express_weight,$tel_arr_express_num);
								
			}else{
				//当有一个数组找不到快递规则，则退出循环并返回
				$rtn_array = 'failed';			
				return $rtn_array;	
			}
						
		}
		//var_dump($rtn_array);
		array_push($result,$rtn_array);
		
	}
	
	if($debug){
		echo '*****start****';
		var_dump($result);
		echo '*****end****';
		
	}
	
	return $result;
						 
	/*----配送方式----*/
}




//产品的邮费
function pro_express($pid,$ccount,$weight,$location_p,$totalprice,$is_supply_id,$customer_id){
/*函数说明：根据已选的运费模板中找出首件最便宜，在配送方位之内的运费规则，没选运费模板则选择默认模板

返回2个数组：
pro_express_data：第一个是每个产品对应的邮费详情；
pro_express_data2：第二个是返回不带键名的邮费详情数组，用于合并到购物车数据

参数说明：
@pid			:产品ID
@ccount			:产品数量
@weight			:产品重量
@location_p		:产品买家的归属地（省）
@totalprice		:产品总金额
@is_supply_id	:产品所属供应商ID(平台为-1)
@customer_id	:平台ID

*/
$debug = false ;		//调试开关
	

	//查出产品选择的运费模板
	$freight_id = 0;		//运费模板ID
	$express_type = 0;		//计费类型	1按件 2按重量
	//查询产品选择的运费模板
	$query1 = "select freight_id,express_type from weixin_commonshop_products where isvalid=true and id=".$pid."";	
	$result=mysql_query($query1)or die('L437 Query failed'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$freight_id = $row->freight_id;
		$express_type = $row->express_type;
	}
	$express_type_sql = '';
	if($express_type>0){		//当选择了计费类型则假如条件中
		$express_type_sql = " and type=".$express_type." ";
	}
	
	//假如是8.0以前的产品则选择默认模板
	
	$tem_id = 0;					//运费模板ID	
	if($freight_id>0){				//大于0则选择了运费模板
		$tem_id = $freight_id;
	}else{							//小于0则使用默认模板
		
		$query2="select id from express_template_t where isvalid=true and is_default=1 and customer_id=".$customer_id." and supply_id=".$is_supply_id."";
		
		$result=mysql_query($query2)or die('Query failed'.mysql_error());
		while($row=mysql_fetch_object($result)){
			$tem_id = $row->id;
		}
	}			
	//查出运费模板中的所有规则
	$express_id	= 0;
	$express_ids = '';
	$query3 = "select express_id from express_relation_t where isvalid=true and customer_id=".$customer_id." and tem_id=".$tem_id."";	
	$result=mysql_query($query3)or die('L445 Query failed'.mysql_error());
	while($row=mysql_fetch_object($result)){
		$express_id = $row->express_id;
		$express_ids .= $express_id.',';
	}
	$express_ids = substr($express_ids,0,-1);	
	$select_express_id = -1; 			//筛选出最便宜的运费规则ID		 
	if($express_ids !='' ){				//找到运费规则		
		/*----配送方式----*/
		if($is_supply_id>0){	//供应商快递
		
				//在运费规则中找出最优的运费规则			
				$query4 = "select id,name,FirstNum,ContinueNum,min(price) as price,ContinuePrice,type,FreeNum from weixin_expresses_supply where isvalid=true and customer_id=".$customer_id." and ((is_include=0 and region like '%".$location_p."%' ) or (is_include=1 and region not like '%".$location_p."%') or region='') and cost<=".$totalprice." and supply_id=".$is_supply_id." ".$express_type_sql." and id in(".$express_ids.") group by name ORDER BY price asc  limit 1"; 
			
		}else{					//平台快递						
			
				//在运费规则中找出最优的运费规则				
				$query4 = "select id,name,FirstNum,ContinueNum,min(price) as price,ContinuePrice,type,FreeNum from weixin_expresses where isvalid=true and customer_id=".$customer_id." and ((is_include=0 and region like '%".$location_p."%' ) or (is_include=1 and region not like '%".$location_p."%') or region='') and cost<=".$totalprice." ".$express_type_sql." and id in(".$express_ids.") group by name ORDER BY price asc  limit 1";											
		}								
		//查出最优的运费规则	 
		 $result = mysql_query($query4) or die('L646  Query failed: ' . mysql_error());		
		 while ($row = mysql_fetch_object($result)) {	
				$select_express_id      = $row->id;							
			}			
				if($select_express_id>0){				//找到合适的运费规则	 								
					if($debug){
						//调用utility_shop的计算运费方法,用于测试每个产品的邮费
						$shop = new shopMessage_Utlity();
						$pro_express_price = $shop->change_freight($select_express_id,$ccount,$weight,$is_supply_id,$customer_id);
					}else{
						$pro_express_price = 'no_use';
					}					
					$pro_express_data = array(					//返回的数组
						'select_express_id'=>$select_express_id,
						'ccount'=>$ccount,
						'weight'=>$weight,
						'location_p'=>$location_p,
						'is_express'=>0,							//-1表示无配送方式， 0表示有配送方式
						'pro_express_price'=>$pro_express_price	//计算出来的运费		
					);					
					$pro_express_data2 = array(passport_encrypt((string)$select_express_id),$ccount,$weight,$location_p,$pro_express_price);					
					$rtn_array = array($pro_express_data,$pro_express_data2);

				 }else{												//找不到规则																		
						$pro_express_data = array(
							'select_express_id'=>$select_express_id,
							'ccount'=>$ccount,
							'weight'=>$weight,
							'location_p'=>$location_p,
							'is_express'=>-1,									//-1表示无配送方式， 0表示有配送方式	
							'remark'=>'no_fit_express_rule_select'				//无合适运费规则适用		
						);						
						$pro_express_data2 = array($select_express_id,$ccount,$weight,$location_p);					
						$rtn_array = array($pro_express_data,$pro_express_data2);				
				 }
			
	}else{		
				$pro_express_data = array(
							'select_express_id'=>$select_express_id,
							'ccount'=>$ccount,
							'weight'=>$weight,
							'location_p'=>$location_p,
							'is_express'=>-1,								//-1表示无配送方式， 0表示有配送方式
							'pro_express_price'=>'',						
							'remark'=>'no_express_rule'							//该运费模板未添加运费规则	
						);							
				$pro_express_data2 = array($select_express_id,$ccount,$weight,$location_p);		
				$rtn_array = array($pro_express_data,$pro_express_data2);
	}
	
	
	if($debug){
		echo '正在调试，请勿惊讶！O(∩_∩)O <br>';	
		echo $query1.'<br>';
		echo $query2.'<br>';
		echo $query3.'<br>';
		echo $query4.'<br>';
	}
	
	//var_dump($pro_express_data);
	return $rtn_array;
						 
	/*----配送方式----*/
}




















?>