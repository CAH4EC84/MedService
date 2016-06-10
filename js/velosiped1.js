/*
 Created by Alexander on 26.01.2016.
 При загрузке получаем список вкладок и асинхронно заполняем их информацией
*/

// при загрузке страницы запрос полных данных из БД для всех вкладок.
$(document).ready(function () {
    console.log("DOM loaded.Jquery-2.1.4");

    var tabs = $('input[name="maincontainer-radio"]');
    for (var i = 0; i < tabs.length; i++) {
        console.log('Try to ask ' + tabs[i].id);
        getInfo(tabs[i].id);
        resizeColumns(tabs[i].id+'Table');
    }


    //для каждой вкладки, в её основную таблицу, в строки поиска, добавляем функцию поиска по мере набора текста.
    $(".maincontainer>input").each (function () {
        $(".inputFilter",$('div[class$='+this.id+']')).click( function() {
            //Функция фильтрации данных при поиске.;
        //Для каждой кнопки поиска опеределяем её родительскую таблицу.
            var parentTable = $(this).parent().parent().parent();
            var trAll = $("tbody tr",parentTable);
        //Передаем в функцию обработки нажатия кнопок.
            var searchInputs = $(".inputFilter",parentTable);

            //Фильтры для данной таблицы обнуляются
            for (var i=0; i<searchInputs.length;i++) {
                trAll.removeClass('filtredCol'+(i+1))
            }
            var filtredClass='';
            $(".inputFilter",parentTable).keyup(debounce(function () {
                trAll.hide()
                //Фильтруем данные
                searchInputs.each(function () {
                    if ( $(this).children().val() ) {
                        var colIndex = ($(this).index() + 1).toString(); //Узнаем номер столбца таблицы
                        trAll.removeClass('filtredCol'+colIndex); //Удаляем ранее установленный на него фильтр
                        var colData = $("td:nth-child(" + colIndex + ")", trAll); //выбираем все  данные столбца
                        colData.filter(":contains(" + $(this).children().val().toLowerCase() + ")").parent("tr").addClass('filtredCol'+colIndex);
                        filtredClass+='.filtredCol'+colIndex;
                    }
                });

                //Выводим только отфильрованные данные
                if (filtredClass) {
                    $(filtredClass).show();
                } else {trAll.show()};
            },300));
        })
    })

    //Событие для клика по строчкам таблицы - получение дополнительной информации из представлений в БД
    $("tbody tr").dblclick( function () {
    //Функция переноса данных из строки таблицы в окна фильтрации
        //Передаем данные по кликнутой строке в строки поиска и скрываем всю таблицу
        var pT=$(this).parent().parent()
        $("td",this).each (function() {
            $( $(".inputFilter",pT)[$(this).index()]).children().val($(this).text() )
        })
        $("tbody tr",pT).hide();

        //Отправка уточняющего запроса к БД
        //Передаем текущую таблицу и поле ИД выбранной строчки
        getInfoDetail( $("input:checked").attr('id'), $(".inputFilter",pT).children().val() );
    })



});




// ФУНКЦИИ

//Функция задержки
function debounce(fn, duration) {
    var timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(fn, duration)
    }
}


//Функция запроса данных из БД для каждой вкладки
function getInfo(tab) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange=function () {
        if (xhr.status==200 && xhr.readyState==4) {
            document.querySelectorAll('div[class$='+tab+']').item(0).innerHTML=xhr.responseText;
        }
    };
    var params="tab="+tab;
    xhr.open("POST","php/getInfo.php",false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);
}


//Функция уточняющего запроса к БД
function getInfoDetail(tab,queryId) {
    console.log("getInfoDetail");
    console.log(tab);
    console.log(queryId);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange=function () {
        if (xhr.status==200 && xhr.readyState==4) {
            $('[class$='+tab+'] + .details').html(xhr.responseText);
        }
    };
    var params="tab="+tab+'Details'+'&queryId='+queryId;
    xhr.open("POST","php/getInfo.php",false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);

}


//Масштабируем таблички


function resizeColumns (tableName) {
    console.log('Resize '+tableName);
    var firstLine= $('#'+tableName+" tbody tr:first>td");
 $('#'+tableName+" tbody tr:first>td").each (function () {
 var colNum='#'+tableName+" tbody td:nth-child("+($(this).index()+1)+")"
     console.log(colNum);
 lengths=[]
 $(colNum).each ( function () {
 lengths.push( $(this).text().length );
 })
     console.log ('char count-'+Math.max.apply(Math,lengths) )
 var maxL=Math.max.apply(Math,lengths)+'px'
 $('#'+tableName+" thead td:nth-child("+($(this).index()+1)+")"+".inputFilter").children().attr('size',Math.max.apply(Math,lengths))
 $()
 })
}



