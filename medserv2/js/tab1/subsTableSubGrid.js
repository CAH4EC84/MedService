/**
 * Created by Alexander on 26.04.2016.
 */
function subTableSubGrid (subgrid_id,row_id) {
    // передаем 2 параметра
    // subgrid_id используется для создания уникального идентификатора diva подчиненной таблицы
    // row_id номер разворачиваемой строчки
    var subgrid_table_id=subgrid_id+"_t";
    $("#"+subgrid_id).html("<table id='" + subgrid_table_id+"' class='scroll'></table>");
    $("#"+subgrid_table_id).jqGrid({
        url:"php/tab1/subsTableSubGrid.php?q=2&id="+ row_id,
        mtype:"GET",
        datatype:"xml",
        colNames:["node_firm","name","base_file","doc_type","base_timeOf","read_timeOf","error_text","actual_days"],
        colModel:[
            {name:"node_firm", width:30,align:"center"},
            {name:"name", width:200,align:"center"},
            {name:"base_file", width:200,align:"center"},
            {name:"doc_type", width:30,align:"center"},
            {name:"base_timeOf", width:150,align:"center"},
            {name:"read_timeOf", width:150,align:"center"},
            {name:"error_text", width:300,align:"center"},
            {name:"actual_days", width:300,align:"center"}
        ],
        height:"auto",
        autowidth:true,
        rowNum:"all",
        sortname:"name",
        sortorder:"asc",
        //subGrid:true,
        ondblClickRow:getQueryText
    })
}
