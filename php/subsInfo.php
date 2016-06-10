<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17.02.2016
 * Time: 13:34
 */
// Содержит информацию, необходимую для подключения к базе данных
// MSSQL. Мы храним здесь логин, пароль, имя базы.
require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);  // Подключаемся к MSSQL
if( $conn === false ) die( print_r( sqlsrv_errors(), true));

//читаем параметры
$page = $_GET['page'];// Получаем номер страницы. Сначала jqGrid ставит его в 1.
$limit = $_GET['rows']; // сколько строк мы хотим иметь в таблице - rowNum параметр
$sidx = $_GET['sidx']; // Колонка для сортировки. Сначала sortname параметр затем index из colModel
$sord = $_GET['sord']; // Порядок сортировки.
if(!$sidx) $sidx =1; // Если колонка сортировки не указана, то будем  сортировать по первой колонке.

//определяем команду (поиск или просто запрос на вывод данных) если поиск, конструируем WHERE часть запроса
if ( isset($_GET['_search']) && $_GET['_search']=='true') {
    $qWhere = ''; //уточняющий запрос
    $allowedFields = array('id', 'name', 'timeOf', 'is_activee'); //разрещенные поля в запросе
    $allowedOperations = array('AND', 'OR'); //разрешенные логические операции

    //Если используется панель поиска
    if (!isset($_GET['filters'])) {
        $qWhere = ' WHERE ';
        $params = array();
        $firstElem = true;

        foreach ($allowedFields as $searchField) { //перебираем все доступные колонки и соединяем их через условие поиска AND %(?)%
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

    //Если используется расширенный поиск в jqGrid
    if (isset($_GET['filters'])) {
        $searchData = json_decode($_GET['filters']); //формирование массива передаваемый в JSON форме
        //ограничение на количество условий
        if (count($searchData->rules) > 10) { //Ограничение на количество условий.
            throw new Exception('Error JSON parametrs');
        }

        $qWhere = ' WHERE ';
        $params = array();
        $firstElem = true;

        //объединяем все полученные условия
        foreach ($searchData->rules as $rule) {
            if (!$firstElem) { //первое правило идет без логических условий к остальным дописываем
                //объединяемые условия AND или OR
                if (in_array($searchData->groupOp, $allowedOperations)) {
                    $qWhere .= ' ' . $searchData->groupOp . ' ';
                } else {
                    throw new Exception ('Error group params');
                }
            } else {
                $firstElem = false;
            }

            //если поле входит в список допустимых
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



//определяем количество записей в таблице
$query="select Count(*) as count from subsInfo".$qWhere;
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
$row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
$count = $row['count'];

//если параметр rowNum установлен в -1 ($limit), возвращаем таблицу целиком.
if ($limit=='all') {
    $limit=$count;
}

// Вычисляем общее количество страниц.
if( $count > 0 && $limit > 0) {
    $total_pages = ceil($count/$limit);
} else {
    $total_pages = 0;
}

// Если запрашиваемый номер страницы больше общего количества страниц,
// то устанавливаем номер страницы в максимальный.
if ($page > $total_pages) $page=$total_pages;

// Вычисляем начальное смещение строк.
$start = $limit*$page - $limit;

// Если начальное смещение отрицательно,
// то устанавливаем его в 0.
// Например, когда пользователь
// выбрал 0 в качестве запрашиваемой страницы.
if($start <0) $start = 0;

$query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo".$qWhere."
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";

// Запрос для получения данных.
$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

// Заголовок с указанием содержимого.
header("Content-type: text/xml;charset=utf-8");

$s = "<?xml version='1.0' encoding='utf-8'?>";
$s .=  "<rows>";
$s .= "<page>".$page."</page>";
$s .= "<total>".$total_pages."</total>";
$s .= "<records>".$count."</records>";

// Обязательно передайте текстовые данные в CDATA
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

//полная информация
function subsInfoFull($conn) {
    //читаем параметры
    $page = $_GET['page'];// Получаем номер страницы. Сначала jqGrid ставит его в 1.
    $limit = $_GET['rows']; // сколько строк мы хотим иметь в таблице - rowNum параметр
    $sidx = $_GET['sidx']; // Колонка для сортировки. Сначала sortname параметр затем index из colModel
    $sord = $_GET['sord']; // Порядок сортировки.
    if(!$sidx) $sidx =1; // Если колонка сортировки не указана, то будем  сортировать по первой колонке.

// Вычисляем количество строк. Это необходимо для постраничной навигации.
    $query="select Count(*) as count from subsInfo";
    $result=sqlsrv_query($conn,$query) or die( print_r( sqlsrv_errors(), true));
    $row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $count = $row['count'];

//если параметр rowNum установлен в -1 ($limit), возвращаем таблицу целиком.
    if ($limit=='all') {
        $limit=$count;
    }

// Вычисляем общее количество страниц.
    if( $count > 0 && $limit > 0) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }

// Если запрашиваемый номер страницы больше общего количества страниц,
// то устанавливаем номер страницы в максимальный.
    if ($page > $total_pages) $page=$total_pages;

// Вычисляем начальное смещение строк.
    $start = $limit*$page - $limit;

// Если начальное смещение отрицательно,
// то устанавливаем его в 0.
// Например, когда пользователь
// выбрал 0 в качестве запрашиваемой страницы.
    if($start <0) $start = 0;

    $query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";
// Запрос для получения данных.
//echo $query.'<hr>';
    $result=sqlsrv_query($conn,$query) or die( print_r( sqlsrv_errors(), true));

// Заголовок с указанием содержимого.
    header("Content-type: text/xml;charset=utf-8");

    $s = "<?xml version='1.0' encoding='utf-8'?>";
    $s .=  "<rows>";
    $s .= "<page>".$page."</page>";
    $s .= "<total>".$total_pages."</total>";
    $s .= "<records>".$count."</records>";

// Обязательно передайте текстовые данные в CDATA
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















//Функция фильрации запрашиваемых данных
function subsInfoFilter($conn) {
    //читаем параметры
    $page = $_GET['page'];// Получаем номер страницы. Сначала jqGrid ставит его в 1.
    $limit = $_GET['rows']; // сколько строк мы хотим иметь в таблице - rowNum параметр
    $sidx = $_GET['sidx']; // Колонка для сортировки. Сначала sortname параметр затем index из colModel
    $sord = $_GET['sord']; // Порядок сортировки.
    if(!$sidx) $sidx =1; // Если колонка сортировки не указана, то будем  сортировать по первой колонке.

    $qWhere = ''; //уточняющий запрос

    $allowedFields=array('id','name','timeOf','is_activee'); //разрещенные поля в запросе
    $allowedOperations=array('AND','OR'); //разрешенные логические операции

    $searchData=json_decode($_GET['filters']); //формирование массива передаваемый в JSON форме
    //ограничение на количество условий
    if ( count($searchData->rules)>5 ) {
        throw new Exception('Error JSON parametrs');
    }

    $qWhere=' WHERE ';
    $params=array();
    $firstElem=true;

    //объединяем все полученные условия
    foreach ($searchData->rules as $rule) {
        if (!$firstElem) { //первое правило идет без логических условий к остальным дописываем
            //объединяемые условия AND или OR
            if (in_array($searchData->groupOp,$allowedOperations)) {
                $qWhere.=' '.$searchData->groupOp.' ';
            } else {
                throw new Exception ('Error group params');
            }
        } else {
            $firstElem=false;
        }

        //если поле входит в список допустимых
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

    //определяем количество записей в таблице
    $query="select Count(*) as count from subsInfo".$qWhere;
    $result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
    $row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $count = $row['count'];

//если параметр rowNum установлен в -1 ($limit), возвращаем таблицу целиком.
    if ($limit=='all') {
        $limit=$count;
    }

// Вычисляем общее количество страниц.
    if( $count > 0 && $limit > 0) {
        $total_pages = ceil($count/$limit);
    } else {
        $total_pages = 0;
    }

// Если запрашиваемый номер страницы больше общего количества страниц,
// то устанавливаем номер страницы в максимальный.
    if ($page > $total_pages) $page=$total_pages;

// Вычисляем начальное смещение строк.
    $start = $limit*$page - $limit;

// Если начальное смещение отрицательно,
// то устанавливаем его в 0.
// Например, когда пользователь
// выбрал 0 в качестве запрашиваемой страницы.
    if($start <0) $start = 0;

    $query = "SELECT *
        FROM (
           SELECT *, ROW_NUMBER() OVER (ORDER BY $sidx $sord) AS x
           FROM subsInfo $qWhere
        ) AS y
        WHERE y.x BETWEEN ".$start." AND ".($start+$limit)." ORDER BY y.x, $sidx $sord;";
// Запрос для получения данных.
//echo $query.'<hr>';
    $result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));

// Заголовок с указанием содержимого.
    header("Content-type: text/xml;charset=utf-8");

    $s = "<?xml version='1.0' encoding='utf-8'?>";
    $s .=  "<rows>";
    $s .= "<page>".$page."</page>";
    $s .= "<total>".$total_pages."</total>";
    $s .= "<records>".$count."</records>";

// Обязательно передайте текстовые данные в CDATA
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