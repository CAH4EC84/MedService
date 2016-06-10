<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 01.04.2016
 * Time: 10:32
 */
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);

//Получаем  параметры даты запроса.
$fromDate=$_GET['from']; // с
$toDate=$_GET['to']; // по
$type=$_GET['type']; // по продукции или по поставщикам
$range=$_GET['range']; // сумма, количество или обращаемость
$group=$_GET['group']; // с или без учета производителя
$summFilter=$_GET['summFilter'];
$productFilter=$_GET['productFilter'];
$producerFilter=$_GET['producerFilter'];


$params = array();
$params[] = $fromDate;
$params[] = $toDate;

// Подключаемся к MSSQL
$mssqlConn=sqlsrv_connect($serverName,$connectionInfo);
if( $mssqlConn === false ) die( print_r( sqlsrv_errors(), true));

$query=$subQuery=$qSelect=$qHaving=$qJoin=$qGroup=$qOrder=$idsFilter='';


//Проверяем наличие фильтров по продукции и производителю.
if ($productFilter) {
   $productFilter = "and  Препарат like '".$productFilter."' ";
}
if ($producerFilter) {
    $producerFilter = "and  Производитель like '".$producerFilter."' ";
}


//Формируем подзапрос для выборки указанного типа данных
switch ($type) {
    case 'Топ препаратов':
        $subQuery=" from (Select Препарат,Производитель,Цена,Количество, Цена*Количество as Сумма , 1 as обращаемость,
        ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика
        from FullData
	    where Дата between (?) and (?) and ИдТипаДокумента=8 ".$productFilter . $producerFilter.") as tmp";
        break;
    case 'Топ продавцов':
        $subQuery ="";
        break;
    default:
        throw new Exception('error rules type filter!');

}


//Определяем диапозон
switch($range) {
    case 'Сумма':
        $qSelect='Select TOP 15 Sum(Сумма) as Summ,';
        $qOrder=" order by Summ DESC";
        break;
    case 'Количество':
        $qSelect="Select TOP 15 Sum(Количество) as Summ,";
        $qOrder=" order by Summ DESC";
        break;
    case 'Обращаемость':
        $qSelect.='Select TOP 15 SUM(обращаемость) as Summ,';
        $qOrder=" order by Summ DESC";
        break;
    default:
        throw new Exception('error rules range filter!');
};

//Определяем уровень детализации данных

switch ($group)  {
    case 'Продукция':
        $qSelect.="Препарат as NAME";
        $qGroup=" group by Препарат";
        break;
    case 'Продукция + Производитель':
        $qSelect.="Препарат +' ; '+ Производитель as NAME";
        $qGroup=" group by Препарат +' ; '+ Производитель";
        break;
    default:
        throw new Exception('error rules level filter!)');
};

if ($summFilter) {
    $summFilter=str_replace(',','',$summFilter);
    switch($range) {
        case 'Сумма':
            $qHaving=" having Sum(Сумма) $summFilter ";
            break;
        case 'Количество':
            $qHaving=" having Sum(Количество) $summFilter ";
            break;
        case 'Обращаемость':
            $qHaving=" having Sum(обращаемость) $summFilter ";
            break;
        default:
            throw new Exception('error rules range filter!');
    };
}

$query=$qSelect.$subQuery.$qGroup.$qHaving.$qOrder;
//echo $query ."<hr>";

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$tmp=array();
while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
        $tmp[ $row['NAME'] ] = round($row['Summ'], 0);
}

$data=json_encode($tmp);
echo $data;

?>