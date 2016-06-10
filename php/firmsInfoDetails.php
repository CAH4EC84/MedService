<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 01.03.2016
 * Time: 13:11
 */
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);  // ������������ � MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

//������ ���������
$nodesId=$_GET['nodes_id']; //�� ������������� �������� ���������� �� subGridModel->params[id]
$page = $_GET['page'];// �������� ����� ��������. ������� jqGrid ������ ��� � 1.
$limit = $_GET['rows']; // ������� ����� �� ����� ����� � ������� - rowNum ��������
$sidx = $_GET['sidx']; // ������� ��� ����������. ������� sortname �������� ����� index �� colModel
$sord = $_GET['sord']; // ������� ����������.
if(!$sidx) $sidx =1; // ���� ������� ���������� �� �������, �� �����  ����������� �� ������ �������.

//���������� ���������� ������� � �������
$query="select Count(*) as count from firmsInfoDetails WHERE nodes_id=".$nodesId;

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//���� �������� rowNum ���������� � all ($limit), ���������� ������� �������.
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



$query = "SELECT *
        FROM (
           SELECT DISTINCT id,id_old,parent_id,typename,subs_id,username,password, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM firmsInfodetails WHERE nodes_id=".$nodesId."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

// ������ ��� ��������� ������.
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