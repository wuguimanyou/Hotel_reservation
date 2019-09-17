<?php
/**
 * Created by PhpStorm. 保存产品评价
 * User: zhaojing
 * Date: 16/5/29
 * Time: 上午12:47
 */

header("Content-type: text/html; charset=utf-8");
require('../config.php'); //配置
$link = mysql_connect(DB_HOST,DB_USER,DB_PWD);
mysql_select_db(DB_NAME) or die('Could not select database');
mysql_query("SET NAMES UTF8");
require('../common/common_from.php'); 

file_put_contents('orderlist_save_evaluation.txt','desc==='.date('Y-m-d H:i:s').'====>'.var_export($_REQUEST,true)."\r\n",FILE_APPEND);

$pids 		  = $_POST['pid'];				//商品ID
$levels 		  = $_POST['level'];			//1好评，2中评，3差评
$discuss 	  = $_POST['discuss'];			//评论
$batchcode 	  = $_POST['batchcode'];		//订单号
$is_anonymous = $_POST['is_anonymous'];		//是否匿名
$supply_id    = $_POST['supply_id'];	//品牌供应商ID

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
    for ($i = 0; $i < count($pids); $i++) {
        $pid = $pids[$i];
        $destination_folder = "../up/mshop/evaluations/" . $customer_id . '/' . $pid . '/'; //上传文件路径
        $destination = "";
        $website_default = "http://" . CLIENT_HOST . "/weixin/plat/app/html/";

        $pidfiles = $_FILES["Filedata_" . $pid];
      //  echo "<br/> pidfiles : ".$pidfiles." count : ".count($pidfiles);
       // var_dump ($pidfiles);
		$destination_a = '';
        for ($j = 0; $j < count($pidfiles); $j++) {
            if (!is_uploaded_file($pidfiles["tmp_name"][$j]))	//判断是否上传文件，是则不上传文件，使用旧文件
            {
                $destination = '';
                //echo  $destination;
            }else{
                //$file = $pidfiles[$j];
                if ($max_file_size < $pidfiles["size"][$j]) {
                    echo "<font color='red'>文件太大！</font>";
                    exit;
                }
                //echo "<br/> file  : ".$file;
                //echo "<br/> type  : ".$pidfiles["type"][$j];
                if (!in_array($pidfiles["type"][$j], $uptypes))//检查文件类型
                {
                    echo "<font color='red'>不能上传此类型文件！</font>";
                    exit;
                }
                if (!file_exists($destination_folder))
                    mkdir($destination_folder, 0777, true);

                $filename = $pidfiles["tmp_name"][$j];

                $image_size = getimagesize($filename);

                $pinfo = pathinfo($pidfiles["name"][$j]);

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

                $destination_a = $destination_a . $destination . ',';
            }

        }
        $destination_a = rtrim($destination_a,',');
        //echo "<br/>  destination_a : ".$destination_a;
        $is_discuss=0; //评论状态 1:已评 ; 2:追加评价
        $query = "select is_discuss from weixin_commonshop_orders where batchcode=".$batchcode;
        $result = mysql_query($query) or die('Query discuss:'.mysql_error());
        while ($row = mysql_fetch_object($result)) {
            $is_discuss =  $row->is_discuss;
        }
        switch($is_discuss){
            case 0:
                //插入评论
                if(!empty($destination_a)){
                    $sql="insert into weixin_commonshop_product_evaluations(user_id,product_id,level,isvalid,createtime,discuss,discussimg,type,batchcode,customer_id,is_anonymous) values(".$user_id.",".$pid.",".$levels[$i].",true,now(),'".$discuss[$i]."','".$destination_a."',1,".$batchcode.",".$customer_id.",".$is_anonymous.")";
                }else{
                    $sql="insert into weixin_commonshop_product_evaluations(user_id,product_id,level,isvalid,createtime,discuss,type,batchcode,customer_id,is_anonymous) values(".$user_id.",".$pid.",".$levels[$i].",true,now(),'".$discuss[$i]."',1,".$batchcode.",".$customer_id.",".$is_anonymous.")";
                }
               // echo $sql;
                mysql_query($sql) or die('Query insert evaluations : ' . mysql_error());
                //修改订单评价状态
                $sql2="update weixin_commonshop_orders set is_discuss=1 where batchcode=".$batchcode." and pid=".$pid;
                mysql_query($sql2) or die('Query update orders : ' . mysql_error());
                //增加商品评价数
                switch($level[$i]){
                    case 1:
                        $sql="update weixin_commonshop_products set good_level=good_level+1 where id=".$pid;
                        mysql_query($sql);
                        break;
                    case 2:
                        $sql="update weixin_commonshop_products set meu_level=meu_level+1 where id=".$pid;
                        mysql_query($sql);
                        break;
                    case 3:
                        $sql="update weixin_commonshop_products set bad_level=bad_level+1 where id=".$pid;
                        mysql_query($sql);
                        break;
                }
                if($supply_id[$i]>0){
                    $query2 = 'update weixin_commonshop_brand_supplys set comment_num=comment_num+1 where user_id='.$supply_id[$i].' and customer_id='.$customer_id;
                    mysql_query($query2) or die('query update supplys : '.mysql_error());
                }

                break;
            case 1:
                //插入追加评论
                if(!empty($destination_a)){
                    $sql="insert into weixin_commonshop_product_evaluations(user_id,product_id,level,isvalid,createtime,discuss,discussimg,type,batchcode,customer_id,is_anonymous) values(".$user_id.",".$pid.",$levels[$i],true,now(),'".$discuss[$i]."','".$destination_a."',2,".$batchcode.",".$customer_id.",".$is_anonymous.")";
                }else{
                    $sql="insert into weixin_commonshop_product_evaluations(user_id,product_id,level,isvalid,createtime,discuss,type,batchcode,customer_id,is_anonymous) values(".$user_id.",".$pid.",$levels[$i],true,now(),'".$discuss[$i]."',2,".$batchcode.",".$customer_id.",".$is_anonymous.")";
                }
                mysql_query($sql) or die('Query Insert Evaluations : ' . mysql_error());
                //修改订单评价状态
                $sql2="update weixin_commonshop_orders set is_discuss=2 where batchcode=".$batchcode." and pid=".$pid;
                mysql_query($sql2) or die('Query Update Orders : ' . mysql_error());
                //增加评价
                break;
        }

        //添加订单日志
        //$levelStr = "好评";
        switch($level[$i]){
            case 1:
                $levelStr = "好评";
                break;
            case 2:
                $levelStr = "中评";
                break;
            case 3:
                $levelStr = "差评";
                break;
        }
        $log_content = "用户评价订单：".$levelStr.",详情：".(empty($discuss[$i]) ? "无" : $discuss[$i]);
        $username = $_SESSION['fromuser_'.$customer_id];
        $query_logs = "insert into weixin_commonshop_order_logs(batchcode,operation,descript,operation_user,createtime,isvalid)
        values('".$batchcode."',17,'".$log_content."','".$username."',now(),1)";
        mysql_query($query_logs) or die("Query Logs failed : ".mysql_error());

    }
}
echo "<script>";
echo "history.go(-2);";
echo "</script>";
