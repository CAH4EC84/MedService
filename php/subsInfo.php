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
$conn=sqlsrv_connect($serverName,$connectionInfo);  // ������������ � MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

//������ ���������
$page = $_GET['page'];// �������� ����� ��������. ������� jqGrid ������ ��� � 1.
$limit = $_GET['rows']; // ������� ����� �� ����� ����� � ������� - rowNum ��������
$sidx = $_GET['sidx']; // ������� ��� ����������. ������� sortname �������� ����� index �� colModel
$sord = $_GET['sord']; // ������� ����������.
if(!$sidx) $sidx =1; // ���� ������� ���������� �� �������, �� �����  ����������� �� ������ �������.

//���������� ������� (����� ��� ������ ������ �� ����� ������) ���� �����, ������������ WHERE ����� �������
if ( isset($_GET['_search']) && $_GET['_search']=='true') {
    $qWhere = ''; //���������� ������
    $allowedFields = array('id', 'name', 'timeOf', 'is_activee'); //����������� ���� � �������
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

    //���� ������������ ����������� ����� � jqGrid
    if (isset($_GET['filters'])) {
        $searchData = json_decode($_GET['filters']); //������������ ������� ������������ � JSON �����
        //����������� �� ���������� �������
        if (count($searchData->rules) > 10) { //����������� �� ���������� �������.
            throw new Exception('Error JSON parametrs');
        }

        $qWhere = ' WHERE ';
        $params = array();
        $firstElem = true;

        //���������� ��� ���������� �������
        foreach ($searchData->rules as $rule) {
            if (!$firstElem) { //������ ������� ���� ��� ���������� ������� � ��������� ����������
                //������������ ������� AND ��� OR
                if (in_array($searchData->groupOp, $allowedOperations)) {
                    $qWhere .= ' ' . $searchData->groupOp . ' ';
                } else {
                    throw new Exception ('Error group params');
                }
            } else {
                $firstElem = false;
            }

            //���� ���� ������ � ������ ����������
            if (in_array($rule->field, $allowedFields)) {
                switch ($rule->op) {
                    case 'eq':
                        $qWhere .= $rule->field . '=' . '(?)';
                        $params[] = $rule->data;
                        break;
                    case 'ne':
                        $qWhere .= $rule->field . '<>' . '(?)';
                        $params[] = $rule->data;
                        break;
                    case 'bw':
                        $qWhere .= $rule->field . ' LIKE (?)';
                        $params[] = $rule->data . '%';
                        break;
                    case 'cn':
                        $qWhere .= $rule->field . ' LIKE (?)';
                        $params[] = '%' . $rule->data . '%';
                        break;
                    default:
                        throw new Exception('error rules filter!!! :)');
                }
            } else {
                throw new Exception ('error filter fields');
            }
        }
    }
}



//���������� ���������� ������� � �������
$query="select Count(*) as count from subsInfo".$qWhere;
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

$query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo".$qWhere."
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
    $s .= "<row id='". $row['id']."'>";
    $s .= "<cell>". $row['id']."</cell>";
    $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
    $s .= "<cell>". $row['timeOf']."</cell>";
    $s .= "<cell>". $row['is_activee']."</cell>";
    $s .= "</row>";
}
$s .= "</rows>";
echo $s;

