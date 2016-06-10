<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 24.02.2016
 * Time: 14:38
 */
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);  // Подключаемся к MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

//читаем параметры
$subsId=$_GET['id']; //Ид запрашиваемой подписки передается из subGridModel->params[id]
$page = $_GET['page'];// Получаем номер страницы. Сначала jqGrid ставит его в 1.
$limit = $_GET['rows']; // сколько строк мы хотим иметь в таблице - rowNum параметр
$sidx = $_GET['sidx']; // Колонка для сортировки. Сначала sortname параметр затем index из colModel
$sord = $_GET['sord']; // Порядок сортировки.
if(!$sidx) $sidx =1; // Если колонка сортировки не указана, то будем  сортировать по первой колонке.

//определяем количество записей в таблице
$query="select Count(*) as count from subsInfoDetails WHERE subs_id=".$subsId;
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//если параметр rowNum установлен в all ($limit), возвращаем таблицу целиком.
if ($limit=='all') {
    $limit=$count;
}
// Вычисляем общее количество страниц.
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}
// Если запрашиваемый номер страницы больше общего количества страниц,
// то устанавливаем номер страницы в максимальный.
if ($page > $total_pages) $page=$total_pages;

// Вычисляем начальное смещение строк.
$start = $limit*$page - $limit;

// Если начальное смещение отрицательно,
// то устанавливаем его в 0.
// Например, когда пользователь
// выбрал 0 в качестве запрашиваемой страницы.
if($start <0) $start = 0;



$query = "SELECT *
        FROM (
           SELECT node_firm,name,doc_type,base_file,base_timeOf,read_timeOf,error_text,actual_days, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfodetails WHERE subs_id=".$subsId."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

//$query = "SELECT node_firm,name,doc_type,base_file,base_timeOf,error_text,actual_days FROM subsInfoDetails WHERE subs_id=".$subsId." order by name";

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
    $s .= "<cell>". $row['node_firm']."</cell>";
    $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
    $s .= "<cell>". $row['doc_type']."</cell>";
    $s .= "<cell><![CDATA[". $row['base_file']."]]></cell>";
    $s .= "<cell>". $row['base_timeOf']."</cell>";
    $s .= "<cell>". $row['read_timeOf']."</cell>";
    $s .= "<cell><![CDATA[". $row['error_text']."]]></cell>";
    $s .= "<cell>". $row['actual_days']."</cell>";
    $s .= "</row>";
}
$s .= "</rows>";
echo $s;