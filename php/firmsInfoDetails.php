<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 01.03.2016
 * Time: 13:11
 */
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);  // ѕодключаемс€ к MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

//читаем параметры
$nodesId=$_GET['nodes_id']; //»д запрашиваемой подписки передаетс€ из subGridModel->params[id]
$page = $_GET['page'];// ѕолучаем номер страницы. —начала jqGrid ставит его в 1.
$limit = $_GET['rows']; // сколько строк мы хотим иметь в таблице - rowNum параметр
$sidx = $_GET['sidx']; //  олонка дл€ сортировки. —начала sortname параметр затем index из colModel
$sord = $_GET['sord']; // ѕор€док сортировки.
if(!$sidx) $sidx =1; // ≈сли колонка сортировки не указана, то будем  сортировать по первой колонке.

//определ€ем количество записей в таблице
$query="select Count(*) as count from firmsInfoDetails WHERE nodes_id=".$nodesId;

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//если параметр rowNum установлен в all ($limit), возвращаем таблицу целиком.
if ($limit=='all') {
    $limit=$count;
}
// ¬ычисл€ем общее количество страниц.
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}
// ≈сли запрашиваемый номер страницы больше общего количества страниц,
// то устанавливаем номер страницы в максимальный.
if ($page > $total_pages) $page=$total_pages;

// ¬ычисл€ем начальное смещение строк.
$start = $limit*$page - $limit;

// ≈сли начальное смещение отрицательно,
// то устанавливаем его в 0.
// Ќапример, когда пользователь
// выбрал 0 в качестве запрашиваемой страницы.
if($start <0) $start = 0;



$query = "SELECT *
        FROM (
           SELECT DISTINCT id,id_old,parent_id,typename,subs_id,username,password, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM firmsInfodetails WHERE nodes_id=".$nodesId."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

// «апрос дл€ получени€ данных.
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

// «аголовок с указанием содержимого.
header("Content-type: text/xml;charset=utf-8");
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

// ќб€зательно передайте текстовые данные в CDATA
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