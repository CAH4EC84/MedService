/**
 * Created by Alexander on 17.05.2016.
 */
function maketReport () {

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


    $("#reportType")
        .change(generateReportOption)
        .selectmenu({
        width: 300,
        create: generateReportOption,
            change:generateReportOption
    });


    $("#generateReport").button()
        .click(function () {
        var selectedFields = []
        $("#reportOption input:checked").each(function () {
            selectedFields.push({
                id: this.parentNode.parentNode.id,
                filter: $(this.parentNode.parentNode).find('td:nth-child(4) input').val()
            })
        })
        generateReport($("#reportType").val(), selectedFields);
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

}

//Передача серверу запроса.
function generateReport (repType,repFields) {

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