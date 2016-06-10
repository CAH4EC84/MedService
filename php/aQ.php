<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 04.03.2016
 * Time: 14:50
 */
/*$xml = simplexml_load_file('//meddb/d$/medUni/bin/accessQueries.xml');
echo "<pre>";
print_r($xml);
echo "</pre>";
*/
require_once 'sql-formatter-master/lib/SqlFormatter.php';

$query = 'INSERT INTO ALL_PRICES ( INT_EXTKEY, NAME_FORM, PRICE1, PACK1, MAKER_COUNTRY, NODES_ID, VENDOR, DateRef, Doc_Type, QUANTITY, LIFETIME, NDS, EAN, COSTMAKER )
SELECT protek21_l.CODE AS ExtKey, protek21_l.NAME AS Name_Form, protek21_l.PRICE AS Price1, protek21_l.MIN_QTY AS Pack1, [FIRM] & " " & [COUNTRY] AS Maker_Country, 26 AS Выражение1, "Протек-3 СПб" AS Выражение2, Now() AS Выражение3, 21 AS Doc_Type, protek21_l.QTY AS Quantity, protek21_l.EXP_DATE AS LIFETIME, protek21_l.NDS AS NDS, protek21_l.SBAR, manuf
FROM protek21_l;';

echo SqlFormatter::format($query);

?>

