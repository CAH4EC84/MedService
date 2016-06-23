/**
 * Created by Alexander on 17.05.2016.
 */
function makeReport () {

    $("#reportListAccordion").accordion({
        collapsible:true,
        heightStyle: 'fill',
        autoHeight: false,
        active:0,
        width:500
    });
    $("#fromDate, #toDate").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd.mm.yy"
    })
        .button();
    $("#datePickers").buttonset({
         icons:{primary:"ui-icon-circle-check"}
        });
    $( "#toMail" ).button({
        icons: { primary: "ui-icon-mail-closed" }
    })
        .click(function () {
            if ($(this).prop('checked')) {
                $("#mailAddress").show('slow');
            } else {$("#mailAddress").hide('slow')}
        })
    if ($("#toMail").prop('checked')) {
        $("#mailAddress").show();
    } else {$("#mailAddress").hide()}

    //Выбор типа отчета.
    $("#reportType")
        .change(generateReportOption)
        .selectmenu({
        width: 300,
        create: generateReportOption,
            change:generateReportOption
    });


    //Кнопка Сгенерировать
    $("#generateReport").button()
        .click(function () {
            var selectedFields = []
            if ($("#reportType").val() =='orderReport' || $("#reportType").val() =='productionReport') {
                $("#reportOption input:checked").each(function () {
                    selectedFields.push({
                        id: this.parentNode.parentNode.id,
                        filter: $(this.parentNode.parentNode).find('td:nth-child(4) input').val()
                    })
                })
                generateZakReport($("#reportType").val(), selectedFields);
            } else if ($("#reportType").val() =='crossReport') {
                var colHeaders=$("#reportOption td:nth-child(4) select").val()
                generateCrossReport($("#reportType").val(),colHeaders,subsList,dilersList );
            }
    })
}

// Фукнкции
    //Генерация списка допустимых столбцов для отчета
function generateReportOption () {
    //Определяем тип выбранного отчета.

    if ($("#reportType").val() =='orderReport') {
        var RepFields = [
            {id:"0",name:"Дата",enbl:'No',filter:''},
            {id:"1",name:"Регион",enbl:'No',filter:"<input type='text'>"},
            {id:"2",name:"Аптечная сеть",enbl:'No',filter:'<input type="text">'},
            {id:"3",name:"Аптека",enbl:'No',filter:'<input type="text">'},
            {id:"4",name:"Поставщик",enbl:'No',filter:'<input type="text">'},
            {id:"5",name:"Сумма",enbl:'No',filter:''}
        ];
    } else if ($("#reportType").val() =='productionReport') {
        var  RepFields = [
            {id:"0",name:"Дата",enbl:'No',filter:''},
            {id:"1",name:"Регион",enbl:'No',filter:'<input type="text">'},
            {id:"2",name:"Аптечная сеть",enbl:'No',filter:'<input type="text">'},
            {id:"3",name:"Аптека",enbl:'No',filter:'<input type="text">'},
            {id:"4",name:"Поставщик",enbl:'No',filter:'<input type="text">'},
            {id:"5",name:"Препарат",enbl:'No',filter:'<input type="text">'},
            {id:"6",name:"Производитель",enbl:'No',filter:'<input type="text">'},
            {id:"7",name:"Препарат + Производитель",enbl:'No',filter:'<input type="text">'},
            {id:"8",name:"Сумма",enbl:'No',filter:''},
            {id:"9",name:"Цена",enbl:'No',filter:''},
            {id:"10",name:"Количество",enbl:'No',filter:''},
            {id:"11",name:"Обращаемость",enbl:'No',filter:''}
        ];
    } else if ($("#reportType").val() =='crossReport'){
        var  RepFields = [
            {id:"0",name:"Заголовки столбцов",enbl:'Yes',filter:'<select><option value="subsName">Подписки</option><option value="dilerName">Поставщики</option></select>'},
            {id:"0",name:"Подписки",enbl:'Yes',filter:'<button value="" id="crossReportSelectSubs">Выбрать</button>'},
            {id:"1",name:"Поставщики",enbl:'Yes',filter:'<button value="" id="crossReportSelectDilers">Выбрать</button> '},
            {id:"2",name:"Препарат",enbl:'No',},
            {id:"3",name:"Препарат + Производитель",enbl:'No',}
        ];
    }
    //Рисуем таблицу с допустимыми полями.
    var grid = $("#reportOption");
    grid.jqGrid({
        datatype: "local",
        data: RepFields,
        colNames:['id','Столбец','Вкл','Фильтр'],
        colModel:[
            {name:'id', index:'id',hidden:true},
            {name:'name',width:130,align:"center"},
            {name: 'enbl', index: 'enbl', width: 40, align: 'center', formatter: 'checkbox',
                editoptions: {value: "Yes:No"},
                formatoptions: {disabled: false}
            },
            {name:'filter',width:150,align:"center"}
        ],
        height:'auto',
        caption: "Выберите столбцы",

    });
// we have to use addJSONData to load the data
    grid[0].addJSONData({
        total: 1,
        page: 1,
        records: RepFields.length,
        rows: RepFields
    });
//Для кросового отчета добавляем кнопки с вызовом функций по выборку подписко / фирм для отчетов
    $("#crossReportSelectSubs").click( function () {
        $.jgrid.gridUnload('#selectCrossData');
        selectCrossSubs()
    });
    $("#crossReportSelectDilers").click(function () {
        $.jgrid.gridUnload('#selectCrossData');
        selectCrossDilers()
    });

}


