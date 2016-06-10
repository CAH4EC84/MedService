/**
 * Created by Alexander on 26.04.2016.
 * Инициализация табов
 */
$(function () {
    //$( document ).tooltip(); //Включаем подсказки для элементов содержащих атрибут title

    var initialized = [false,false] //Пометка о состоянии таба
    $("#tabs").tabs({
        create: function (event, ui) {
            initialized[0]=true;
            subsTableCreate();
            firmsInSubsTableCreate()
        },
        activate: function (event,ui){
            //Подписки
            if (ui.newTab.index() == 0 && !initialized[0]) {
                subsTableCreate();
                initialized[0]=true;
            }
            //Фирмы
            else if (ui.newTab.index() == 1 && !initialized[1]) {
                firmsTableCreate();
                initialized[1]=true;
            }
            //Отчеты
            else if (ui.newTab.index()==2 && !initialized[2]){
                showArcReport();
                maketReport();
                helpIcon();
                initialized[2]=true;
            }

        }
    })
})