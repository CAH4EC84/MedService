<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 02.03.2016
 * Time: 10:08
 * Заголовок для XML
 * header('Content-Type: text/xml');
 */
require_once 'sql-formatter-master/lib/SqlFormatter.php'; // Внешняя билиотека форматирующая SQL для удобного чтения.


$fieldAlias=['Код товара'=>'INT_EXTKEY',
    'Продукция'=>'NAME_FORM',
    'Производитель Страна'=>'MAKER_COUNTRY',
    'Цена'=>'PRICE1',
    'Кратность'=>'PACK1',
    'Кол-во в упакове'=>'UPAK',
    'Остаток на складе'=>'QUANTITY',
    'Срок годности'=>'LIFETIME',
    'Цена производителя'=>'COSTMAKER',
    'НДС'=>'NDS',
    'Цена производителя с НДС'=>'COSTMAKER_NDS',
    'Цена Реестра без НДС'=>'COSTREESTR',
    'Акция'=>'Z',
    'Комментарий'=>'COMMENT_',
    'ШтрихКод'=>'EAN',
    'Узел'=>'NODES_ID',
    'Номер документа'=>'DOC_TYPE',
    'Минимальный заказ в штуках'=>'MINZAKAZ'
];

$nodesId=$_GET['nodes_id'];
$docId=$_GET['doc_id'];
$xml = simplexml_load_file('//meddb/d$/medUni/bin/accessQueries.xml');

//определяем базовый или индивидуальный прайс.
if ($docId) { $checkQ=$nodesId."_".$docId;} else { $checkQ=$nodesId;}
//Находим текст запроса по ноду и типу документа
$query = $xml->queries->xpath('query[@name="'.$checkQ.'"]');

//Определяем есть ли у запроса подзапрос. для этого выделяем из секции FROM название таблицы и ещем запрос с таким именем в xml файле
if (stripos($query[0],'where') ) {$delimeter='where'; } else {$delimeter=';';}
$tempName=substr($query[0],stripos($query[0],'from')+5,stripos($query[0],$delimeter)-stripos($query[0],'from')-5);
$subquery=$xml->queries->xpath('query[@name="'.$tempName.'"]') ;

if (!$subquery) {
    $tableName=trim($tempName);
} else {
    if (stripos($subquery[0],'where') ) {$delimeter='where'; } else {$delimeter=';';}
    $tableName=trim( substr($subquery[0],stripos($subquery[0],'from')+5,stripos($subquery[0],$delimeter)-stripos($subquery[0],'from')-6) );
}

//connect sourceTableName
$queryInfo=['nodesId'=>$nodesId,'docId'=>$docId,
    'querySQL'=>trim(strval($query[0])),'subQuerySQL'=>trim(strval($subquery[0]))];

$queryInfo['ParsedSQL']=parseSQL($query[0],$subquery[0]);




//Вывод результата обработки
//echo SqlFormatter::format($query);
echo '<span class="querySQL">'.SqlFormatter::format($queryInfo['querySQL']).'</span> <hr>'; //Текст запроса
echo '<span class="querySQL">'.SqlFormatter::format($queryInfo['subQuerySQL']).'</span> <hr>'; //Подзапрос

if ($queryInfo['ParsedSQL']['subList']) {
    echo "<pre>";
    //print_r($queryInfo['ParsedSQL']['subList']);
    foreach ($queryInfo['ParsedSQL']['subList'] as $key=>$value) {
        $key=strtoupper(trim($key));
        $alias = array_search($key,$fieldAlias);
        if ($alias===false) {
            $result[$key]=$value;
        } else {
            $result[$alias]=$value;
        }
    }
    print_r($result);
    echo "</pre>";
} else {
    echo "<pre>";
    $tmp=array_combine($queryInfo['ParsedSQL']['insertList'], $queryInfo['ParsedSQL']['selectList']);
    //print_r($tmp);
    foreach ($tmp as $key=>$value) {
        $key=strtoupper(trim($key));
        $alias = array_search($key,$fieldAlias);
        if ($alias===false) {
            $result[$key]=$value;
        } else {
            $result[$alias]=$value;
        }
    }
    print_r($result);
    echo "</pre>";
}


function parseSQL ($q,$subq) {
    //INSERT SECTION
    $insertFileds=substr($q,(stripos($q,'INSERT INTO ALL_PRICES (')+24),stripos($q,'SELECT')-(stripos($q,'INSERT INTO ALL_PRICES (')+26));
    $insertArr= explode(',',$insertFileds);

    if (!$subq) { //Если нет подзапроса
        //SELECT SECTION
        $selectFields=substr($q,(stripos($q,'SELECT')+6),stripos($q,'from')-(stripos($q,'SELECT')+6));
        $selectArr= explode(',',$selectFields);
    } else {
        //SELECT SECTION for SubQuery
        $selectFields=substr($q,(stripos($q,'SELECT')+6),stripos($q,'from')-(stripos($q,'SELECT')+6));
        $selectArr= explode(',',$selectFields);
        $selectArr=array_combine($insertArr, $selectArr);
        $subArr= explode(',',substr( $subq,8,stripos($subq,'from')-8));

        //Сопоставляем INSERT и подзапрос.
        foreach ($selectArr as $keyIndex=>$subField) {
            $key=trim($subField);
            $keyfound=false;
            $key=trim(substr($key,stripos($key,'.')+1)); //обрезаем от точки и до конца строки
            if (stripos($key,' as ')) { //если есть as значит используется псевдоним для псевдонима......
                $noaskey=substr($key,0,stripos($key,' as'));
                $key='AS '.substr($key,0,stripos($key,' as'));
            } else {
                $noaskey=$key;
                $key='AS '.$key;

            }

            foreach ($subArr as $value) { //Ищем в нормальном синтаксисе все поля имеют псевдонимы
                $value=trim($value);
                $vl=mb_strlen($value);
                $kpl=(mb_strlen($key)+mb_stripos($value,$key));
                if ( mb_stripos($value,$key ) && $vl==$kpl && $keyfound==false ) {
                    //echo "key found in value:$value at pos:".mb_stripos($value,$key)." vl:$vl; kpl:$kpl <br>";
                    $keyfound=true;
                    $resultarr[trim($keyIndex)]=$value;
                    break; //если нашли ключ выходим из проверки
                }
            }
            //Если поле на найдено проверяем его без псевдонима.
            if (!$keyfound) {
                foreach ($subArr as $value) { //Ищем в нормальном синтаксисе все поля имеют псевдонимы
                    $vl = mb_strlen($value);
                    $noaskpl = (mb_strlen($noaskey) + mb_stripos($value, $noaskey));
                    if (mb_stripos($value, $noaskey) && $vl == $noaskpl && $keyfound == false) {
                        echo "noaskey found in value:$value at pos:" . mb_stripos($value, $noaskey) . " vl:$vl; kpl:$noaskpl <br>";
                        $keyfound = true;
                        $resultarr[trim($keyIndex)]=$value;
                        break; //если нашли ключ выходим из проверки
                    }
                }
            }
        }

    }
    /*
    echo "<pre>";
    print_r($insertArr);
    print_r($selectArr);
    print_r($resultarr);
    echo "</pre>";*/
    return ['insertList'=>$insertArr,'selectList'=>$selectArr,'subList'=>$resultarr];



}
