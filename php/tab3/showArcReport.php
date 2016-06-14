<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 27.05.2016
 * Time: 14:14
 */
require_once '../../conf/login.php';
$conn=sqlsrv_connect($serverName, $connectionInfo) or die (print_r(sqlsrv_errors(),true));

//Параметры входящего запроса от jqGrid.
$page = $_GET['page']; //Номер запрашиваемой страницы
$limit = $_GET['rows']; //Количество запрашиваемых строк
$sidx = $_GET['sidx']; //Колонка для сортировки
$sord = $_GET['sord']; //Порядок сортировки

//Определяем команду полная выборка или поиск.
if ( isset($_GET['_search']) && $_GET['_search']=='true' ) { //Если поиск то формируем уточняющие условие для запроса
    $params = array();
    $firstElem = true;
    $qWhere=' WHERE '; //Уточняющий запрос
    $allowedFields = array ('id','GenerationDate','FileName','Type','DateRange','rowsCount','comment'); //Разрешенные поля в запросе
    foreach ($allowedFields as $searchField) { //Перебираем все доступные колонки и соединяем их черех условие поиска
        if ( isset($_GET[$searchField]) ) {
            if ($firstElem) {
                $qWhere.=$searchField.' LIKE (?)';
                $params[]='%'.$_GET[$searchField].'%';
                $firstElem = false;
            } else {
                $qWhere.=' AND '.$searchField.' LIKE (?)';
                $params[]='%'.$_GET[$searchField].'%';
            }
        }
    }
}

//Общее количество выводимых записей
$query='SELECT COUNT(*) as count from GeneratedReports'.$qWhere;
$result = sqlsrv_query($conn,$query,$params) or die (print_r(sqlsrv_errors(),true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count=$row['count'];


if ($limit=='all') {
    $limit=$count;
}

if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}
// Если запрашиваемый номер страницы больше общего количества страниц, то устанавливаем номер страницы в максимальный.
if ($page > $total_pages) $page=$total_pages;
// Вычисляем начальное смещение строк.
$start = $limit*$page - $limit;
// Если начальное смещение отрицательно, то устанавливаем его в 0.
if($start <0) $start = 0;


//Получение данных.

$query = "SELECT *
        FROM (
           SELECT id,GenerationDate,FileName,DateRange,RepType,rowsCount,comment as comment, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM GeneratedReports".$qWhere."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";


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
    $s .= "<row id='". $row['id']."'>";
    $s .= "<cell>". $row['id']."</cell>";
    $s .= "<cell><![CDATA[". $row['RepType']."]]></cell>";
    $s .= "<cell>". $row['GenerationDate']."</cell>";
    $s .= "<cell>". $row['DateRange']."</cell>";
    ($row['rowsCount']==0) ? $s .="<cell><![CDATA[No rows]]></cell>" : $s .= "<cell><![CDATA[".$row['FileName']."]]></cell>";
    $s .= "<cell>".$row['rowsCount']."</cell>";
    $s .= "<cell><![CDATA[".$row['comment']."]]></cell>";
    $s .= "</row>";
}
$s .= "</rows>";
echo $s;