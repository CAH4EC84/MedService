<?php
require_once('dbdata.php');

try {
    //читаем новые значения
    $id = $_POST['id'];
    $surname = $_POST['surname'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    
    //подключаемся к базе
    $dbh = new PDO('mysql:host='.$dbHost.';dbname='.$dbName, $dbUser, $dbPass);
    //указываем, мы хотим использовать utf8
    $dbh->exec('SET CHARACTER SET utf8');

    //определяем количество записей в таблице
    $stm = $dbh->prepare('UPDATE users SET surname=?, fname=?, lname=? WHERE id=?');
    $stm->execute(array($surname, $fname, $lname, $id));
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}

// end of saverow.php