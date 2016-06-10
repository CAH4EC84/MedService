<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17.02.2016
 * Time: 13:34
 */
// —одержит информацию, необходимую дл€ подключени€ к базе данных
// MSSQL. ћы храним здесь логин, пароль, им€ базы.
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);

// ѕолучаем номер страницы. —начала jqGrid ставит его в 1.
$page = $_GET['page'];

// сколько строк мы хотим иметь в таблице - rowNum параметр
$limit = $_GET['rows'];

//  олонка дл€ сортировки. —начала sortname параметр
// затем index из colModel
$sidx = $_GET['sidx'];

// ѕор€док сортировки.
$sord = $_GET['sord'];

// ≈сли колонка сортировки не указана, то будем
// сортировать по первой колонке.
if(!$sidx) $sidx =1;

// ѕодключаемс€ к MSSQL
$mssqlConn=sqlsrv_connect($serverName,$connectionInfo);
if( $mssqlConn === false ) die( print_r( sqlsrv_errors(), true));

//ѕоисковый запрос
if ( isset($_GET['_search']) && ($_GET['_search']=='true')  ) {
    $qWhere = ''; //уточн€ющий запрос
    $allowedFields = array('nodes_id', 'name', 'parent', 'address1', 'region'); //разрещенные пол€ в запросе
    $allowedOperations = array('AND', 'OR'); //разрешенные логические операции

    //≈сли используетс€ панель поиска
    if (!isset($_GET['filters'])) {
        $qWhere = ' WHERE ';
        $params = array();
        $firstElem = true;

        foreach ($allowedFields as $searchField) { //перебираем все доступные колонки и соедин€ем их через условие поиска AND %(?)%
            if ( isset($_GET[$searchField])) {
                if ($firstElem) {
                    $qWhere.=$searchField.' LIKE (?)';
                    $params[] = '%' .$_GET[$searchField]. '%';
                    $firstElem = false;
                } else {
                    $qWhere.=' AND '.$searchField.' LIKE (?)';
                    $params[] = '%' .$_GET[$searchField]. '%';
                }

            }
        }
    }
}

// ¬ычисл€ем количество строк. Ёто необходимо дл€ постраничной навигации.
$query="select Count(*) as count from firmsInfo".$qWhere;
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//если параметр rowNum установлен в -1 ($limit), возвращаем таблицу целиком.
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

// «апрос дл€ получени€ данных.
$query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM firmsInfo".$qWhere."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

//echo $query ."<hr>";
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
//echo $row['NAME'].$row['parent'].$row['address1'].$row['region']."<br>";

    $s .= "<row>";
    $s .= "<cell>". $row['NODES_ID']."</cell>";
    $s .= "<cell><![CDATA[". $row['NAME']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['parent']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['ADDRESS1']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['region']."]]></cell>";
    $s .= "</row>";

}
$s .= "</rows>";
echo $s;
?>