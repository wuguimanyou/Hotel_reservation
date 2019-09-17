<?php 
class Product_Type_Utlity{
	//下架的商品属性 编号
	private $count_type=array();
	private $up=array();
	//寻找下级
	public function search_off_shelves($parent_id){
		//检查是否已经存在
		if(in_array($parent_id,$this->count_type)){
			return false;
			break;
		}
		//放入数组
		array_push($this->count_type,$parent_id);
		//file_put_contents ( "11111111.txt", "=".var_export ( $this->count_type, true ) ."\r\n", FILE_APPEND); 	
		//寻找下级
		$query = "SELECT id FROM weixin_commonshop_types where isvalid=true and parent_id=".$parent_id;
			$result = mysql_query($query) or die('Query failed========: ' . mysql_error());  			
			$id=-1;
			while ($row = mysql_fetch_object($result)) {
				$id =  $row->id;
				$this->search_off_shelves($id);
			}
		return 	$this->count_type;
	}

	public function search_up($id,$parent_id){
		array_push($this->up,$id);
		$re = $this->search_up_parent($parent_id); 
		return $re;
	}	
	
	public function search_up_parent($id){  
		//检查是否已经存在
		if(in_array($id,$this->up)){      
			//file_put_contents ( "11111111.txt", "--------".var_export ( $this->up, true ) ."\r\n", FILE_APPEND); 	
			return -1;
		}else if($id>0){ 		 //当上级不是顶级(-1) 继续寻找上级
		//放入数组
			array_push($this->up,$id);			
			//寻找上级
			$query = "SELECT parent_id FROM weixin_commonshop_types where isvalid=true and id=".$id;
				$result = mysql_query($query) or die('Query failed2========: ' . mysql_error());  			
				$id=-1;
				while ($row = mysql_fetch_object($result)) {
					$id =  $row->parent_id;
					return $this->search_up_parent($id);      //递归寻找上一级
				} 
		
		}else{
			return 	count($this->up);
		}
	}	
	
}

?>