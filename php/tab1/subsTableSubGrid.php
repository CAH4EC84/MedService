<?php
require_once '../../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);
if( $conn === false ) die( print_r( sqlsrv_errors(), true));


$subsId=$_GET['id'];
$page = $_GET['page'];
$limit = $_GET['rows'];
$sidx = $_GET['sidx'];
$sord = $_GET['sord'];



$query="select Count(*) as count from subsInfoDetails WHERE subs_id=".$subsId;

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$limit = $row['count'];

$query = "SELECT *
        FROM (
           SELECT node_firm,name,doc_type,base_file,base_timeOf,read_timeOf,error_text,actual_days, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfodetails WHERE subs_id=".$subsId."
        ) AS y
        WHERE y.x BETWEEN 1 AND ".$limit." ORDER BY y.x, $sidx $sord;";
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

header("Content-type: text/xml;charset=utf-8");
$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";


while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
    $s .=  "<row>";
    $s .= "<cell>". $row['node_firm']."</cell>";
    $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
    $s .= "<cell><![CDATA[". $row['base_file']."]]></cell>";
    $s .= "<cell>". $row['doc_type']."</cell>";
    $s .= "<cell>". $row['base_timeOf']."</cell>";
    $s .= "<cell>". $row['read_timeOf']."</cell>";
    $s .= "<cell><![CDATA[". $row['error_text']."]]></cell>";
    $s .= "<cell>". $row['actual_days']."</cell>";
    $s .= "</row>";
}
$s .= "</rows>";
echo $s;