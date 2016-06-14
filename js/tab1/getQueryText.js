/**
 * Created by Alexander on 27.04.2016.
 */
function getQueryText (rowid,iRow,iCol,e) {
    // передаем 2 параметра
    // subgrid_id используется для создания уникального идентификатора diva подчиненной таблицы
    // row_id номер разворачиваемой строчки используется для получения значения nodes_id и doc_type
    var tableName =($(e.currentTarget).attr("id"));
    var nodesId=$("#"+tableName+" #"+rowid+" td:nth-child(1)").text();
    var docType=$("#"+tableName+" #"+rowid+" td:nth-child(4)").text();
    if (docType==1) {queryName=nodesId} else {queryName=nodesId+"_"+docType}
    $("#queryTextModal").dialog({
        modal:true,
        height:400,
        width:600,
        zIndex:5,
        resizable:true,
        title:queryName
    })
        .progressbar({
        value: false
    });
    $.get('php/tab1/getQueryText.php', {queryName:queryName},
        function (data) {
            $('#queryTextModal').html(data);
        });
}