<?php
/**
 * Created by PhpStorm. 商品列表显示，公共部分
 * User: zhaojing
 * Date: 16/5/27
 * Time: 上午2:50
 */
$pagecount = 5 ; // 每页显示订单数
//if($currtype !=7) { //非售后


    $sql_batchcode = "select min(supply_id) supply_id , batchcode from weixin_commonshop_orders ";
if(empty($sql_cond)){
    $sql_cond = " where isvalid = true and customer_id=" . $customer_id . "  and user_id=" . $user_id;

    switch ($currtype) {
        case 1:
            // 所有订单
            break;
        case 2: //待付款
            $sql_cond = $sql_cond . " and status = 0 and paystatus = 0";
            break;
        case 3: // 待发货
            $sql_cond = $sql_cond . " and paystatus=1 and status = 0 and sendstatus = 0 ";
            break;
        case 4: //待收货
            $sql_cond = $sql_cond . " and paystatus=1 and status = 0 and sendstatus = 1";
            break;
        case 5: //待评价
            $sql_cond = $sql_cond . " and (status = 0 or status = 1) and sendstatus = 2 and is_discuss = 0 ";
            break;
        case 7: //售后中
            $sql_cond = $sql_cond . " and (sendstatus > 2 || aftersale_type > 0)";
            break;

    }
}
    $sql_batchcode .= $sql_cond;

/*}else{
    $sql_batchcode = "select supply_id,batchcode  from weixin_commonshop_order_aftersale where isvalid = true and customer_id =".$customer_id;
}*/
$start = $pagecount*($pagenum-1);
$sql_batchcode = $sql_batchcode." group by batchcode order by id desc limit ".$start." , ".$pagecount;

