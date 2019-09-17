<?php
/**
 * Created by PhpStorm.  订单列表页的操作
 * User: zhaojing
 * Date: 16/5/31
 * Time: 下午11:25
 */
header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
require('../common/utility_shop.php');

$op = "";
if(!empty($_GET["op"])){
    $op = $_GET["op"];
}
$batchcode = $configutil->splash_new($_GET["batchcode"]);

$return_arr = array(); //用于结果返回
if(empty($op)){
    $return_arr["result"] = 0;
    $return_arr["msg"] = "请选择要执行的操作！";
    echo json_encode($return_arr);
    return;
}
if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
$query_user="select weixin_fromuser from weixin_users where isvalid=true and id=".$user_id." limit 0,1";
$result_user = mysql_query($query_user) or die('Query failed: ' . mysql_error());
$fromuser = "";
while ($row_user = mysql_fetch_object($result_user)) {
	$fromuser = $row_user->weixin_fromuser;
}
if($op == "remind"){ //提醒发货
    if(empty($batchcode)){
        $return_arr["result"] = 0;
        $return_arr["msg"] = "缺少参数：订单号！";
    }else{
        $sql_insert = "insert into weixin_commonshop_order_remind (customer_id,user_id,batchcode,createtime,isvalid)
              values ('".$customer_id."','".$user_id."','".$batchcode."',now(),true)";
        mysql_query($sql_insert) or die("query sql_insert error : ".mysql_error());
        $return_arr["result"] = 1;
        $return_arr["msg"] = "提醒商家发货发送消息成功！";
    }
    echo json_encode($return_arr);
    return;
}else if($op == "cancel"){ //取消订单
    if(empty($batchcode)){
        $return_arr["result"] = 0;
        $return_arr["msg"] = "缺少参数：订单号！";
    }else{
        $status    = 1;
        $paystatus = 0;
        $sql_sel = 'SELECT  paystatus FROM weixin_commonshop_orders where isvalid=true and batchcode='.$batchcode;
        $result = mysql_query($sql_sel) or die("query sql_sel error : ".mysql_error());
        while ($row = mysql_fetch_object($result)) {
            $paystatus = $row -> paystatus;
        }

        if( 0 == $paystatus ){
            $sql_update="update weixin_commonshop_orders set status=-1 where batchcode='".$batchcode."'";
            mysql_query($sql_update) or die("query sql_sel error : ".mysql_error());
            $return_arr["result"] = -1;
            $return_arr["msg"] = "已成功取消订单！";
        }else{
            $status = 2;
            $return_arr["result"] = -1;
            $return_arr["msg"] = "已支付订单不能取消！";
        }
    }
    echo json_encode($return_arr);
    return;

}else if($op == "confirm"){
    if(empty($batchcode)){
        $return_arr["result"] = 0;
        $return_arr["msg"] = "缺少参数：订单号！";
    }else {
        $now_totalprice = $configutil->splash_new($_GET["totalprice"]);
        $query_order = "select fromuser_app from weixin_commonshop_orders where batchcode='" . $batchcode . "'";
        $result = mysql_query($query_order) or die('Query query_order failed: ' . mysql_error());
        $fromuser_app = "";
        while ($row = mysql_fetch_object($result)) {
            $fromuser_app = $row->fromuser_app;
        }

        $type = -1;
        if ($fromuser_app != "") {
            $query_user = "select type from weixin_users where weixin_fromuser='" . $fromuser_app . "'";
            $result = mysql_query($query_user) or die('Query query_user failed: ' . mysql_error());
            while ($row = mysql_fetch_object($result)) {
                $type = $row->type;
            }
        }
//file_put_contents ( "log.txt", "mul=".$type."消费反馈=1==\r\n".$fromuser_app, FILE_APPEND );
//如果为app运营商用户，则进行消费反馈
        if ($type == 4) {
            //file_put_contents ( "log.txt", "mul=消费反馈=1==\r\n", FILE_APPEND );
            //通过接口 反馈给app运营商用户
            $customerid = $customer_id;
            $app_type = 6;
            $paymoney = $now_totalprice;

            $WH_status = -1;
            $resultapp = $appconfigutil->feedback_money_score($fromuser_app, $app_type, $paymoney, $customerid);
            if ($resultapp) {
                $obj = json_decode($resultapp);
                $WH_status = $obj->status;
            }

            if ($WH_status > 0) {
                $sql_orders = "update weixin_commonshop_orders set sendstatus=2,fromuser_app='',confirm_receivetime=now() where batchcode='" . $batchcode . "'";
                mysql_query($sql_orders) or die("query sql_orders error : " . mysql_error());
            } else {
                $sql_orders = "update weixin_commonshop_orders set sendstatus=2,confirm_receivetime=now() where batchcode='" . $batchcode . "'";
                mysql_query($sql_orders) or die("query sql_orders error : " . mysql_error());
            }


        } else {
            $sql_orders = "update weixin_commonshop_orders set sendstatus=2,confirm_receivetime=now() where batchcode='" . $batchcode . "'";
            mysql_query($sql_orders) or die("query sql_orders error : " . mysql_error());
        }

        //添加订单日志
        $username = $_SESSION['fromuser_' . $customer_id];
        $sql_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
	values('" . $batchcode . "',7,'用户确认收货','" . $username . "',now(),1)";
        mysql_query($sql_logs) or die("query sql_logs error : " . mysql_error());
    }
    $return_arr["result"] = 0;
    $return_arr["msg"] = "已确认收货！";
    echo json_encode($return_arr);
    return;
}else if($op == "delay"){ //申请延迟收货
    if(empty($batchcode)){
        $return_arr["result"] = 0;
        $return_arr["msg"] = "缺少参数：订单号！";
    }else{

        $sql_update = "update weixin_commonshop_orders set is_delay = 1 where isvalid = true and sendstatus = 1 and batchcode='".$batchcode."'";
        mysql_query($sql_update) or die("sql_update query error  : ".mysql_error());

        $sql_insert = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
        values('".$batchcode."',5,'用户申请延迟收货','".$fromuser."',now(),1)";
        mysql_query($sql_insert) or die("sql_insert query error  : ".mysql_error());
        
        $return_arr["result"] = 1;
        $return_arr["msg"] = "已申请延迟收货，等待卖家确认！";
    }
    echo json_encode($return_arr);
    return;

}else if($op == "pay_currency"){ //购物币支付
	//查询商家绑定的会员卡------star
	$shop_card_id = -1;
	$query = "SELECT shop_card_id FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." limit 1";
	$result= mysql_query($query);
	while($row=mysql_fetch_object($result)){
		$shop_card_id = $row->shop_card_id;    //--------先查出商家现在绑定的是哪张会员卡
	}
	if($shop_card_id>0){
		$card_member_id = -1;
		$remain_score  = 0 ; //个人积分余额
		$query = "SELECT id FROM weixin_card_members WHERE isvalid=true AND user_id=".$user_id." AND card_id=".$shop_card_id." LIMIT 1";
		$result= mysql_query($query);
		while($row=mysql_fetch_object($result)){
			$card_member_id = $row->id;						//----------根据商家绑定会员卡id跟user_id查出会员卡id
			if($card_member_id>0){
				$query = "SELECT remain_score FROM weixin_card_member_scores where isvalid=true AND card_member_id=".$card_member_id." LIMIT 1";
				$result= mysql_query($query);
				while($row2=mysql_fetch_object($result)){
					$remain_score = round($row2->remain_score,2);		//---------再拿会员卡id查出积分余额
				}
			}
		}
	}
	
	//查询商家绑定的会员卡------end
	//查询订单需要积分-----start
	$needScore = 0;
	$sql_score = "select needScore from weixin_commonshop_order_prices where isvalid=true and batchcode='" . $batchcode . "'";
	$result_score = mysql_query($sql_score) or die('sql_score failed:'.mysql_error());
	while($row_score = mysql_fetch_object($result_score)){
		$needScore = $row_score->needScore;
	}
	//查询订单需要积分-----end
	
	if($needScore > $remain_score){
		$return_arr["result"] = -1;
        $return_arr["msg"] = "您的会员积分不够！";
		$jsons = json_encode($return_arr);
		die($jsons);
	}
	
	$custom = '购物币';	//自定义购物币名称
    $sql_cur  = "SELECT custom FROM weixin_commonshop_currency WHERE isvalid = true and customer_id=".$customer_id;
    $res_cur = mysql_query($sql_cur) or die("sql_cur failed:".mysql_error());
    if ($row_cur = mysql_fetch_object($res_cur) ){
		$custom = $row_cur->custom;
	}
	
    $user_currency = 0;
    $query_curr_user = "select currency from weixin_commonshop_user_currency where isvalid=true and customer_id=".$customer_id." and user_id=".$user_id."";
    $result_curr_user=mysql_query($query_curr_user)or die('query_curr_user Query failed'.mysql_error());
    if($row_curr_user=mysql_fetch_object($result_curr_user)){
        $user_currency = $row_curr_user->currency;
    }

    //获取订单总价
    $totalprice = 0;
    $sql_changeprice = "select totalprice from weixin_commonshop_changeprices where status=1 and isvalid=1 and batchcode='" . $batchcode . "' order by id desc limit 1";
    $result_cp = mysql_query($sql_changeprice) or die('Query sql_changeprice failed: ' . mysql_error());
    if ($row_cp = mysql_fetch_object($result_cp)) {
        $totalprice = $row_cp->totalprice;
    } else {
        //查询订单价格表中的记录
        $sql_price = "select price,NoExpPrice,ExpressPrice from weixin_commonshop_order_prices where isvalid=true and batchcode='" . $batchcode . "'";
        $result_price = mysql_query($sql_price) or die('Query sql_price failed: ' . mysql_error());
        if ($row_price = mysql_fetch_object($result_price)) {
            //获取订单的真实价格（可能是折扣总价）
            $totalprice = $row_price->price;
        }
    }
    //echo "totalprice : ".$totalprice." user_curr : ".$user_currency;
    if($totalprice > $user_currency){
        $return_arr["result"] = -1;
        $return_arr["msg"] = $custom."余额不足！";
    }else{

        /*购物币支付开始*/
		
		//插入购物币使用情况语句
		$sql_CACs = "insert into order_currencyandcoupon_t(pay_batchcode,currency,user_id,customer_id,coupon) values('".$batchcode."',".$totalprice.",".$user_id.",".$customer_id.",0)";
		$result = mysql_query($sql_CACs) or die('sql_CACs Query failed: ' . mysql_error());
		
        //插入购物币消费日志
        $sql = "insert into weixin_commonshop_currency_log(isvalid,customer_id,user_id,cost_money,cost_currency,after_currency,batchcode,status,type,class,remark,createtime)
          select true,".$customer_id.",".$user_id.",".$totalprice.",".$totalprice.",currency-".$totalprice.",".$batchcode.",1,0,1,'商城购物消费',now() from weixin_commonshop_user_currency  where isvalid=true and user_id=" . $user_id;
        mysql_query($sql) or die('购物币支付2 Query failed: ' . mysql_error());

        //支付币大于支付金额时扣除购物币，订单改成支付状态
        $sql = "update weixin_commonshop_user_currency set currency=currency-".$totalprice." where isvalid=true and user_id=" . $user_id;
        mysql_query($sql) or die('购物币支付1 Query failed: ' . mysql_error());

        $sql = "update weixin_commonshop_orders set pay_batchcode='".$batchcode."',paystatus=1,paystyle='购物币支付',paytime=now()  where isvalid=true and batchcode='" . $batchcode . "'";
        mysql_query($sql) or die('购物币支付3 Query failed: ' . mysql_error());

        $sql = "update weixin_commonshop_order_prices set pay_batchcode='".$batchcode."',paystatus=1,paystyle='购物币支付',paytime=now() where isvalid=true and batchcode='" . $batchcode . "'";
        mysql_query($sql) or die('购物币支付7 Query failed: ' . mysql_error());
		

        //这个不知道是什么操作，先注释
        
        $callBackBatchcode = $batchcode.$currency_id;//系统生成回调订单号
        $query = "insert into paycallback_t(createtime,isvalid,customer_id,pay_batchcode,callBackBatchcode,price,payClass) values(now(),true,".$customer_id.",".$batchcode.",".$callBackBatchcode.",".$totalprice.",1)";
        mysql_query($query) or die('购物币支付4 Query failed: ' . mysql_error());
        
        //添加订单日志 － 支付

        $query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
    values('".$batchcode."',2,'订单支付 － 购物币支付','".$fromuser."',now(),1)";
        mysql_query($query_log) or die('购物币支付5 Query failed: ' . mysql_error());

        //查询此订单返佣总金额
        $query_reward="select reward_money,currency,supply_id,needScore from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."'";
        $result_reward = mysql_query($query_reward) or die('W376 Query failed: ' . mysql_error());
        $reward_money	= 0;//返还的佣金
        $currency		= 0;//返还的购物币
        $supply_id		= 0;//供应商ID
        $needScore		= 0;//需要积分
        if ($row_r = mysql_fetch_object($result_reward)) {
            $reward_money = $row_r->reward_money;
            $currency = $row_r->currency;
            $supply_id = $row_r->supply_id;
            $needScore = $row_r->needScore;
        }
		
		//插入购物币使用情况语句
		$sql_CACs = "insert into order_currencyandcoupon_t(pay_batchcode,currency,user_id,customer_id,coupon) values('".$batchcode."',".$totalprice.",".$user_id.",".$customer_id.",0)";
		$result = mysql_query($sql_CACs) or die('sql_CACs Query failed: ' . mysql_error());

        //上级用户
        $query="select exp_user_id,is_QR,card_member_id from weixin_commonshop_orders where batchcode='".$batchcode."'";
        $result = mysql_query($query) or die('Query failed: ' . mysql_error());
        $exp_user_id 	= -1;
		$is_QR			= 0;	//是否二维码核销
		$card_member_id	= -1;	//会员卡ID
        if ($row = mysql_fetch_object($result)) {
            $exp_user_id 	= $row->exp_user_id;
            $is_QR 			= $row->is_QR;
			$card_member_id = $row->card_member_id;
        }
        $shopmessage 	= new shopMessage_Utlity(); 	//返佣、发信息、查找上一级
        // $shopmessage->GetMoney_Common($batchcode,$customer_id,$reward_money,$user_id,$exp_user_id,$currency);
		$shopmessage->GetMoney_Common($batchcode,$customer_id,$reward_money,$user_id,$exp_user_id,0,-1,$needScore,$card_member_id,$currency,$totalprice);//分佣
        $shopmessage->GetTicket($http_host, $batchcode);//小票打印机
		$content1 = "亲，您的".$custom."支付 -".$totalprice."元\r\n".
					"来源：【".$custom."】\n"."状态：【支付成功】\n".
					"余额：".($user_currency-$totalprice)."元\n".
				"时间：<".date( "Y-m-d H:i:s").">";
		$shopmessage->SendMessage($content1,$fromuser,$customer_id);//发送短信和邮件
		if($supply_id>0){
			$query = "select weixin_fromuser from weixin_users where isvalid=true and customer_id=".$customer_id." and id=" . $supply_id . " limit 0,1";
			$result = mysql_query($query) or die('W603 Query failed: ' . mysql_error());
			$supply_fromuser = "";
			while ($row = mysql_fetch_object($result)) {
				$supply_fromuser = $row->weixin_fromuser;
				break; 
			}
			$stringtime = date("Y-m-d H:i:s", time());
			$content = "亲，您有一笔新订单，请及时发货\n\n订单：".$batchcode."\n顾客：".$weixin_name."\n时间：<".$stringtime.">";
			$shopmessage->SendMessage($content, $supply_fromuser, $customer_id);
		}
        if($is_QR){
            $GetQR = $shopmessage->GetQR($batchcode,$fromuser,$customer_id);

            $sql_qr="update weixin_commonshop_orders set sendstatus = 2 where batchcode='".$batchcode."'";
            mysql_query($sql_qr) or die('购物币支付6 Query failed: ' . mysql_error());
			$sql = "update weixin_commonshop_order_prices set sendstatus=2,confirm_sendtime=now() where isvalid=true and batchcode='".$batchcode."'"; 
			mysql_query($sql) or die('购物币支付9 Query failed: ' . mysql_error());
        }
        $return_arr["result"] = 1;
        $return_arr["msg"] = "支付成功！";
        /*购物币支付结束*/
    }

    echo json_encode($return_arr);
    return;
}else if($op == "save_return"){
    $code = $configutil->splash_new($_POST["express_num"]);
    $express_type = $configutil->splash_new($_POST["express_name"]);
    $remark = $configutil->splash_new($_POST["remark"]);

    $sql="update weixin_commonshop_orders set return_status = 5  where isvalid = true and batchcode='".$batchcode."'";
    mysql_query($sql) or die("update orders query error  : ".mysql_error());

    $sql = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
		values('".$batchcode."',13,'用户填写退货单：快递类型：".$express_type."单号<a href=\'http://m.kuaidi100.com/result.jsp?nu=".$code."\' target=\"_blank\">".$code."</a>,;备注:".$remark."','".$fromuser."',now(),1)";
    mysql_query($sql) or die("insert logs query error  : ".mysql_error());
    //echo $sql;
    echo "<script> history.go(-2);</script>";
}else if( $op == "order_currency" ){
	//查询商家绑定的会员卡------star
	$shop_card_id = -1;
	$query = "SELECT shop_card_id FROM weixin_commonshops WHERE isvalid=true AND customer_id=".$customer_id." limit 1";
	$result= mysql_query($query);
	while($row=mysql_fetch_object($result)){
		$shop_card_id = $row->shop_card_id;    //--------先查出商家现在绑定的是哪张会员卡
	}
	if($shop_card_id>0){
		$card_member_id = -1;
		$remain_score  = 0 ; //个人积分余额
		$query = "SELECT id FROM weixin_card_members WHERE isvalid=true AND user_id=".$user_id." AND card_id=".$shop_card_id." LIMIT 1";
		$result= mysql_query($query);
		while($row=mysql_fetch_object($result)){
			$card_member_id = $row->id;						//----------根据商家绑定会员卡id跟user_id查出会员卡id
			if($card_member_id>0){
				$query = "SELECT remain_score FROM weixin_card_member_scores where isvalid=true AND card_member_id=".$card_member_id." LIMIT 1";
				$result= mysql_query($query);
				while($row2=mysql_fetch_object($result)){
					$remain_score = round($row2->remain_score,2);		//---------再拿会员卡id查出积分余额
				}
			}
		}
	}
	
	//查询商家绑定的会员卡------end
	//查询订单需要积分-----start
	$needScore = 0;
	$sql_score = "select needScore from weixin_commonshop_order_prices where isvalid=true and batchcode='" . $batchcode . "'";
	$result_score = mysql_query($sql_score) or die('sql_score failed:'.mysql_error());
	while($row_score = mysql_fetch_object($result_score)){
		$needScore = $row_score->needScore;
	}
	//查询订单需要积分-----end
	
	if($needScore > $remain_score){
		$return_arr["status"] = 10005;
        $return_arr["msg"] = "您的会员积分不够！";
		$jsons = json_encode($return_arr);
		die($jsons);
	}
	
	$currency = $configutil->splash_new($_GET["currency"]);
	$query ="select id,currency from weixin_commonshop_user_currency where isvalid=true and user_id=".$user_id;
	$result = mysql_query($query) or die('购物币查询Query failed: ' . mysql_error());
	$user_currency = 0;//钱包有的购物币
	while ($row = mysql_fetch_object($result)) {
		$currency_id	= $row->id;
		$user_currency	= $row->currency;
	}
	if( $currency > $user_currency ){
		$json["status"] = 10004;
		$json["msg"] = $custom."不足！";
		$jsons=json_encode($json);
		die($jsons);
		
	}
	//插入购物币使用情况语句
	$num = 0;
	$sql = "select count(1) as num from order_currencyandcoupon_t where pay_batchcode='".$batchcode."'";
	$result = mysql_query($sql) or die('sql failed:'.mysql_error());
	while($row = mysql_fetch_object($result)){
		$num = $row->num;
	}
	if( $num > 0 ){
		$sql_CACs="update order_currencyandcoupon_t set currency = ".$currency."  where pay_batchcode='".$batchcode."'";
	}else{
		$sql_CACs = "insert into order_currencyandcoupon_t(pay_batchcode,currency,user_id,customer_id,coupon) values('".$batchcode."',".$currency.",".$user_id.",".$customer_id.",0)";
	}
	$result = mysql_query($sql_CACs) or die('sql_CACs Query failed: ' . mysql_error());
	
	$json["status"] = 1;
	$jsons=json_encode($json);
	die($jsons);
}