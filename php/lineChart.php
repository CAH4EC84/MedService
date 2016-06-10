<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 16.03.2016
 * Time: 9:55

 */
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);

//Получаем  параметры даты запроса.
$fromDate=$_GET['from'];
$toDate=$_GET['to'];
$type=$_GET['type'];
$range=$_GET['range'];
$level=$_GET['level'];
$summFilter=$_GET['summFilter'];

$params = array();
$params[] = $fromDate;
$params[] = $toDate;


// Подключаемся к MSSQL
$mssqlConn=sqlsrv_connect($serverName,$connectionInfo);
if( $mssqlConn === false ) die( print_r( sqlsrv_errors(), true));

$query=$subQuery=$qSelect=$qHaving=$qJoin=$qGroup=$qOrder=$idsFilter='';

switch ($level)  {
    case 'Все заказы':
        $idsFilter='';
        break;
    case 'Регионы':
        if (isset($_GET['ids']) || $_GET['ids']!='') {
            foreach ($_GET['ids'] as $value) {
                $ids.= "ИдРегиона=".$value. "OR ";
            }
            if ($ids) {$idsFilter=" and (".mb_substr($ids,0,-3)." )";} else {$idsFilter='';}
        }
        break;
    case 'Апт. Сети':
        if (isset($_GET['ids']) || $_GET['ids']!='') {
            foreach ($_GET['ids'] as $value) {
                $ids.= "ИдАптечнойСети=".$value. "OR ";
            }
            if ($ids) {$idsFilter=" and (".mb_substr($ids,0,-3)." )";} else {$idsFilter='';}
        }
        break;
    case 'Аптеки':
        if (isset($_GET['ids']) || $_GET['ids']!='') {
            foreach ($_GET['ids'] as $value) {
                $ids.= "ИдАптеки=".$value. "OR ";
            }
            if ($ids) {$idsFilter=" and (".mb_substr($ids,0,-3)." )";} else {$idsFilter='';}
        }
        break;
    case 'Поставщики':
        if (isset($_GET['ids']) || $_GET['ids']!='') {
            foreach ($_GET['ids'] as $value) {
                $ids.= "ИдПоставщика=".$value. "OR ";
            }
            if ($ids) {$idsFilter=" and (".mb_substr($ids,0,-3)." )";} else {$idsFilter='';}
        }
        break;
    default:
        throw new Exception('error rules level filter!)');
};

//Формируем подзапрос для выборки указанного типа данных
switch ($type) {
    case 'Заказ':
       $subQuery = " from (Select DISTINCT Дата as DATA ,НомерЗаказа,Сумма,ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика
        from FullData
	    where Дата between (?) and (?) and ИдТипаДокумента=8 ".$idsFilter.") as tmp ";
        break;
    case 'Отказ':
        $subQuery = " from (Select DISTINCT Дата as DATA ,НомерЗаказа,Сумма,ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика
         from FullData
	     where Дата between (?) and (?) and ИдТипаДокумента=10 ".$idsFilter.") as tmp ";
        break;
    case 'Конкурс':
        $subQuery = " from (Select DISTINCT Дата as DATA ,НомерЗаказа,Сумма,ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика
        from FullData
	    where Дата between (?) and (?) and ИдТипаДокумента=12 ".$idsFilter.") as tmp ";
        break;
}

//Определяем диапозон
switch($range) {
    case 'День':
        $qSelect='Select DATA,';
        $qGroup=' group by DATA';
        break;
    case 'Месяц':
        $qSelect="Select ( Cast(YEAR(DATA)as nvarchar) + '-' + RIGHT('0' + RTRIM(MONTH(DATA)), 2) )as DATA,";
        $qGroup=" group by ( Cast(YEAR(DATA)as nvarchar) + '-' + RIGHT('0' + RTRIM(MONTH(DATA)), 2) )";
        break;
    case 'Год':
        $qSelect='Select ( Cast(YEAR(DATA)as nvarchar) )as DATA,';
        $qGroup=' group by ( Cast(YEAR(DATA)as nvarchar) )';
        break;
    default:
        throw new Exception('error rules range filter!');
};

//Определяем уровень детализации данных

switch ($level)  {
    case 'Все заказы':
        $qOrder=" order by DATA";
        break;
    case 'Регионы':
        $qSelect.='R.NAME as NAME,';
        $qJoin='Left Join medline39.dbo.REGIONS as R on R.ID=ИдРегиона';
        $qGroup.=',NAME';
        $qOrder=' order by NAME,DATA';
        break;
    case 'Апт. Сети':
        $qSelect.='F.NAME as NAME,';
        $qJoin='Left Join medline39.dbo.FIRMS as F on F.ID=ИдАптечнойСети';
        $qGroup.=',NAME';
        $qOrder=' order by NAME,DATA';
        break;
    case 'Аптеки':
        $qSelect.='F.NAME as NAME,';
        $qJoin='Left Join medline39.dbo.FIRMS as F on F.ID=ИдАптеки	';
        $qGroup.=',NAME';
        $qOrder=' order by NAME,DATA';
        break;
    case 'Поставщики':
        $qSelect.='F.NAME as NAME,';
        $qJoin='Left Join medline39.dbo.FIRMS as F on F.ID=ИдПоставщика	';
        $qGroup.=',NAME';
        $qOrder=' order by NAME,DATA';
        break;
    default:
        throw new Exception('error rules level filter!)');
};
if ($summFilter) {
    $summFilter=str_replace(',','',$summFilter);
    $qHaving=" having SUM(Сумма) $summFilter ";
}

$query=$qSelect.'SUM(Сумма) as Summ '.$subQuery.$qJoin.$qGroup.$qHaving.$qOrder;

//echo $query ."<hr>";

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

$tmp=array();
while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
    if ($row['NAME']) {
        $tmp[$row['NAME']][$row['DATA']] = round($row['Summ'],0);
    } else {
        $tmp[$row['DATA']]=round($row['Summ'],0);
    }

}

$data=json_encode($tmp);
echo $data;

?>