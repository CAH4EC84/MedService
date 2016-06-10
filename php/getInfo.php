<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 21.01.2016
 * Time: 16:18
 * При открытии сайта JS вызывает данный модуль, последовательно передавая ему имена вкладок.
 * Модуль заполняет данными все необходимые вкладки.
 * Далее если пользователь желает уточнить данные то в данный скрипт передаются запрашиваемые данные.
 */
require_once '../conf/login.php';
$mssqlConn=sqlsrv_connect($serverName,$connectionInfo);
if( $mssqlConn === false ) die( print_r( sqlsrv_errors(), true));
//Получаем полную информацию для каждой вкладки.
$tab = $_POST['tab'];
$predicate = $_POST['queryId'];
//Функиции получения данных и отрисовки таблицы
//GetInfo($mssqlConn,$tab);


//Формируем заголовоки столбцов таблицы и строки для поиска
$thead=MakeTableHead(GetTableHeaders($mssqlConn,$tab),$tab);

//Содержимое таблицы
$tbody=GetTableData($mssqlConn,$tab,$predicate);

//Отрисовка таблицы
DrawTable($thead,$tbody);

function GetTableHeaders($conn,$table) {
    $queryFields="Select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME ='".$table."'";
    $resultFields=sqlsrv_query($conn,$queryFields) or die( print_r( sqlsrv_errors(), true));
    return $resultFields;
}

function MakeTableHead($th,$tid) {
    //Заголовоки столбцов
    $headersCount=0;
    $tableHead='<table class="table" border="1" id="'.$tid.'Table"> <thead><tr>';
        while ($row = sqlsrv_fetch_array($th,SQLSRV_FETCH_NUMERIC)) {
            $tableHead.='<td>'.($row[0]).'</td>';
            $headersCount++;
        };
    $tableHead.="</tr>";

    //Строка поиска
    $sRow="<tr>";
    for ($i=0;$i<$headersCount; $i++) {
        $sRow.= '<td class="inputFilter"><input type="text"></td>';
    }
    $tableHead.=$sRow."</tr></thead>";
    return $tableHead;
}

function GetTableData($conn,$table,$condition) {
    if ($condition) {
        $queryData="select * from ".$table." where [".$table."_id]=".$condition." order by name";
    } else{
        $queryData="select * from ". $table." order by 1";
    }
    $resultData=sqlsrv_query($conn,$queryData) or die( print_r( sqlsrv_errors(), true));
    $tableBody='';
    while ($row2=sqlsrv_fetch_array($resultData,SQLSRV_FETCH_NUMERIC)) {
        $tableBody.='<tr>';
        for ($j=0; $j<count($row2); $j++) {
            $tableBody.='<td>'.(mb_strtolower($row2[$j])).'</td>';
        }
        $tableBody.='</tr>';
    };
    $tableBody.='</tbody></table>';
    return $tableBody;
}

function DrawTable ($th,$tb) {
    echo $th.$tb;
}