$result_batchcode = mysql_query($sql_batchcode) or die('Query sql_batchcode failed: ' . mysql_error());
while($row_bc = mysql_fetch_object($result_batchcode)){
    $batchcode = $row_bc -> batchcode;
    $supply_id = $row_bc -> supply_id;

    $shop_show_name  = ""; //显示的商城名
	$brand_supply_id = -1; //品牌供应商ID

    if($supply_id > 0) { //如有存在供应商编号 ，则查询供应商名称
        $sql_supplyname = "select id,brand_supply_name from weixin_commonshop_brand_supplys where isvalid=true and user_id=".$supply_id;
		$result_supply = mysql_query($sql_supplyname) or die('query sql_supplyname failed3' . mysql_error());
		if ($row_supply = mysql_fetch_object($result_supply)) {		//查询品牌供应商
			$brand_supply_id = $row_supply->id;                
			$shop_show_name  = $row_supply->brand_supply_name;      //店铺名
		}else{
			$sql_supplyname = "select shopName from weixin_commonshop_applysupplys where isvalid = true and user_id=" . $supply_id;
			$result_supply = mysql_query($sql_supplyname) or die('query sql_supplyname failed3' . mysql_error());
			if ($row_supply = mysql_fetch_object($result_supply)) {		//普通供应商
				$shop_show_name = $row_supply->shopName;                //店铺名
			}
		}
    }else{
        //查询商城名
        $sql_shopname = "select name from weixin_commonshops where isvalid=true and customer_id=".$customer_id;
        $result_shop = mysql_query($sql_shopname) or die('query sql_shopname failed'.mysql_error());
        if($row_shop = mysql_fetch_object($result_shop)) {
            $shop_show_name = $row_shop->name;					//商家名
        }
    }
    ?>
    <!-- 店铺信息 begin  -->
    <div class="shopHead">
        <ul class="am-navbar-nav am-cf am-avg-sm-1">
            <li class="tab_right_top" style="margin:0px;">
                <img class="itemPhotoCheck shopall shopCheck" src="./images/order_image/icon_shop.png">
				<span onclick="<?php if($brand_supply_id>0){echo "gotoShop(".$supply_id.")";}else{echo "gotoIndex()";}?>" class="am-navbar-label"><span class="shopName"><?php echo $shop_show_name;?></span></span>
				<img class="img_shop_right" onclick="<?php if($brand_supply_id>0){echo "gotoShop(".$supply_id.")";}else{echo "gotoIndex()";}?>" src="./images/order_image/btn_right.png">
            </li>
        </ul>
    </div>
    <!-- 店铺信息 end  -->
    <?php

    $pro_totalcount = 0;
    // 1: 全部 ； 2:待付款 ； 3:待发货 ； 4:待收货 ； 5:待评价 ； 6:已完成 （不要）； 7:售后中
    //if($currtype !=7) { //非售后
        $query = "SELECT id,pid,rcount,rcount,is_payother,paystyle,sendstatus,totalprice,status,batchcode,user_id,sendstyle,remark
				            ,createtime,paystatus,address_id,express_id,is_discuss,confirm_receivetime,supply_id,auto_receivetime,is_delay,expressnum
				            ,return_type,return_status,aftersale_state,aftersale_reason,prvalues,paytime,is_QR from weixin_commonshop_orders where isvalid=true and batchcode = '" . $batchcode . "'";
   /* }else{
        $query = "select id,pid,batchcode,supply_id,prvalues,rcount,rtype,returntype,reason,account,
                confirm_account,createtime,status from
                weixin_commonshop_order_aftersale where isvalid = true and batchcode = '".$batchcode."'";
    }*/
    $result = mysql_query($query) or die('Query OrderList failed: ' . mysql_error());

    $supply_id=-1;		//供应商编号
    $is_delay = 0;		//是否申请延时
    $return_type = -1;		//退货类型
    $return_status = -1;		//退货状态
    $aftersale_state = 0;		//售后状态
    $aftersale_reason = "";		//申请售后原因

    $express_price = 0;     //快递费
    while ($row = mysql_fetch_object($result)) {
       // if($currtype !=7) { //非售后
            $order_id = $row->id;
            $rcounts = $row->rcounts;
            $createtime = $row->createtime;
            $pid = $row->pid;
            $paystyle= $row->paystyle;
            $paystatus = $row->paystatus;
            $sendstyle=$row->sendstyle;
            $rcount = $row->rcount;
            $pro_totalprice= $row->totalprice;
            $all_goodsprice=$row->totalprice;
            $batchcode = $row->batchcode;
            $sendstatus = $row->sendstatus;
            $status = $row->status;
            $express_id = $row->express_id;
            $supply_id = $row->supply_id;//供应商ID
            $is_discuss = $row->is_discuss;  //是否评论 0:无 1:评论 2:追加
            $confirm_receivetime = $row->confirm_receivetime;   //收货时间
            $auto_receivetime = $row->auto_receivetime;
            $is_delay = $row->is_delay;
            $return_type = $row->return_type;
            $return_status = $row->return_status;
            $aftersale_state = $row->aftersale_state;
            $aftersale_reason = $row->aftersale_reason;
            $prvalues = $row -> prvalues;
            $paytime = $row -> paytime;
            $expressnum = $row -> expressnum;	//快递单号
            $date=0;
            $date=floor((strtotime($now)-strtotime($confirm_receivetime))/86400);    //计算收货时间与现在相差时间
            $is_QR = $row -> is_QR;

            $pro_totalcount = $pro_totalcount + $rcount;
        /*}else{
            $order_id = $row->id;
            $rcount = $row->rcount;
            $createtime = $row->createtime;
            $pid = $row->pid;
            $batchcode = $row->batchcode;
            $supply_id = $row->supply_id;//供应商ID
            $aftersale_state = $row->status;
            //$aftersale_reason = $row->aftersale_reason;
            $prvalues = $row -> prvalues;
            $rtype = $row ->rtype;
            $return_type = $row -> returntype;
            $account = $row ->account;
            $confirm_account = $row -> confirm_account;
        }*/


        /* 商品属性 */
        $prvstr="";
        if(!empty($prvalues)){ //商品属性不为空
            $prvarr= explode("_",$prvalues);
            for($i=0;$i<count($prvarr);$i++){
                $prvid = $prvarr[$i];
                if($prvid>0){
                    $parent_id = -1;
                    $prname = '';
                    $sql_pros = "select name,parent_id from weixin_commonshop_pros where   id=".$prvid;
                    $result_pros = mysql_query($sql_pros) or die('query sql_pros failed4'.mysql_error());
                    while($row_pros = mysql_fetch_object($result_pros)){
                        $parent_id = $row_pros->parent_id;	//是否子属性
                        $prname = $row_pros->name;			//属性名
                    }
                    $p_prname = '';
                    $query5 = "select name from weixin_commonshop_pros where  id=".$parent_id;
                    $result5 = mysql_query($query5) or die('query failed5'.mysql_error());
                    while($row5 = mysql_fetch_object($result5)){
                        $p_prname = $row5->name;		//属性名
                        $prvstr = $prvstr.$p_prname.":".$prname."  ";
                        // $prvstr = $prvstr.$prname."  ";
                    }
                }
            }
        }
        /* 商品属性 */
        $query6 = "select id,name,orgin_price,now_price,is_virtual,default_imgurl from weixin_commonshop_products where  customer_id=".$customer_id." and id=".$pid;
        $result6 = mysql_query($query6) or die('query failed6'.mysql_error());
        while($row6 = mysql_fetch_object($result6)){
            $product_id = $row6->id;						//商品ID
            $product_name = $row6->name;					//商品名
            $product_orgin_price = $row6->orgin_price;		//商品原价
            $product_now_price = $row6->now_price;			//商品现价
            $product_is_virtual = $row6->is_virtual;		//是否虚拟产品
            $product_default_imgurl = $row6->default_imgurl;//商品封面图
        }
        //weixin_commonshop_pros  商城属性表
        //weixin_commonshop_product_prices 商品属性表
        ?>
        <li class="itemWrapper" style="margin:0px;" onclick="gotoProductOrder('<?php echo $batchcode;?>')">
            <div class="itemMainDiv">
                <img class="itemPhoto" style="height: 90px;" src="<?php echo $product_default_imgurl;?>">
                <div class="contentLiDiv" style="min-height:90px;">
                    <div class="itemProName">
                        <span class="goodsName" ><?php if($is_QR == 1){?>
                        <img src="../common/images_V6.0/contenticon/coupon.png"/>
                    <?php } echo $product_name;?></span>
                        <span class="goodsPrice-red">￥
                            <?php
                                echo number_format($pro_totalprice / $rcount, 2, '.', '');
                                 ?></span>
                    </div>
                    <span class="itemProContent goodsContent"></span>
                    <div class="itemProContent goodsSize">
                        <?php echo $prvstr;?><span>x <?php echo $rcount;?></span>
                    </div>
					<?php 	$rtip = "";
							if($currtype !=7){//非售后
					
							}else{
								$rtip = $rtype == 1 ? "已申请退换货" : "已申请售后";
							}
							if(!empty($rtip)){
					?>
						<div class="goodsRedRect">
						<?php echo $rtip;?>
                        </div>
					<?php	}?>
                    <?php /* if($currtype !=7 && ($sendstatus == 2 || $sendstatus == 1)){ //发货状态为 ： 已收货,待收货?>
                    <div class="goodsRedRect"
                         onclick="toAftersale('<?php echo $batchcode;?>','<?php echo $pid;?>','<?php echo $prvalues;?>')">申请售后</div>
                    <?php }*/ ?>
                </div>
            </div>
        </li>
        <div class="horizLine1"></div>
    <?php
    }
    //if($currtype !=7) {//非售后
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
                $express_price = $row_price->ExpressPrice;
            }
        }
    ?>
    <div class="order_info">
						<span class="order_goods_count">共<?php echo $pro_totalcount;?>件商品&nbsp;&nbsp;合计:￥<span style="font-size:19px;"><?php echo $totalprice;?></span>
                            <span style="color:#aaa;">&nbsp;&nbsp;<?php if($express_price>0){echo '(含运费'.$express_price.'元)';}else{ echo '(不含运费)';}?></span></span>
    </div>
    <?php /* } else {?>
        <div class="order_info">
						<span class="order_goods_count">共<?php echo $rcount;?>件商品&nbsp;&nbsp;合计:￥<span style="font-size:19px;"><?php echo $account;?></span>
                            <span style="color:#aaa;">&nbsp;&nbsp;<?php if(!empty($confirm_account)){echo '确认退款金额：'.$confirm_account;}else{ echo '等待商家同意';}?></span></span>
        </div>
   <?php } */?>
    <div style="width:100%;">
        <ul class="am-navbar-nav am-cf am-avg-sm-1 button_area">
            <li class="tab_right_top" style="margin:0px;">
                <!-- 按钮类型 -->
        <?php
        if ($sendstatus == 2) { //发货状态为 ： 已收货
            /* ?>
              <span onclick="toAftersale('<?php echo $batchcode;?>')" class="am-navbar-label btnBlack2"><span style="color:#fff;">申请售后</span></span>
              <?php */
              if ($is_discuss == 0) {  //未评价
                  ?>
                  <span onclick="toEvaluation('<?php echo $batchcode;?>')" class="am-navbar-label btnBlack2"><span
                          style="color:#fff;">评价</span></span>
              <?php } else if ($is_discuss == 1) { //已评 ?>
                  <span onclick="toEvaluation('<?php echo $batchcode; ?>')" class="am-navbar-label btnBlack2"><span
                          style="color:#fff;">追加评价</span></span>
              <?php }?>
          <?php
          }
        ?>
                <?php
   // if($currtype !=7) {//非售后
        $currtime = time();
        $hour = floor(($currtime - strtotime($paytime)) / 3600);
        //
        if ($status == 0 && ($paystatus == 1 || $paystyle == "货到付款") && $sendstatus == 0 && $hour >= 12) { //未确认，已支付||货到付款，未发货
            //离支付时间已超过12小时则可以提醒发货
            ?>
            <span onclick="order_remind('<?php echo $batchcode;?>')" class="am-navbar-label btnWhite4"><span
                    style="color:#777;">提醒发货</span></span>
        <?php
        }
        if (($paystatus == 1 or $paystyle == "货到付款") && $sendstatus > 0) { //已支付||货到付款,不在未发货状态
            ?>            
            <?php
            if ($sendstatus == 1) {  //已发货
                    if($is_delay == 0){
                ?>
                <span onclick="order_delay('<?php echo $batchcode;?>')" class="am-navbar-label btnBlack4"><span
                        style="color:#fff;">延时收货</span></span>
                <?php }?>
                <span onclick="order_confirm('<?php echo $batchcode;?>',<?php echo $totalprice;?>)" class="am-navbar-label btnBlack4"><span
                        style="color:#fff;">确认收货</span></span>
            <?php
            }
            ?>
            <span onclick="check_express('<?php echo $expressnum;?>')" class="am-navbar-label btnWhite4">
            <span style="color:#777;">查看物流</span></span>
            <?php
                       
        }else if($paystatus == 1 && $sendstatus == 0){
            /*
            ?>
            <span onclick="toAftersale('<?php echo $batchcode;?>')" class="am-navbar-label btnBlack4"><span
                    style="color:#fff;">申请退款</span></span>
        <?php */
        }
        ?>        
        <?php
        if ($paystatus == 0 && $status == 0) { //未确认，未付款状态
            ?>
            <span onclick="order_cancel('<?php echo $batchcode;?>')" class="am-navbar-label btnBlack4"><span
                    style="color:#fff;">取消订单</span></span>
				<?php 
					$currtime = time();		//当前时间
					$recovery_time = '';	//支付失效时间
					$query_time = "select recovery_time from weixin_commonshop_order_prices where isvalid=true and batchcode='".$batchcode."' limit 1";
					$result_time = mysql_query($query_time) or die('Query_time failed:'.mysql_error());
					while($row_time = mysql_fetch_object($result_time)){
						$recovery_time = $row_time->recovery_time;
					}
				?>
            <?php if ($paystyle != "货到付款" and strtotime($recovery_time)>=$currtime) { //货到付款的不需要支付按钮 ?>
                 <span onclick="topay('<?php echo $batchcode; ?>')" class="am-navbar-label btnBlack2"><span
                        style="color:#fff;">去付款</span></span>
                <!-- <span onclick="order_otherpay('<?php echo $batchcode; ?>')" class="am-navbar-label btnBlack4"><span
                        style="color:#fff;">找人代付</span></span> -->
            <?php
            }
        }
  //  }// 非售后
                ?>

            </li>
        </ul>
	</div>
	<div class="horizLineGray"></div>
    <script type="text/javascript">
        function topay(batchcode){
            location.href="orderlist_detail.php?batchcode="+batchcode+"&customer_id=<?php echo $customer_id_en;?>&user_id=<?php echo $user_id?>#topay"
        }
    </script>
<?php
  /*  //未支付，已开启使用，余额大于0
if($paystatus == 0 and $status == 0  and $isOpen == 1 and $user_curr > 0){

    ?>
    <!--购物币-->
    <div class="itembutton">
        <div class="top">
            <span>使用购物币 (可用：<span style="color:red;display: inline;padding: 0px;margin: 0px"><?php echo $user_curr;?></span></span>)
            <input type="checkbox" id="checkbox_c1" class="chk_3">
            <label for="checkbox_c1" open_val="0" class="open_curr">
                <div class="slide_body"></div>
                <div class="slide_block"></div>
            </label>
        </div>
        <div class="currency" style="display:none">
            <div class="line"></div>
            <div class="bottom">
                <input max_currr="" class="user_currency" id="txt_curr_<?php echo $batchcode; ?>" type="number"
                       max="<?php echo $user_curr; ?>" placeholder="请输入抵用购物币数量">
            </div>
        </div>
    </div>
    <!--购物币-->
    </div>
    <div class="horizLineGray"></div>

<?php
}*/
}
?>