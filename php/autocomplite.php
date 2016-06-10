<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 25.02.2016
 * Time: 14:29
 */
// MSSQL. Мы храним здесь логин, пароль, имя базы.
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);  // Подключаемся к MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

echo ( findAutocomplete($conn,$_GET['table'],$_GET['field'],$_GET['nameStartWith']) );

function findAutocomplete($conn,$table,$field,$term) {
    $query = "SELECT DISTINCT ".$field." FROM ".$table." WHERE ".$field." LIKE (?)";
     $params[] = '%' .$term. '%';
    $result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

    // Заголовок с указанием содержимого.
    header("Content-type: text/xml;charset=utf-8");
    $s = "<?xml version='1.0' encoding='utf-8'?>";
    $s .=  "<rows>";
// Обязательно передайте текстовые данные в CDATA
        while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
            $s .= "<row>";
            $s .= "<cell><![CDATA[".str_replace('\\',';',$row[$field])."]]></cell>";
            $s .= "</row>";
        }
        $s .= "</rows>";
    return $s;
    }



