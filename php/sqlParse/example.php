<?php
/**
* Author  : NEUMANN-RYSTOW François <kalachnkv@free.fr>
* Date    : 23 Oct, 2008
* Purpose : Usage example of dqml2tree class
*/

require 'dqml2tree.php';
// complex query
$sql_query = 'INSERT INTO ALL_PRICES ( INT_EXTKEY, NAME_FORM, MAKER_COUNTRY, PRICE1, PACK1, LIFETIME, QUANTITY, VENDOR, DateRef, NODES_ID, Doc_Type, COSTMAKER, COSTMAKER_NDS, NDS, UPAK, COMMENT_, EAN, COSTREESTR ) SELECT tamda_2.Int_ExtKey, tamda_2.Name_Form, tamda_2.Maker AS Maker_Country, tamda_2.Price1, tamda_2.Pack1, tamda_2.Lifetime, tamda_2.Quantity, tamda_2.Vendor, tamda_2.DateRef, tamda_2.Nodes_ID, tamda_2.Doc_Type, tamda_2.COSTMAKER, tamda_2.COSTMAKERNDS, tamda_2.VAT, tamda_2.[Êîë-âî â óï], " Êð: " & [Pack1] & " ØÊ: " & [Comment] AS Âûðàæåíèå1, tamda_2.EAN, tamda_2.COSTREESTR FROM tamda_2;
';


$query2tree = new dqml2tree($sql_query);
$sql_tree = $query2tree->make();
echo "<pre>";
print_r($sql_tree);
echo "</pre>";
?>
