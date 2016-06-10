/**
 * Created by Alexander on 26.04.2016.
 */
//Таблица с данными о подписках, содержит подтаблицу с поставщиками и прайсами подключенными к конкретной подписке.
//Используется плагин jqGrid

function subsTableCreate() {
    $("#subsTable").jqGrid({
        url: "php/tab1/subsTable.php",
        mtype:"GET",
        datatype:"xml",
        colNames: ["id", "name", "timeOf", "is_activee"],
        colModel: [
            {name:"id",width:100, align:"center"},
            {name:"name", width:300,align:"center"},
            {name:"timeOf", width:300, align:"center"},
            {name:"is_activee", width:100, align:"center"}
        ],
        pager:"#subsPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Подписка -> Прайсы фирм",
        height:"auto",
        autowidth:true,
        subGrid:true, //Подтаблица
        subGridRowExpanded:subTableSubGrid
    })
    //Добавляем панель поиска
    .jqGrid('filterToolbar');
}