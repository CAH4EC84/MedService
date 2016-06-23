/**
 * Created by Alexander on 23.06.2016.
 */
//Список прайсов с ошибками
function priceReadErrors() {
    $("#priceReadErrors").jqGrid({
        url: "php/tab1/priceReadErrors.php",
        mtype:"GET",
        datatype:"xml",
        colNames:["subs_id","node_firm","name","doc_type","base_file","base_timeOf","read_timeOf","error_text"],
        colModel:[
            {name:"subs_id", width:30,align:"center"},
            {name:"node_firm",hidden:true},
            {name:"name", width:200,align:"center"},
            {name:"doc_type", width:45,align:"center"},
            {name:"base_file", width:200,align:"center"},
            {name:"base_timeOf", width:120,align:"center"},
            {name:"read_timeOf", width:120,align:"center"},
            {name:"error_text", width:300,align:"center"}
        ],
        height:"auto",
        hiddengrid: true,
        pager:"#priceReadErrorsPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"subs_id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Ошибки чтения прайсов",
        autowidth:true,
    })
        //Добавляем панель поиска
        .jqGrid('filterToolbar');
}