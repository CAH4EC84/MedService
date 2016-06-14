/**
 * Created by Alexander on 01.06.2016.
 */

//Список поставщиков
function firmsInSubsTableCreate() {
    $("#firmsInSubs").jqGrid({
        url: "php/tab1/firmsInSubs.php",
        mtype:"GET",
        datatype:"xml",
        colNames: ["id", "name", "active","useCodeForProduct","useCodeFormaker","actual_days"],
        colModel: [
            {name:"id",width:100, align:"center"},
            {name:"name", width:300,align:"center"},
            {name:"active", width:100, align:"center"},
            {name:"useCodeForProduct", width:100, align:"center"},
            {name:"useCodeFormaker", width:100, align:"center"},
            {name:"actual_days", width:100, align:"center"}
        ],
        hiddengrid: true,
        pager:"#firmsInSubsPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Фирма -> Прайсы в подписках",
        height:"auto",
        autowidth:true,
        subGrid:true, //Подтаблица
        subGridRowExpanded:firmsInSubsTableSubGrid
    })
        //Добавляем панель поиска
        .jqGrid('filterToolbar');
}