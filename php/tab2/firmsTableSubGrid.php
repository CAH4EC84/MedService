<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 27.04.2016
 * Time: 13:29
 */
require_once '../../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo) or die ( print_r( sqlsrv_errors(),true) );

$nodesId=$_GET['nodes_id'];
$page = $_GET['page'];
$limit = $_GET['rows'];
$sidx = $_GET['sidx'];
$sord = $_GET['sord'];

//определяем количество записей в таблице
$query="select Count(*) as count from firmsInfoDetails WHERE nodes_id=".$nodesId;

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$limit = $row['count'];

$query = "SELECT *
        FROM (
           SELECT DISTINCT id,id_old,parent_id,typename,subs_id,username,password, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM firmsInfodetails WHERE nodes_id=".$nodesId."
        ) AS y
        WHERE y.x BETWEEN 1 AND ".$limit." ORDER BY y.x, $sidx $sord;";

// Запрос для получения данных.
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

// Заголовок с указанием содержимого.
header("Content-type: text/xml;charset=utf-8");
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

// Обязательно передайте текстовые данные в CDATA
while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
    $s .=  "<row>";
    $s .= "<cell>". $row['id']."</cell>";
    $s .= "<cell>". $row['id_old']."</cell>";
    $s .= "<cell>". $row['parent_id']."</cell>";
    $s .= "<cell><![CDATA[". $row['typename']."]]></cell>";
    $s .= "<cell>". $row['subs_id']."</cell>";
    $s .= "<cell><![CDATA[". $row['username']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['password']."]]></cell>";
    $s .= "</row>";
}
$s .= "</rows>";
echo $s;


?>