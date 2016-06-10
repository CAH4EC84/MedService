<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17.02.2016
 * Time: 13:34
 */
// �������� ����������, ����������� ��� ����������� � ���� ������
// MSSQL. �� ������ ����� �����, ������, ��� ����.
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);

// �������� ����� ��������. ������� jqGrid ������ ��� � 1.
$page = $_GET['page'];

// ������� ����� �� ����� ����� � ������� - rowNum ��������
$limit = $_GET['rows'];

// ������� ��� ����������. ������� sortname ��������
// ����� index �� colModel
$sidx = $_GET['sidx'];

// ������� ����������.
$sord = $_GET['sord'];

// ���� ������� ���������� �� �������, �� �����
// ����������� �� ������ �������.
if(!$sidx) $sidx =1;

// ������������ � MSSQL
$mssqlConn=sqlsrv_connect($serverName,$connectionInfo);
if( $mssqlConn === false ) die( print_r( sqlsrv_errors(), true));

//��������� ������
if ( isset($_GET['_search']) && ($_GET['_search']=='true')  ) {
    $qWhere = ''; //���������� ������
    $allowedFields = array('nodes_id', 'name', 'parent', 'address1', 'region'); //����������� ���� � �������
    $allowedOperations = array('AND', 'OR'); //����������� ���������� ��������

    //���� ������������ ������ ������
    if (!isset($_GET['filters'])) {
        $qWhere = ' WHERE ';
        $params = array();
        $firstElem = true;

        foreach ($allowedFields as $searchField) { //���������� ��� ��������� ������� � ��������� �� ����� ������� ������ AND %(?)%
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

// ��������� ���������� �����. ��� ���������� ��� ������������ ���������.
$query="select Count(*) as count from firmsInfo".$qWhere;
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//���� �������� rowNum ���������� � -1 ($limit), ���������� ������� �������.
if ($limit=='all') {
    $limit=$count;
}

// ��������� ����� ���������� �������.
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}

// ���� ������������� ����� �������� ������ ������ ���������� �������,
// �� ������������� ����� �������� � ������������.
if ($page > $total_pages) $page=$total_pages;

// ��������� ��������� �������� �����.
$start = $limit*$page - $limit;

// ���� ��������� �������� ������������,
// �� ������������� ��� � 0.
// ��������, ����� ������������
// ������ 0 � �������� ������������� ��������.
if($start <0) $start = 0;

// ������ ��� ��������� ������.
$query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM firmsInfo".$qWhere."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

//echo $query ."<hr>";
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

// ��������� � ��������� �����������.

header("Content-type: text/xml;charset=utf-8");
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

// ����������� ��������� ��������� ������ � CDATA
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