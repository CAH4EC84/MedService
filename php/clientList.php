<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 17.03.2016
 * Time: 14:46
 */

$level=$_GET['level'];

require_once '../conf/login.php';
$conn=sqlsrv_connect($serverName,$connectionInfo);


switch ($level)  {
    case 'Все заказы':
        $qOrder=" order by DATA";
        break;
    case 'Регионы':
        $query="Select DISTINCT ID,NAME from medline39.dbo.REGIONS where PARENT_ID=4 order by NAME";
        break;
    case 'Апт. Сети':
        $query="Select DISTINCT f1.ID,F1.NAME from medline39.dbo.FIRMS as F1
                inner join medline39.dbo.FIRMS as F2 on F1.ID=F2.PARENT_ID
                where f1.PARENT_ID<>0 and f1.DELETED<>100 order by F1.NAME";
        break;
    case 'Аптеки':
        $query="Select F.ID,F.NAME,FTT.TYPE
                from medline39.dbo.FIRMS as F
                inner join medline39.dbo.FIRM_TO_TYPES as FTT on F.ID=FTT.FIRMS_ID
                where F.DELETED<>100 and FTT.DELETED<>100 and F.NODES_ID<>0 and F.PARENT_ID<>0 and FTT.TYPE=7 order by F.NAME";
        break;
    case 'Поставщики':
        $query="Select F.ID,F.NAME,FTT.TYPE
                from medline39.dbo.FIRMS as F
                inner join medline39.dbo.FIRM_TO_TYPES as FTT on F.ID=FTT.FIRMS_ID
                where F.DELETED<>100 and  FTT.DELETED<>100 and F.NODES_ID<>0 and F.PARENT_ID<>0 and FTT.TYPE=6 order by F.NAME";
        break;
    default:
        throw new Exception('error rules level filter!)');
};

$result=sqlsrv_query($conn,$query,$params) or die( print_r( sqlsrv_errors(), true));
while($row=sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
    $data[$row['NAME']]=$row['ID'];
};
$data=json_encode($data);
echo $data;
?>





