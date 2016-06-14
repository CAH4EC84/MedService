/**
 * Created by Alexander on 27.04.2016.
 */
function firmsTableCreate() {
    $("#firmsTable").jqGrid({
        url:"php/tab2/firmsTable.php",
        mtype:"GET",
        datetype:"xml",
        ignoreCase:true,
        colNames:["nodes_id","name","parent","address1","region"],
        colModel:[
            {name:"nodes_id", width:'50',align:"center"},
            {name:"name", width:'300',align:"center"},
            {name:"parent", width:'300',align:"center"},
            {name:"address1", width:'300',align:"center"},
            {name:"region", width:'300',align:"center"}
        ],
        pager:"firmsPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"nodes_id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Фирмы",
        height:"auto",
        autowidth:true,
        subGrid:true,
        subGridRowExpanded:firmsTableSubGrid
    })
        .jqGrid("filterToolbar",{});

}
