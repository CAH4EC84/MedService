<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 20.05.2016
 * Time: 10:39
 */
//Класс  с параметрами отчета.
class Report
{
    public $fieldsList = []; // Названия полей в SQL запросе
    public $cname = []; // Псевдонимы полей, а так же названия полей в GeneratedReports
    public $filter = []; //Фильтры на поля

    public $range = [];
    public $title = '';
    public $repDateList=[];
    public $from ='';
    public $to='';
    public $comment='';

    //SQL
    public $query =[];
    public $subQuery = '';
    public $regQuery = '';
    public $select = 'SELECT ';
    public $insertFromSelect = '';
    public $groupFromSelect='';
    public $where = ' WHERE ';
    public $group = ' GROUP BY ';
    public $insert = 'INSERT INTO GeneratedReports (GenerationDate,FileName,DateRange,RepType,Comment,';
    public $insertValues ='VALUES (current_timestamp,';

    function prepare_date ($fDate,$tDate) {
        $this->from=$fDate;
        $this->to=$tDate;
        $limit=15;
        $format = 'd.m.Y';
        $from = DateTime::createFromFormat($format, $fDate);
        $tmpTo = DateTime::createFromFormat($format, $fDate);
        $to = DateTime::createFromFormat($format, $tDate);
        $interval= date_diff($from,$to);
        $daysCount=$interval->format('%a');
        //Если временной промежут более 30 дней разбиваем запрос на несколько частей
        if ($daysCount>0 && $daysCount>$limit) {
            $tmpTo->modify("+$limit days");
            while ($tmpTo<$to) {
                $this->repDateList[]="'".date_format($from, 'd.m.Y')."' and '".date_format($tmpTo, 'd.m.Y')."'";
                $from->modify("+$limit days");
                $tmpTo->modify("+$limit days");
            }
            $this->repDateList[]="'".date_format($from, 'd.m.Y')."' and '".date_format($to, 'd.m.Y')."'";
        } else {
            $this->repDateList[]="'".date_format($from, 'd.m.Y')."' and '".date_format($to, 'd.m.Y')."'";
        }
    }

