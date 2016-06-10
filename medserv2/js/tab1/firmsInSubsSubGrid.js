/**
 * Created by Alexander on 01.06.2016.
 */
function firmsInSubsTableSubGrid(subgrid_id,row_id) {
    // передаем 2 параметра
    // subgrid_id используется для создания уникального идентификатора diva подчиненной таблицы
    // row_id номер разворачиваемой строчки
    var subgrid_table_id=subgrid_id+"_t";
    $("#"+subgrid_id).html("<table id='" + subgrid_table_id+"' class='scroll'></table>");
    $("#"+subgrid_table_id).jqGrid({
        url:"php/tab1/firmsInSubsTableSubGrid.php?q=2&id="+ row_id,
        mtype:"GET",
        datatype:"xml",
        colNames:["node_firm","id","name","doc_type","subs_is_active","base_file","base_timeOf","read_timeOf","error_text"],
        colModel:[
            {name:"node_firm",hidden:true},
            {name:"id", width:30,align:"center"},
            {name:"name", width:200,align:"center"},
            {name:"doc_type", width:45,align:"center"},
            {name:"subs_is_active", width:60,align:"center"},
            {name:"base_file", width:200,align:"center"},
            {name:"base_timeOf", width:120,align:"center"},
            {name:"read_timeOf", width:120,align:"center"},
            {name:"error_text", width:300,align:"center"}
        ],
        height:"auto",
        autowidth:true,
        rowNum:"all",
        sortname:"id",
        sortorder:"asc",
        ondblClickRow:getQueryText
    })
}