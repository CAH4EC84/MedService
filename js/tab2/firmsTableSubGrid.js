/**
 * Created by Alexander on 27.04.2016.
 */
function firmsTableSubGrid (subgrid_id,row_id) {
    // передаем 2 параметра
    // subgrid_id используется для создания уникального идентификатора diva подчиненной таблицы
    // row_id номер разворачиваемой строчки используется для получения значения nodes_id
    nodes_id = $("#tabs-2 #" + row_id + " td:nth-child(2)").text();
    var subgrid_table_id=subgrid_id+"_t";
    $("#"+subgrid_id).html("<table id='" + subgrid_table_id+"' class='scroll'></table>");
    $("#"+subgrid_table_id).jqGrid({
        url:"php/tab2/firmsTableSubGrid.php?q=2&nodes_id="+ nodes_id,
        mtype:"GET",
        datatype:"xml",
        colNames:["id","id_old","parent_id","typename","subs_id","username","password"],
        colModel:[
            {name:"id", width:50,align:"center"},
            {name:"id_old", width:50,align:"center"},
            {name:"parent_id", width:50,align:"center"},
            {name:"typename", width:200,align:"center"},
            {name:"subs_id", width:150,align:"center"},
            {name:"username", width:300,align:"center"},
            {name:"password", width:300,align:"center"}
        ],
        height:"auto",
        autowidth:true,
        rowNum:"all",
        sortname:"id",
        sortorder:"asc"
    })
}
