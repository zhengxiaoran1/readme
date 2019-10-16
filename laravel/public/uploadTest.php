<!DOCTYPE html>
<html>
<title>图片上传测试</title>
<body>
<form action='api/service/upload' method='post' enctype="multipart/form-data">
    <input type='file' value ='' name ='file_name'>
    <input type='submit' value ='提交'>
</form>


</body>
</html>




<?php
function dd($arr){
    echo "<pre>";
    print_r($arr);
    echo "<pre>";
    die();
}



