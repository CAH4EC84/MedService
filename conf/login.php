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