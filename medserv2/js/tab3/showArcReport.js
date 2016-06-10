/**
 * Created by Alexander on 27.05.2016.
 */
function showArcReport () {
    $("#selectArcReport").jqGrid({
        url: "php/tab3/showArcReport.php",
        mtype:"GET",
        datatype:"xml",
        colNames: ["id", "RepType","GenerationDate","DateRange", "Download","RowsCount","Comment"],
        colModel: [
            {name:"id",width:20,align:"center"},
            {name:"RepType", width:50,align:"center"},
            {name:"GenerationDate", width:70,align:"center"},
            {name:"DateRange", width:70,align:"center"},
            {name:"Download", width:50,align:"center",formatter:downloadFile},
            {name:"RowsCount", width:20,align:"center"},
            {name:"Comment", width:70,align:"center"}
        ],
        pager:"arcReportPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Архив отчетов",
        height:"auto",
        autowidth:true,
        ondblClickRow:getReportSettings
    })
        //Добавляем панель поиска
        .jqGrid('filterToolbar');

    function downloadFile (cellvalue, options, rowObject) {
        var link = $.trim(cellvalue)
        if (link=='No rows') {
            return "No data returned";
        } else {
            return "<a href='../medserv2/output/" + link + "' download> Скачать </a>  ";
        }
    }

    function getReportSettings(rowid,iRow,iCol,e) {
        // передаем 2 параметра
        // row_id номер кликнутой строчки используется для полей и фильтров отчета
        var tableName =($(e.currentTarget).attr("id"));
        var repId=$("#"+tableName+" #"+rowid+" td:nth-child(1)").text();
        $.ajax({
            methode:'GET',
            url:'php/tab3/getReportSettings.php',
            datatype:'json',
            data:{
                repId:repId
            }
        })
            .done(function (data) {
                var repSet = JSON.parse(data);
                if (repSet!=null) {
                    switch (repSet["type"]) {
                        case'Отчет по продукции':
                            $("#reportType").val('productionReport').trigger("change")
                            .selectmenu( "refresh" );
                            break;
                        case'Отчет по заказам':
                        $("#reportType").val('orderReport').change()
                        .selectmenu( "refresh" );
                        break;
                        default:
                    }
                    $("#reportOption tr td:nth-child(4) input").val('')
                    $("#reportOption tr td:nth-child(3) input").prop("checked", false)
                    $.each(repSet, function (i, item) {
                        $("#reportOption tr#"+i+" td:nth-child(3) input").prop("checked", true)
                        $("#reportOption tr#"+i+" td:nth-child(4) input").val(item);
                        })
                }
                //$("#reportOption tr#1 td:nth-child(3) input").prop("checked", true)
            })
    }
}