<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 06.06.2016
 * Time: 13:18
 */
require_once '../../conf/login.php';
$conn=sqlsrv_connect($serverName, $connectionInfo) or die (print_r(sqlsrv_errors(),true));

//Id интересующего отчета
$repId= $_GET['repId'];
$reportSettings=[]; //Информация о требуемых полях и фильтрах на них.
//определяем тип отчета и соответствующий ему набор полей.
$query = "Select RepType as RepType from GeneratedReports where ID=$repId";
$result = sqlsrv_query($conn,$query) or die (print_r(sqlsrv_errors(),true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$reptype=trim($row['RepType']);
switch ($reptype) {
    case 'Отчет по заказам':
        $reportSettings['type']=$reptype;
        $fieldsList=['Дата',
            'Регион.name',
            'Аптечная_сеть.name',
            'Аптека.name',
            'Поставщик.name',
            'SUM(Сумма)'
        ];
        $cname=['CreateDate','Region','Network','Apteka','Diler','Paysumm'];
        break;
    case 'Отчет по продукции':
        $reportSettings['type']=$reptype;
        $fieldsList=['Дата',
            'Регион.name',
            'Аптечная_сеть.name',
            'Аптека.name',
            'Поставщик.name',
            'Препарат',
            'Производитель',
            'Препарат+ \' \'Производитель',
            'SUM(Сумма)',
            'Цена',
            'Sum(Количество)',
            'SUM(Обращаемость)'
        ];
        $cname=['CreateDate','Region','Network','Apteka','Diler','Production','Producer','ProductionProducer','Paysumm','Price','Count','Rotate'];
        break;
    case 3:
        break;
    default:

}


//Получаем данные о отчете
$query = "Select * from GeneratedReports where ID=$repId";
$result = sqlsrv_query($conn,$query) or die (print_r(sqlsrv_errors(),true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);




foreach ($cname as $key=>$value) {

    if ($row[$value]==1) {
        if ($row[$value.'Filter']) {
            $reportSettings[trim($key)] = trim($row[$value.'Filter']);
        } else {$reportSettings[trim($key)] = '';}
    };

}
echo json_encode($reportSettings);
