# Diler
Информация_о_поставщиках

Серверная часть PHP. Работаем с MS SQL.
Используется собственная БД upi_0_2. Пользователь convertor. Пароль Br@inFuck.
Настройки подключения хранятся в файле conf/login.php



21.01.2016
Принцип работы сайта:
В БД есть представления, через которые данные выбираются на сайт.
Сайт содержит меню с вкладками,(ид вкладки на сайте соответсвует названию представления в БД) в которые при загрузке выбирается вся информация из представлений.
По умолчанию сайт открывается на вкладке "Подписки" (subsInfo).

Пользователь видит всю информацию которую возвращает представление в виде таблицы.Пользователю доступен поиск по данной таблице.

    Поиск по таблице.
    Реализован на стороне клиента (браузера) средствами JS.
    В заголовке таблицы содержится 2 строки, первая с наименованием столбцов, вторая с поисковыми окнами.
    При вводе информации в окно поиска, после 500мс паузы во вводе, избыточные данные скрываются.

    Открытие дополнительных данных.
    Прик двойном клике на строчке открывается дополнительная информация о объекте.
        Пример меню подписки:
        Окно открывается со списком всех подписок (veiw subsInfo), при двойном клике по интересующей нас подписке отправляется запрос
        на сервер и выбирается информация о поставщиках входящих в подписку (subsInfoDetails).




















