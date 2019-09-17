<?php
/**
 * Created by PhpStorm.  保存退货、售后操作
 * User: zhaojing
 * Date: 16/6/5
 * Time: 下午3:43
 */

header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
require('../customer_id_decrypt.php'); //导入文件,获取customer_id_en[加密的customer_id]以及customer_id[已解密]
require('../common/utility.php');
$link = mysql_connect(DB_HOST, DB_USER, DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
require('../proxy_info.php');
require('../common/utility_shop.php');

$fromuser = $_SESSION["fromuser_".$customer_id];
$dotype 	= $configutil->splash_new($_POST['dotype']);			//退货＼售后
$batchcode 	= $configutil->splash_new($_POST['batchcode']);		//订单号
//$pid 	  = $configutil->splash_new($_POST['pid']);			    //产品编号
//$supplyid 	  = $configutil->splash_new($_POST['supply_id']);			    //供应商编号
//$prvalues 	= $configutil->splash_new($_POST['prvalues']);       //产品属性
$re_type 	= $configutil->splash_new($_POST['re_type']);		//操作类型 ： 退货 ＼ 退款
$reason = $configutil->splash_new($_POST['re_reason']);		//退货原因
$remark = $configutil->splash_new($_POST['re_remark']);		//退货原因
$return_account = $configutil->splash_new($_POST['return_account']);  //退款金额
$return_count = $configutil->splash_new($_POST['return_count']); //退货数量

$return_account = round($return_account,2);	//保留两位小数

if(!empty($_GET["user_id"])){
    $user_id=$configutil->splash_new($_GET["user_id"]);
    $user_id = passport_decrypt($user_id);
}else{
    if(!empty($_SESSION["user_id_".$customer_id])){
        $user_id=$_SESSION["user_id_".$customer_id];
    }
}
/*图片上传*/
$uptypes = array('image/jpg', //上传文件类型列表
    'image/jpeg',
    'image/png',
    'image/pjpeg',
    'image/gif',
    'image/bmp',
    'image/x-png');
$max_file_size=10000000; //上传文件大小限制, 单位BYTE
$path_parts=pathinfo($_SERVER['PHP_SELF']); //取得当前路径
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $destination_folder = "../up/mshop/aftersale/" . $customer_id . '/' . $pid . '/'; //上传文件路径
    $destination = "";
    $website_default = "http://" . CLIENT_HOST . "/weixin/plat/app/html/";
    $files = $_FILES["filedata"];
    $savePath = "";

    for ($j = 0; $j < count($files); $j++) {
        if (!is_uploaded_file($files["tmp_name"][$j]))	//判断是否上传文件，是则不上传文件，使用旧文件
        {
            $destination = '';
        }else{
            if ($max_file_size < $files["size"][$j]) {
                echo "<font color='red'>文件太大！</font>";
                exit;
            }

            if (!in_array($files["type"][$j], $uptypes))//检查文件类型
            {
                echo "<font color='red'>不能上传此类型文件！</font>";
                exit;
            }
            if (!file_exists($destination_folder))
                mkdir($destination_folder, 0777, true);

            $filename = $files["tmp_name"][$j];

            $image_size = getimagesize($filename);

            $pinfo = pathinfo($files["name"][$j]);

            $ftype = $pinfo["extension"];

            $destination = $destination_folder . time() . $j . "." . $ftype;
            $overwrite = true;

            if (file_exists($destination) && $overwrite != true) {
                echo "<font color='red'>同名文件已经存在了！</font>";
                exit;
            }
            if (!move_uploaded_file($filename, $destination)) {
                echo "<font color='red'>移动文件出错！</font>";
                exit;
            }
            $save_str = str_replace("../","",$destination);
            $save_str = "/weixinpl/".$save_str;
            $savePath = $savePath."<a href=\"".$save_str."\" target=\"_blank\" style=\"color:blue\">图片".($j+1)."</a>";
        }

    }
    //$savePath = rtrim($savePath,',');

    $rtype = 1;
    //判断类型是 退货 ＼ 维权
    if(!empty($dotype) && $dotype == "aftersale"){ // 维权
        $rtype = 2;
        /*
        $sql_orders = "update weixin_commonshop_orders set aftersale_type = ".$re_type.",aftersale_state = 1
            where batchcode='".$batchcode."' and pid = ".$pid." and prvalues = '".$prvalues."'";*/
        $sql_orders = "update weixin_commonshop_orders set aftersale_type = ".$re_type.",aftersale_state = 1 , return_account='".$return_account."'
            where batchcode='".$batchcode."'";
        mysql_query($sql_orders) or die("sql_orders aftersale query error  : ".mysql_error());
        //echo "<br/> sql_orders aftersale : ".$sql_orders;

        $sql_rejects = "insert into weixin_commonshop_order_rejects(batchcode,remark,createtime,isvalid,operation_role,
            record_type,images,account,reason)
	      values('".$batchcode."','".$remark."',now(),true,0,1,'".$savePath."','".$return_account."','".$reason."')";
        mysql_query($sql_rejects) or die("sql_rejects aftersale query error  : ".mysql_error());
        //echo "<br/> sql_rejects aftersale : ".$sql_rejects;


        $return_str = "维权";
        if($re_type == 2){
            $return_str = $return_str."(退货) 申请数量：".$return_count." 申请金额：".$return_account;
        }else if($re_type == 3){
            $return_str = $return_str."(换货)";
        }
        $sql_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
	    values('".$batchcode."',18,'用户申请售后".$return_str.",原因：".$reason."，备注：".(empty($remark) ? "无" : $remark)."".$savePath."','".$fromuser."',now(),1)";
        mysql_query($sql_logs) or die("sql_logs aftersale query error  : ".mysql_error());
       // echo "<br/> sql_logs aftersale : ".$sql_logs;

    }else if($dotype == "backgoods"){ //售后退货
        /*
        $sql_orders = "update weixin_commonshop_orders set sendstatus=3,return_status = 0 , backgoods_reason='".$remark."' , return_type = ".$re_type.",return_account='".$return_account."'
            where batchcode='".$batchcode."'  and pid = ".$pid." and prvalues = '".$prvalues."'";
        */
        $sql_orders = "update weixin_commonshop_orders set sendstatus=3,return_status = 0 , backgoods_reason='".$remark."' , return_type = ".($re_type-1).",return_account='".$return_account."'
            where batchcode='".$batchcode."'";
        mysql_query($sql_orders) or die("sql_orders backgoods query error  : ".mysql_error());
        //echo "<br/> sql_orders : ".$sql_orders;

        $sql_rejects = "insert into weixin_commonshop_order_rejects
          (batchcode,remark,createtime,isvalid,operation_role,record_type,images,account,reason)
		values('".$batchcode."','".$remark."',now(),1,0,0,'".$savePath."','".$return_account."','".$reason."')";
      // echo "<br/> sql_rejects : ".$sql_rejects;
       mysql_query($sql_rejects) or die("sql_rejects backgoods query error  : ".mysql_error());


        $return_str = "";
        if($re_type == 2){
            $return_str = "退货 ,申请数量：".$return_count." 申请金额：".$return_account;
        }else if($re_type == 3){
            $return_str = "换货";
        }else if($re_type == 1){
            $return_str = "退款 , 申请数量：".$return_count." 申请金额：".$return_account;
        }
        $sql_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
		values('".$batchcode."',8,'用户申请".$return_str.",原因：".$reason.",备注：".(empty($remark)?"无":$remark)."".$savePath."','".$fromuser."',now(),1)";
        //echo "<br/> sql_logs : ".$sql_logs;
        mysql_query($sql_logs) or die("sql_logs backgoods query error  : ".mysql_error());
		
    }else{	//未发货退款
		$sql_orders = "update weixin_commonshop_orders set sendstatus=5,return_status = 0 , backgoods_reason='".$remark."' , return_type = ".($re_type-1).",return_account='".$return_account."'
            where batchcode='".$batchcode."'";
        mysql_query($sql_orders) or die("sql_orders refund query error  : ".mysql_error());
		
		$sql_rejects = "insert into weixin_commonshop_order_rejects
          (batchcode,remark,createtime,isvalid,operation_role,record_type,images,account,reason)
		values('".$batchcode."','".$remark."',now(),1,0,0,'".$savePath."','".$return_account."','".$reason."')";
      // echo "<br/> sql_rejects : ".$sql_rejects;
       mysql_query($sql_rejects) or die("sql_rejects backgoods query error  : ".mysql_error());
	   
	   $return_str = "";
	   $return_str = "退款 , 申请数量：".$return_count." 申请金额：".$return_account;
	   $sql_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
		values('".$batchcode."',8,'用户申请".$return_str.",原因：".$reason.",备注：".(empty($remark)?"无":$remark)."".$savePath."','".$fromuser."',now(),1)";
        //echo "<br/> sql_logs : ".$sql_logs;
        mysql_query($sql_logs) or die("sql_logs backgoods query error  : ".mysql_error());
	}
   /* //向退货表中添加一条记录
    $sql_as = "insert into weixin_commonshop_order_aftersale
          (batchcode,pid,prvalues,rcount,rtype,returntype,reason,picpath,account,status,customer_id,isvalid,
          createtime,user_id,remark,supply_id) values(
          '".$batchcode."',".$pid.",'".$prvalues."',".$return_count.",".$rtype.",'".$re_type."','".$reason."','".$savePath."',
          '".$return_account."' ,1,'".$customer_id."',true,now(),".$user_id.",'".$remark."','".$supplyid."'
          )";
    mysql_query($sql_as) or die("sql_as query error : ".mysql_error());
*/
}

echo "<script>";
echo "location.href='orderlist.php?currtype=7&customer_id=".$customer_id_en."&user_id=".passport_encrypt($user_id)."';";
echo "</script>";
