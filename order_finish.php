<?php
header("Content-type: text/html; charset=utf-8");
require('../../../config.php');   //配置
require('../../../customer_id_decrypt.php');   //解密参数

$batchcode =$configutil->splash_new($_POST["batchcode"]);
$op =$configutil->splash_new($_POST["op"]);

if(!isset($customer_id)){
    $json["status"] = 10001;
    $json["line"] = 15;
    $json["msg"] = "登录超时，请重新登录！$customer_id";
    $jsons=json_encode($json);
    die($jsons);
}



$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");

$today = date("Y_m_d");
$log_username = $_SESSION['username'];

require('../../../proxy_info.php');       //OEM域名
require('../../../common/utility_shop.php');  //商城方法
$shopmessage= new shopMessage_Utlity();

switch($op){

    case "finish_order":
		//if($customer_id == 37){
        //$totalprice = $configutil->splash_new($_POST["totalprice"]);
        $query_order = 'select batchcode,totalprice from weixin_commonshop_orders where (sendstatus = 2 or sendstatus =4 or sendstatus =6) and status!=1 and customer_id ='.$customer_id;
        $result_order = mysql_query($query_order) or die('Query_status failed: ' . mysql_error());
        while ($row_order = mysql_fetch_object($result_order)) {
            $batchcode     = $row_order->batchcode;  //0:未发货；1：已发货;2:已收货;3.申请退货；4.已退货;5申请退款；6：已经退款
            $totalprice = $row_order->totalprice;

            /* 订单属性 */
            $agentcont_type = 0;
            $sendstatus     = 0;
            $order_status   = 0;
            $paystyle       = "";
            $user_id        = -1;
            $card_member_id = -1;
            $exp_user_id    = -1;
            $paytime        = "";
            $query_status   = "select sendstatus,status,card_member_id,paystyle,user_id,paytime,exp_user_id from weixin_commonshop_orders where batchcode='".$batchcode."' limit 1";
            file_put_contents("log/order_confirm_" . $today . ".txt", "\r\nquery_status=======".var_export($query_status,true)."\r\n",FILE_APPEND);
            $result_status = mysql_query($query_status) or die('Query_status failed: ' . mysql_error());
            while ($row_status = mysql_fetch_object($result_status)) {
                $sendstatus     = $row_status->sendstatus;  //0:未发货；1：已发货;2:已收货;3.申请退货；4.已退货;5申请退款；6：已经退款
                $order_status   = $row_status->status;		//1:确认完成
                $card_member_id = $row_status->card_member_id;		//会员卡号
                $paystyle       = $row_status->paystyle;		//支付方式
                $user_id        = $row_status->user_id;		//用户编号
                $paytime        = $row_status->paytime;
                $exp_user_id    = $row_status->exp_user_id;
            }
            /* 订单属性 End */
            if($sendstatus != 2 and $sendstatus != 4 and $sendstatus != 6){
                $json["status"] = 20001;
                $json["line"] = 673;
                $json["msg"] = "订单编号：".$batchcode."，无法确认订单，请检查订单状态！";
            }elseif($order_status == 1){
                $json["status"] = 20002;
                $json["line"] = 677;
                $json["msg"] = "订单编号：".$batchcode."，已确认完成，请勿重复提交！";
            }else{

                /* 商城设置 */
                $isOpenPublicWelfare=0;
                $is_cashback=0;
                $is_shareholder=0;
                $is_team=0;
                $query_set ="select isOpenPublicWelfare,is_cashback,is_shareholder,is_team from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
                file_put_contents("log/order_confirm_" . $today . ".txt", "query_set=======".var_export($query_set,true)."\r\n",FILE_APPEND);
                $result_set = mysql_query($query_set) or die('Query_set failed: ' . mysql_error());
                while ($row_set = mysql_fetch_object($result_set)) {
                    $isOpenPublicWelfare = $row_set->isOpenPublicWelfare;
                    $is_cashback         = $row_set->is_cashback;
                    $is_shareholder      = $row_set->is_shareholder;
                    $is_team             = $row_set->is_team;
                }
                /* 商城设置 End */

                /* 更改订单状态 */
                $query_up_status="update weixin_commonshop_orders set status=1 where batchcode='".$batchcode."'";
                file_put_contents("log/order_confirm_" . $today . ".txt", "query_up_status=======".var_export($query_up_status,true)."\r\n",FILE_APPEND);
                mysql_query($query_up_status);
                /* 更改订单状态 End */

                if($sendstatus != 4 and $sendstatus != 6){

                    //旧股东分红、区域团队版本结算方法调用
                    if(strtotime($paytime) < strtotime(shareholder_team_bug_time)){
                        if($is_shareholder==1){
                            file_put_contents("log/order_confirm_" . $today . ".txt", "Confirm_GetMoney_shareholder_old=======".var_export($exp_user_id,true)."\r\n",FILE_APPEND);
                            $shopmessage->Confirm_GetMoney_shareholder_old($batchcode,$customer_id,$exp_user_id);
                        }
                        if($is_team==1){
                            file_put_contents("log/order_confirm_" . $today . ".txt", "Confirm_GetMoney_team_old=======".var_export($exp_user_id,true)."\r\n",FILE_APPEND);
                            $shopmessage->Confirm_GetMoney_team_old($batchcode,$customer_id,$exp_user_id);
                        }
                    }
                    file_put_contents("log/order_confirm_" . $today . ".txt", "1.batchcode=======".var_export($batchcode,true)."\r\n",FILE_APPEND);
                    file_put_contents("log/order_confirm_" . $today . ".txt", "2.card_member_id=======".var_export($card_member_id,true)."\r\n",FILE_APPEND);
                    file_put_contents("log/order_confirm_" . $today . ".txt", "3.totalprice=======".var_export($totalprice,true)."\r\n",FILE_APPEND);
                    file_put_contents("log/order_confirm_" . $today . ".txt", "4.customer_id=======".var_export($customer_id,true)."\r\n",FILE_APPEND);
                    file_put_contents("log/order_confirm_" . $today . ".txt", "5.paystyle=======".var_export($paystyle,true)."\r\n",FILE_APPEND);

                    $shopmessage->Confirm_GetMoney_Agent($batchcode,$card_member_id,$totalprice,$customer_id,$paystyle);

                    //增加团队订单数
                    $shopmessage->Confirm_Team_order($batchcode);
                }

                //给顾客增加消费积分奖励.1 表示为商城消费
                if( $sendstatus == 2 ){
                    $shopmessage->AddScore_level($card_member_id,$totalprice,1,$paystyle);
                }
                /* 公益基金 */
                if($isOpenPublicWelfare==1){
                    $valuepercent = 0;
                    $publicwelfare=0;
                    $query_pub = "select valuepercent,publicwelfare from weixin_commonshop_publicwelfare where isvalid=true and customer_id=".$customer_id." limit 1";
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_pub=======".var_export($query_pub,true)."\r\n",FILE_APPEND);
                    $result_pub = mysql_query($query_pub);
                    while ($row_pub = mysql_fetch_object($result_pub)) {
                        $valuepercent = $row_pub->valuepercent;    //比率
                        $publicwelfare=$row_pub->publicwelfare;    //奖金池累计金额
                    }

                    /* 运费 */
                    $express_price = 0;
                    $query_express = "select price from weixin_commonshop_order_express_prices where isvalid=true and batchcode=".$batchcode." limit 1";
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_express=======".var_export($query_express,true)."\r\n",FILE_APPEND);
                    $result_express = mysql_query($query_express);
                    while ($row_express = mysql_fetch_object($result_express)) {
                        $express_price = $row_express->price;
                    }
                    /* 运费 End */

                    if($express_price>0){$totalprice=$totalprice-$express_price;}  //减去运费
                    $welfare=round($totalprice*$valuepercent,2);

                    $welfare_id = -1;
                    $query_welfare="select id,before_score,add_score from weixin_commonshop_publicwelfare_log where isvalid=true and customer_id=".$customer_id." and user_id=".$user_id." order by id desc limit 0,1";
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_welfare=======".var_export($query_welfare,true)."\r\n",FILE_APPEND);
                    $result_welfare = mysql_query($query_welfare);
                    while ($row_welfare = mysql_fetch_object($result_welfare)) {
                        $welfare_id=$row_welfare->id;
                        $before_score=$row_welfare->before_score;
                        $add_score=$row_welfare->add_score;
                    }

                    //判断此用户是否曾经捐助过
                    if($welfare_id>0){
                        $new_before_score=$before_score+$add_score;
                        $query_insert_welfare="insert into weixin_commonshop_publicwelfare_log(user_id,createtime,isvalid,customer_id,before_score,add_score,batchcode) values(".$user_id.",now(),true,".$customer_id.",".$new_before_score.",".$welfare.",".$batchcode.")";
                    }else{
                        $query_insert_welfare="insert into weixin_commonshop_publicwelfare_log(user_id,createtime,isvalid,customer_id,before_score,add_score,batchcode) values(".$user_id.",now(),true,".$customer_id.",0,".$welfare.",".$batchcode.")";
                    }
                    mysql_query($query_insert_welfare);
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_insert_welfare=======".var_export($query_insert_welfare,true)."\r\n",FILE_APPEND);

                    //累加至奖金池
                    $new_publicwelfare=round($publicwelfare+$welfare,2);
                    $query_up_public = "update weixin_commonshop_publicwelfare set publicwelfare=".$new_publicwelfare." where customer_id=".$customer_id;
                    file_put_contents("log/order_confirm_" . $today . ".txt", "new_publicwelfare=======".var_export($new_publicwelfare,true)."\r\n",FILE_APPEND);
                    mysql_query($query_up_public);
                }

                //消费返现开关
                if($is_cashback==1){
                    $sum_cashback=0;
                    $query_cash_order="select pid,rcount from weixin_commonshop_orders where isvalid=true and customer_id=".$customer_id." and batchcode=".$batchcode;
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_cash_order=======".var_export($query_cash_order,true)."\r\n",FILE_APPEND);
                    $result_cash_order = mysql_query($query_cash_order);
                    while ($row_cash_order = mysql_fetch_object($result_cash_order)) {
                        $pid=$row_cash_order->pid;
                        $rcount=$row_cash_order->rcount;
                        /* 查询返现 */
                        $query_cashback="select cashback from weixin_commonshop_products where isvalid=true and customer_id=".$customer_id." and id=".$pid;
                        file_put_contents("log/order_confirm_" . $today . ".txt", "query_cashback=======".var_export($query_cashback,true)."\r\n",FILE_APPEND);
                        $result_cashback = mysql_query($query_cashback);
                        while ($row_cashback = mysql_fetch_object($result_cashback)) {
                            $cashback=$row_cashback->cashback;
                        }
                        $sum = $cashback*$rcount;
                        $sum_cashback += $sum;
                    }

                    if($sum_cashback>0){
                        /* 插入返现记录 */
                        $query_cash_insert="insert into cashback(customer_id,user_id,isvalid,createtime,batchcode,cashback,rest_cashback) values(".$customer_id.",".$user_id.",true,now(),".$batchcode.",".$sum_cashback.",".$sum_cashback.")";
                        file_put_contents("log/order_confirm_" . $today . ".txt", "query_cash_insert=======".var_export($query_cash_insert,true)."\r\n",FILE_APPEND);
                        mysql_query($query_cash_insert);
                    }

                    //添加订单操作日志
                    $query_log = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
					values('".$batchcode."',16,'平台已确认订单完成','".$log_username."',now(),1)";
                    file_put_contents("log/order_confirm_" . $today . ".txt", "query_log=======".var_export($query_log,true)."\r\n",FILE_APPEND);
                    mysql_query($query_log);

                }


            }
        }
	//}
        $json["status"] = 0;
        $json["line"] = 41;
        $json["msg"] = "订单完成成功";
        break;

    default:
        $json["status"] = 10003;
        $json["line"] = 999;
        $json["msg"] = "未知方法";
}




$error =mysql_error();
if(!empty($error)){
    $json["status"] = 10002;
    $json["msg"] = $error;
}

if($link){mysql_close($link);}

$jsons=json_encode($json);
die($jsons);

?>