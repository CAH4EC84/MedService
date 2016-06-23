<?php
require_once '../../conf/login.php';
$mail = 'alex2@medline.spb.ru';
$filename='http://medserv.medline.spb.ru/output/9765EB84-3EA8-44AD-8A6A-62466E1C8376.xml';
$title='Отчет по заказам';
$comment='Квартал афганы';

if ($mail) {
    $subject = "Ссылка на отчет";
    $subject = 'Тестовое письмо';
    $message = "
<html>
    <head>
    </head>
    <body>
        <p> $title <br> $comment <hr>
        <a href='$filename'>Скачать отчет</a>
        </p>
    </body>
</html>";

    $headers  = "Content-type: text/html; charset=UTF-8 \r\n";
    $headers .= "From: Medserv Report Module <".$medserv['from_email']."\r\n";
    $headers .= "Bcc: ".$medserv['from_email']."\r\n";
    mail($mail, $subject, $message, $headers);
}


//mail("alex2@medline.spb.ru", "My Subject", "Line 1\nLine 2\nLine 3");
?> 