    function set_type($reptype,$tname)
    {
        switch ($reptype) {
            case 'orderReport':
                $this->title = 'Отчет по заказам';
                $this->fieldsList=['Дата',
                    'Регион.name',
                    'Аптечная_сеть.name',
                    'Аптека.name',
                    'Поставщик.name',
                    'SUM(Сумма)'
                ];
                $this->cname=['CreateDate','Region','Network','Apteka','Diler','Paysumm'];

                $this->subQuery="(Select DISTINCT Дата,НомерЗаказа,ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика,Сумма
                                        from upi_0_2.dbo.FullData
	                                    where Дата between REPLACE4DATE and ИдТипаДокумента=8) as tmp
	                              Left Join medline39.dbo.FIRMS as Аптека on Аптека.ID=ИдАптеки
	                              Left Join medline39.dbo.FIRMS as Аптечная_сеть on Аптечная_сеть.ID=ИдАптечнойСети
	                              Left Join medline39.dbo.FIRMS as Поставщик on Поставщик.ID=ИдПоставщика
	                              Left Join medline39.dbo.REGIONS as Регион on Регион.ID=ИдРегиона";
                //$this->subQuery= " into [$tname] from ".$this->subQuery;
                break;
            case 'productionReport':
                $this->title = 'Отчет по продукции';
                $this->fieldsList=['Дата',
                    'Регион.name',
                    'Аптечная_сеть.name',
                    'Аптека.name',
                    'Поставщик.name',
                    'Препарат',
                    'Производитель',
                    'Препарат+\' \'+Производитель',
                    'SUM(Сумма)',
                    'Цена',
                    'Sum(Количество)',
                    'SUM(Обращаемость)'
                    ];
                $this->cname=['CreateDate','Region','Network','Apteka','Diler','Production','Producer','ProductionProducer','Paysumm','Price','Count','Rotate'];

                $this->subQuery="(Select DISTINCT Дата,НомерЗаказа,ИдРегиона,ИдАптечнойСети,ИдАптеки,ИдПоставщика,Препарат, Производитель,Цена,Количество,Цена * Количество as Сумма,1 as Обращаемость
                                        from upi_0_2.dbo.FullData
	                                    where Дата between REPLACE4DATE and ИдТипаДокумента=8) as tmp
	                              Left Join medline39.dbo.FIRMS as Аптека on Аптека.ID=ИдАптеки
	                              Left Join medline39.dbo.FIRMS as Аптечная_сеть on Аптечная_сеть.ID=ИдАптечнойСети
	                              Left Join medline39.dbo.FIRMS as Поставщик on Поставщик.ID=ИдПоставщика
	                              Left Join medline39.dbo.REGIONS as Регион on Регион.ID=ИдРегиона";
                //$this->subQuery= " into [$tname] from ".$this->subQuery;
                break;
            case 3:
                $this->title = 'Отчет по оборотам';
                break;
            default:
                $this->title = 'Тип не определен';
        }
    }
    function construct_sql ($fields,$tname) {
        $fname=$tname.'.xml';
        //Устанавливаем в регистрационном запросе -  имя файл, временной промежуток, тип отчета и комментарий
        $this->insertValues.="'$fname','$this->from - $this->to','$this->title','$this->comment',";


        //Формируем запрос на выборку данных , и запрос регистрирующий отчет.

        foreach ($fields as $key=>$value) {
            //SELECT
            $this->select.=$this->fieldsList[$value['id']] .' as '.$this->cname[$value['id']] .',';


            //INSERT Псевдоним, который соответсвует полю в таблице GeneratedReports
            $this->insert.= $this->cname[$value['id']].',';
            $this->insertValues.='1,';

            //INSERT для разбивки по датам.
            $this->insertFromSelect.= $this->cname[$value['id']].',';

            //WHERE & Фильтры
            if ($value['filter']) {
                $this->where.=$this->fieldsList[$value['id']] .' '.$value['filter']. ' AND ';
                $this->insert.= $this->cname[$value['id']].'Filter,';
                $this->insertValues.="'".preg_replace("/'/","''",$value['filter'])."',";
            }

            //GROUP BY
            if ( stripos( $this->fieldsList[$value['id']],'SUM(')===false ) {
                $this->group.=$this->fieldsList[$value['id']] .',';
            }
        }

        //Удаляем лишни символы и соединяем части запроса.
        $this->insert=substr($this->insert,0,strlen($this->insert)-1).')';
        $this->insertFromSelect="(".substr($this->insertFromSelect,0,strlen($this->insertFromSelect)-1).')';
        $this->insertValues=substr($this->insertValues,0,strlen($this->insertValues)-1).')';
        $this->select=substr($this->select,0,strlen($this->select)-1);
        if ($this->where==' WHERE ') {$this->where='';} else {$this->where=substr($this->where,0,strlen($this->where)-4);}
        $this->group=substr($this->group,0,strlen($this->group)-1);

        //Для каждого временого диапозона формируем свой запрос на выборку
        if (count($this->repDateList)>1) {
            foreach ($this->repDateList as $key=>$value) {
                 if ($key=='0') { //Если это первый запрос из диапозона - то созадем им таблицу
                     $this->query[$key] = $this->select ." into [$tname] from ". $this->subQuery . $this->where . $this->group . " order by 1 ";
                     $this->query[$key]=str_replace('REPLACE4DATE',$value,$this->query[$key]);
                 } else {
                     $this->query[$key] = "INSERT INTO [$tname] $this->insertFromSelect ".$this->select. " FROM ". $this->subQuery . $this->where . $this->group . " order by 1 ";
                     $this->query[$key]=str_replace('REPLACE4DATE',$value,$this->query[$key]);
                 }
            }
        } else {
            $this->subQuery= " into [$tname] from ".$this->subQuery;
            $this->query[] = $this->select . $this->subQuery . $this->where . $this->group . " order by 1 ";
            $this->query[0]=str_replace('REPLACE4DATE',$this->repDateList[0],$this->query[0]);
        }
        $this->regQuery=$this->insert.' '.$this->insertValues;

        echo "<pre>";
        print_r($this->query);
        echo "</pre>";
    }


    function export_result ($conn,$fname,$q,$tname) {
        //Сохраняем результат во временную таблицу, выводим и удаляем таблицу

        foreach ($q as $key=>$value) {
            set_time_limit(300); //300 секунд на выполнение.
            $result = sqlsrv_query($conn,$value) or die (print_r(sqlsrv_errors(),true));
        }


        if (count($q)>1) {
            echo $this->insertFromSelect."<br>";
            $this->insertFromSelect=str_replace('(','',$this->insertFromSelect);
            $this->insertFromSelect=str_replace(')','',$this->insertFromSelect);
            $this->insertFromSelect=str_replace('Paysumm','SUM(Paysumm) as Paysumm',$this->insertFromSelect);
            $this->insertFromSelect=str_replace('Rotate','SUM(Rotate) as Rotate',$this->insertFromSelect);
            $this->insertFromSelect=str_replace('Count','SUM(Count) as Count',$this->insertFromSelect);
            echo "slpart-".$this->insertFromSelect."<br>";

            //Удаляем из группировки поля которые суммуриются
            $grReplace=array (",SUM(Paysumm) as Paysumm",",SUM(Rotate) as Rotate",",SUM(Count) as Count");
            $this->groupFromSelect=str_replace($grReplace,' ',$this->insertFromSelect);
            echo "grpart-".$this->groupFromSelect."<br>";




            $queryTmp="Select $this->insertFromSelect from upi_0_2.dbo.[$tname] GROUP BY $this->groupFromSelect";
            echo $queryTmp."<hr>";
            $stmt = sqlsrv_query($conn,"Select Count(*) as count FROM ($queryTmp) as tmp") or die (print_r(sqlsrv_errors(),true));
        } else {
            $stmt = sqlsrv_query($conn,"Select Count(*) as count FROM upi_0_2.dbo.[". $tname ."]") or die (print_r(sqlsrv_errors(),true));
            $queryTmp="Select * from upi_0_2.dbo.[$tname]";
            echo $queryTmp."<hr>";
        }

        $row=sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
        $limit = $row['count'];
        echo $limit;
            if ($result && $limit > 0 ) {
                //Выводим результат.
                $cmd = 'bcp "'.$queryTmp.' FOR XML PATH,ROOT(\'UID\')" queryout d:\UniServerZ\www\medserv2\output' . "\\" . $fname . ' -w -r  -S meddb -U sa -P supertrimcreator';
                echo "<hr>$cmd";
                exec($cmd);
                sqlsrv_query($conn, "DROP TABLE upi_0_2.dbo.[$tname]") or die (print_r(sqlsrv_errors(), true));
                sqlsrv_query($conn, $this->regQuery) or die (print_r(sqlsrv_errors(), true));
                sqlsrv_query($conn, "update upi_0_2.dbo.GeneratedReports  SET rowsCount=$limit where FileName='$fname'") or die (print_r(sqlsrv_errors(), true));
            }
            if  ($result && $limit == 0 ) {
                echo "Query returns empty set <br>";
                //sqlsrv_query($conn, "DROP TABLE upi_0_2.dbo.[$tname]") or die (print_r(sqlsrv_errors(), true));
                sqlsrv_query($conn, $this->regQuery) or die (print_r(sqlsrv_errors(), true));
                sqlsrv_query($conn, "update upi_0_2.dbo.GeneratedReports  SET rowsCount=$limit where FileName='$fname'") or die (print_r(sqlsrv_errors(), true));
            }

        sqlsrv_free_stmt($stmt);
        sqlsrv_free_stmt($result);
        sqlsrv_close($conn);

    }
}
//Подключаемся к БД
require_once '../../conf/login.php';
$conn=sqlsrv_connect($serverName, $connectionInfo) or die (print_r(sqlsrv_errors(),true));



//Получаем данные из формы.
$type = $_GET['type'];
$fields = $_GET['fields'];
$filename = $_GET['fname'].'.xml';
$tablename = $_GET['fname'];
$mail = $_GET['mail'];





//Новый объект отчета
$object = new Report();

//Анализ полученной даты
$object->prepare_date( $_GET['from'],$_GET['to']);
//Устанавливаем тип отчета и комментарий
$object->comment=$_GET['comment'];

//Определяем тип отчета
$object->set_type($_GET['type'],$tablename,$object->comment);

//Определяем тип отчета,выбранные столбцы и фильтры для них
$object->construct_sql($_GET['fields'],$tablename,$repDateList);

//Выгружаем отчет через BCP, и регистрируем его в списке сгенерированных отчетов
$object->export_result($conn,$filename,$object->query,$tablename);




















