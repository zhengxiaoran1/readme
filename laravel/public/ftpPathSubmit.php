<?php
$path = $_REQUEST['path'];
$pathArr = explode('/',$path);


//如果目录开始不是api开头，进行过滤
$firstPather = array_shift($pathArr);
while($firstPather != 'api' && $firstPather){
    $firstPather = array_shift($pathArr);
}

if($firstPather){
    array_unshift($pathArr,$firstPather);
}





foreach($pathArr as $key => $pathStr){


    if(!$pathStr){
        unset($pathArr[$key]);
    }
    if(strstr($pathStr,'.php')){
        unset($pathArr[$key]);
    }
}

////拼接字符串
//本地地址
$localPath = "D:\UPUPW_NP7.0\htdocs\yiguantong_product_new";
foreach($pathArr as $pathStr){
    $localPath.='\\'.$pathStr;
}

//服务器地址1
$serverPath1 = "/data/wwwroot/118.178.24.119";
$serverPath2 = "/data/wwwroot/eguantong.bxuping.com";
$serverPath3 = "/data/wwwroot/egt.100dp.com";

foreach($pathArr as $pathStr){
    $serverPath1.='/'.$pathStr;
    $serverPath2.='/'.$pathStr;
    $serverPath3.='/'.$pathStr;
}

echo $localPath;
?>
<!DOCTYPE html>
<html>
<title>ftp目录地址</title>
<body>

<br>
<br>
<br>
<br>
<br>
<?php 
echo $serverPath1;
?>
<br>
<br>
<br>
<br>
<br>
<br>
<?php
echo $serverPath2;
?>
<br>
<br>
<br>
<br>
<br>
<br>
<?php
echo $serverPath3;
?>

</body>
</html>




