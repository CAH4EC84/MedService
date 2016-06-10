<?php
require_once('dbdata.php');

try {
    //читаем параметры
    $curPage = $_POST['page'];
    $rowsPerPage = $_POST['rows'];
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];
    
    //подключаемся к базе
    $dbh = new PDO('mysql:host='.$dbHost.';dbname='.$dbName, $dbUser, $dbPass);
    //указываем, мы хотим использовать utf8
    $dbh->exec('SET CHARACTER SET utf8');

	$qWhere = '';
	//определяем команду (поиск или просто запрос на вывод данных)
	//если поиск, конструируем WHERE часть запроса
	if (isset($_POST['_search']) && $_POST['_search'] == 'true') {
		$allowedFields = array('surname', 'fname', 'lname');
		$allowedOperations = array('AND', 'OR');
		
		$searchData = json_decode($_POST['filters']);

		//ограничение на количество условий
		if (count($searchData->rules) > 10) {
			throw new Exception('Cool hacker is here!!! :)');
		}

		$qWhere = ' WHERE ';
		$firstElem = true;

		//объединяем все полученные условия
		foreach ($searchData->rules as $rule) {
			if (!$firstElem) {
				//объединяем условия (с помощью AND или OR)
				if (in_array($searchData->groupOp, $allowedOperations)) {
					$qWhere .= ' '.$searchData->groupOp.' ';
				}
				else {
					//если получили не существующее условие - возвращаем описание ошибки
					throw new Exception('Cool hacker is here!!! :)');
				}
			}
			else {
				$firstElem = false;
			}
			
			//вставляем условия
			if (in_array($rule->field, $allowedFields)) {
				switch ($rule->op) {
					case 'eq': $qWhere .= $rule->field.' = '.$dbh->quote($rule->data); break;
					case 'ne': $qWhere .= $rule->field.' <> '.$dbh->quote($rule->data); break;
					case 'bw': $qWhere .= $rule->field.' LIKE '.$dbh->quote($rule->data.'%'); break;
					case 'cn': $qWhere .= $rule->field.' LIKE '.$dbh->quote('%'.$rule->data.'%'); break;
					default: throw new Exception('Cool hacker is here!!! :)');
				}
			}
			else {
				//если получили не существующее условие - возвращаем описание ошибки
				throw new Exception('Cool hacker is here!!! :)');
			}
		}
	}
	
    //определяем количество записей в таблице
    $rows = $dbh->query('SELECT COUNT(id) AS count FROM users'.$qWhere);
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    //получаем список пользователей из базы
    $res = $dbh->query('SELECT * FROM users '.$qWhere.' ORDER BY '.$sortingField.' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage);
	
    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
    $response->page = $curPage;
    $response->total = ceil($totalRows['count'] / $rowsPerPage);
    $response->records = $totalRows['count'];

    $i=0;
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'], $row['surname'], $row['fname'], $row['lname']);
        $i++;
    }
    echo json_encode($response);
}
catch (Exception $e) {
    echo json_encode(array('errMess'=>'Error: '.$e->getMessage()));
}

// end of getdata.php