<?php
/**
 * Created by PhpStorm.
 * User: Alexander
 * Date: 18.01.2016
 * Time: 13:27
 * Настройки подключения к MS SQL
 */
ini_set('mssql.charset','windows-1252');
$serverName='meddb';
$connectionInfo=array('Database'=>'upi_0_2',
    'UID'=>'convertor',
    'PWD'=>'Br@inFuck',
    'CharacterSet' => 'UTF-8',
    'ReturnDatesAsStrings'=>true);


// Настройки Email
$medserv['from_name'] = 'Golikov Alexander'; // from (от) имя
$medserv['from_email'] = 'alex2@medline.spb.ru'; // from (от) email адрес
// Указываем настройки
// для дополнительного (внешнего) SMTP сервера.
$medserv['smtp_mode'] = 'enabled'; // enabled or disabled (включен или выключен)
$medserv['smtp_host'] = 'mx.medline.spb.ru';
$medserv['smtp_port'] = 25;
$medserv['smtp_username'] = 'alex2@medline.spb.ru';
$medserv['smtp_password']='ale129ucanlqjnxa';
$medserv['Sendmail']='D:/UniServerZ/core/msmtp/msmtp.exe --file=D:/UniServerZ/core/msmtp/msmtprc.ini -t';