//Передача серверу запроса на генерацию отчета

//Отчета по заказам
function generateZakReport (repType,repFields) {

    $.ajax({
        timeout:600000,
        async:true,
        methode:'GET',
        url:'php/tab3/makeZakReport.php',
        data:{
            from:$("#fromDate").val(),
            to:$("#toDate").val(),
            type:repType,
            fields:repFields,
            fname: Math.uuid(),
            mail:$("#mailAddress").val(),
            comment:$("#reportComment").val()
        },
        success: function () {$('#selectArcReport').trigger('reloadGrid')}

    })
}

    //Перекрестный отчет

//Список подписок
function selectCrossSubs() {
    console.log ('Open Dilaog for selection Subs')
    if ($("#crossReportHeadListSubs").length==0 ) {
        $('<p id="crossReportHeadListSubs">Выбранные Подписки:</p>').insertBefore("#crossListSubsDilers");
    }
    $("#crossSelectGridModal").dialog({
        modal:true,
        height:400,
        width:600,
        zIndex:5,
        resizable:true,
        title:'Выберите подписки для отчета'
    })
    $("#selectCrossData").jqGrid({
        url: "php/tab3/getCrossSubsList.php",
        mtype:"GET",
        datatype:"xml",
        colNames: ["id", "name"],
        colModel: [
            {name:"id",width:100, align:"center"},
            {name:"name", width:300,align:"center"}
        ],
        hiddengrid: false,
        pager:"#selectCrossDataPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Выберите подписки",
        height:"auto",
        autowidth:true,
        ondblClickRow:addSForCrossReport
    })
        //Добавляем панель поиска
        .jqGrid('filterToolbar');
}

//Список поставщиков
function selectCrossDilers() {
        console.log ('Open Dilaog for selection Dilers')
        $("#crossSelectGridModal").dialog({
            modal:true,
            height:400,
            width:600,
            zIndex:5,
            resizable:true,
            title:'Выберите поставщиков для отчета',
            close:function (event,ui) {
                var susbList=jQuery.unique( $("#crossReportHeadListSubs span").text().split(';') );
                var dilersList=jQuery.unique( $("#crossReportHeadListDilers span").text().split(';') );

            }
        })
    if ($("#crossReportHeadListDilers").length==0 ) {
        $('<p id="crossReportHeadListDilers">Выбранные поставщики:</p>').insertBefore("#crossListSubsDilers");
    }
    $("#selectCrossData").jqGrid({
        url: "php/tab3/getCrossDilersList.php",
        mtype:"GET",
        datatype:"xml",
        colNames: ["nodes_id","name"],
        colModel: [
            {name:"nodes_id",width:30, align:"center"},
            {name:"name", width:300,align:"center"}
        ],
        hiddengrid: false,
        pager:"#selectCrossDataPager",
        rowNum:"20",
        rowList:[20,50,"all"],
        sortname:"nodes_id",
        viewrecords:true,
        gridview:true,
        autoencode:true,
        caption:"Выберите поставщиков",
        height:"auto",
        autowidth:true,
        ondblClickRow:addSForCrossReport
    })
        //Добавляем панель поиска
        .jqGrid('filterToolbar');
}
//Добавление фирм и подписок в перекрестный отчет
function addSForCrossReport(rowid,iRow,iCol,e) {
    // передаем 2 параметра
    // e используется для получения таблицы и её заголовка
    // row_id номер разворачиваемой строчки используется для получения значения nodes_id и doc_type
    var tableName =($(e.currentTarget).attr("id"));
    var caption=$("#"+tableName).jqGrid("getGridParam", "caption");
    var clientId=$("#"+tableName+" #"+rowid+" td:nth-child(1)").text();
    if (caption=='Выберите подписки') {
        $("<span>"+clientId+";</span>").appendTo("#crossReportHeadListSubs")
    } else {
        $("<span>"+clientId+";</span>").appendTo("#crossReportHeadListDilers")
    }
}

//Генерация отчета
function generateCrossReport (repType,repFields) {

    $.ajax({
        timeout:600000,
        async:true,
        methode:'GET',
        url:'php/tab3/makeCrossReport.php',
        data:{
            type:repType,
            fields:repFields,
            fname: Math.uuid(),
            mail:$("#mailAddress").val(),
            comment:$("#reportComment").val()
        },
        success: function () {$('#selectArcReport').trigger('reloadGrid')}
    })
}