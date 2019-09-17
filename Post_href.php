<?php 
header("Content-type: text/html; charset=utf-8"); 

/*
data 		:post数据,数组数据不带自定义键名，默认数字键名
url 		:跳转的链接
*/
function Post_href($data,$url){

	$data = json_encode($data);//数组转json否则无法编译

	echo "<script>Post_data(".$data.",'".$url."');</script>";
}

//获取POST跳转数据,并返回数组
function Get_post_data(){

	$Post_data = $_POST['post_array'];
	$Post_data_arr = explode(',',$Post_data);
	var_dump($Post_data_arr);
	
	return $Post_data_arr;
	
}


?>

<!--创建html-->
<html>
<body>
</body>
</html>
<!--创建html-->


<script>

//模拟POST提交方法
function Post_data(data,url){

  
	/* 将GET方法改为POST ----start---*/
		
    var objform = document.createElement('form');
	document.body.appendChild(objform);
	
	var obj_p = document.createElement("input");
	obj_p.type = "hidden";
	objform.appendChild(obj_p);
	obj_p.value = data;
	obj_p.name = "post_array";

	objform.action = url;
	objform.method = "POST"
	objform.submit();
	
	/* 将GET方法改为POST ----end---*/	
}

</script>