//������ ����������
function subsInfoFull($conn) {
    //������ ���������
    $page = $_GET['page'];// �������� ����� ��������. ������� jqGrid ������ ��� � 1.
    $limit = $_GET['rows']; // ������� ����� �� ����� ����� � ������� - rowNum ��������
    $sidx = $_GET['sidx']; // ������� ��� ����������. ������� sortname �������� ����� index �� colModel
    $sord = $_GET['sord']; // ������� ����������.
    if(!$sidx) $sidx =1; // ���� ������� ���������� �� �������, �� �����  ����������� �� ������ �������.

// ��������� ���������� �����. ��� ���������� ��� ������������ ���������.
    $query="select Count(*) as count from subsInfo";
    $result=sqlsrv_query($conn,$query) or die( print_r( sqlsrv_errors(), true));
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

    $query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";
// ������ ��� ��������� ������.
//echo $query.'<hr>';
    $result=sqlsrv_query($conn,$query) or die( print_r( sqlsrv_errors(), true));

// ��������� � ��������� �����������.
    header("Content-type: text/xml;charset=utf-8");

    $s = "<?xml version='1.0' encoding='utf-8'?>";
    $s .=  "<rows>";
    $s .= "<page>".$page."</page>";
    $s .= "<total>".$total_pages."</total>";
    $s .= "<records>".$count."</records>";

// ����������� ��������� ��������� ������ � CDATA
    while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
        $s .= "<row id='". $row['id']."'>";
        $s .= "<cell>". $row['id']."</cell>";
        $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
        $s .= "<cell>". $row['timeOf']."</cell>";
        $s .= "<cell>". $row['is_activee']."</cell>";
        $s .= "</row>";
    }
    $s .= "</rows>";

    return $s;

}















//������� ��������� ������������� ������
function subsInfoFilter($conn) {
    //������ ���������
    $page = $_GET['page'];// �������� ����� ��������. ������� jqGrid ������ ��� � 1.
    $limit = $_GET['rows']; // ������� ����� �� ����� ����� � ������� - rowNum ��������
    $sidx = $_GET['sidx']; // ������� ��� ����������. ������� sortname �������� ����� index �� colModel
    $sord = $_GET['sord']; // ������� ����������.
    if(!$sidx) $sidx =1; // ���� ������� ���������� �� �������, �� �����  ����������� �� ������ �������.

    $qWhere = ''; //���������� ������

    $allowedFields=array('id','name','timeOf','is_activee'); //����������� ���� � �������
    $allowedOperations=array('AND','OR'); //����������� ���������� ��������

    $searchData=json_decode($_GET['filters']); //������������ ������� ������������ � JSON �����
    //����������� �� ���������� �������
    if ( count($searchData->rules)>5 ) {
        throw new Exception('Error JSON parametrs');
    }

    $qWhere=' WHERE ';
    $params=array();
    $firstElem=true;

    //���������� ��� ���������� �������
    foreach ($searchData->rules as $rule) {
        if (!$firstElem) { //������ ������� ���� ��� ���������� ������� � ��������� ����������
            //������������ ������� AND ��� OR
            if (in_array($searchData->groupOp,$allowedOperations)) {
                $qWhere.=' '.$searchData->groupOp.' ';
            } else {
                throw new Exception ('Error group params');
            }
        } else {
            $firstElem=false;
        }

        //���� ���� ������ � ������ ����������
        if (in_array($rule->field, $allowedFields)) {
            switch ($rule->op) {
                case 'eq': $qWhere.= $rule->field.'='.'(?)'; $params[]=$rule->data; break;
                case 'ne': $qWhere.= $rule->field.'<>'.'(?)'; $params[]=$rule->data; break;
                case 'bw': $qWhere.= $rule->field.'LIKE'.'(?)'; $params[]=$rule->data; break;
                case 'cn': $qWhere.= $rule->field.'LIKE'.'%(?)%';$params[]=$rule->data; break;
                default: throw new Exception('error rules filter!!! :)');
            }
        } else {
            throw new Exception ('error filter fields');
        }
    }

    //���������� ���������� ������� � �������
    $query="select Count(*) as count from subsInfo".$qWhere;
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

    $query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo $qWhere
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";
// ������ ��� ��������� ������.
//echo $query.'<hr>';
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
        $s .= "<row id='". $row['id']."'>";
        $s .= "<cell>". $row['id']."</cell>";
        $s .= "<cell><![CDATA[". $row['name']."]]></cell>";
        $s .= "<cell>". $row['timeOf']."</cell>";
        $s .= "<cell>". $row['is_activee']."</cell>";
        $s .= "</row>";
    }
    $s .= "</rows>";

    return $s;
}
?>