<?php
header("Content-type: text/html; charset=utf-8");    
?>
<div class="am-share" style="top:100px;">
    <div class="box">
        <h1>输入支付密码</h1>
        <label for="ipt">
            <ul>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </label>
        <input type="tel" id="ipt" maxlength="6">
        <div style="width:100%;text-align: right;;"> <a onclick='modify_passworf();'>密码管理</a></div>
        <a class="commtBtn" href="javascript:void(0);">确认</a>
    </div>
</div>
<script type="text/javascript">
$('input').on('input', function (e){
    var numLen = 6;
    var pw = $('input').val();
    var list = $('li');
    for(var i=0; i<numLen; i++){
        if(pw[i]){
            $(list[i]).text('·');
        }else{
            $(list[i]).text('');
        }
    }
});
function modify_passworf(){
    window.location.href = "modify_password.php?customer_id=<?php echo $customer_id_en;?>";
}
</script>