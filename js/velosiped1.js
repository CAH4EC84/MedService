/*
 Created by Alexander on 26.01.2016.
 ��� �������� �������� ������ ������� � ���������� ��������� �� �����������
*/

// ��� �������� �������� ������ ������ ������ �� �� ��� ���� �������.
$(document).ready(function () {
    console.log("DOM loaded.Jquery-2.1.4");

    var tabs = $('input[name="maincontainer-radio"]');
    for (var i = 0; i < tabs.length; i++) {
        console.log('Try to ask ' + tabs[i].id);
        getInfo(tabs[i].id);
        resizeColumns(tabs[i].id+'Table');
    }


    //��� ������ �������, � � �������� �������, � ������ ������, ��������� ������� ������ �� ���� ������ ������.
    $(".maincontainer>input").each (function () {
        $(".inputFilter",$('div[class$='+this.id+']')).click( function() {
            //������� ���������� ������ ��� ������.;
        //��� ������ ������ ������ ����������� � ������������ �������.
            var parentTable = $(this).parent().parent().parent();
            var trAll = $("tbody tr",parentTable);
        //�������� � ������� ��������� ������� ������.
            var searchInputs = $(".inputFilter",parentTable);

            //������� ��� ������ ������� ����������
            for (var i=0; i<searchInputs.length;i++) {
                trAll.removeClass('filtredCol'+(i+1))
            }
            var filtredClass='';
            $(".inputFilter",parentTable).keyup(debounce(function () {
                trAll.hide()
                //��������� ������
                searchInputs.each(function () {
                    if ( $(this).children().val() ) {
                        var colIndex = ($(this).index() + 1).toString(); //������ ����� ������� �������
                        trAll.removeClass('filtredCol'+colIndex); //������� ����� ������������� �� ���� ������
                        var colData = $("td:nth-child(" + colIndex + ")", trAll); //�������� ���  ������ �������
                        colData.filter(":contains(" + $(this).children().val().toLowerCase() + ")").parent("tr").addClass('filtredCol'+colIndex);
                        filtredClass+='.filtredCol'+colIndex;
                    }
                });

                //������� ������ �������������� ������
                if (filtredClass) {
                    $(filtredClass).show();
                } else {trAll.show()};
            },300));
        })
    })

    //������� ��� ����� �� �������� ������� - ��������� �������������� ���������� �� ������������� � ��
    $("tbody tr").dblclick( function () {
    //������� �������� ������ �� ������ ������� � ���� ����������
        //�������� ������ �� ��������� ������ � ������ ������ � �������� ��� �������
        var pT=$(this).parent().parent()
        $("td",this).each (function() {
            $( $(".inputFilter",pT)[$(this).index()]).children().val($(this).text() )
        })
        $("tbody tr",pT).hide();

        //�������� ����������� ������� � ��
        //�������� ������� ������� � ���� �� ��������� �������
        getInfoDetail( $("input:checked").attr('id'), $(".inputFilter",pT).children().val() );
    })



});




// �������

//������� ��������
function debounce(fn, duration) {
    var timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(fn, duration)
    }
}


//������� ������� ������ �� �� ��� ������ �������
function getInfo(tab) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange=function () {
        if (xhr.status==200 && xhr.readyState==4) {
            document.querySelectorAll('div[class$='+tab+']').item(0).innerHTML=xhr.responseText;
        }
    };
    var params="tab="+tab;
    xhr.open("POST","php/getInfo.php",false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);
}


//������� ����������� ������� � ��
function getInfoDetail(tab,queryId) {
    console.log("getInfoDetail");
    console.log(tab);
    console.log(queryId);

    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange=function () {
        if (xhr.status==200 && xhr.readyState==4) {
            $('[class$='+tab+'] + .details').html(xhr.responseText);
        }
    };
    var params="tab="+tab+'Details'+'&queryId='+queryId;
    xhr.open("POST","php/getInfo.php",false);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);

}


//������������ ��������


function resizeColumns (tableName) {
    console.log('Resize '+tableName);
    var firstLine= $('#'+tableName+" tbody tr:first>td");
 $('#'+tableName+" tbody tr:first>td").each (function () {
 var colNum='#'+tableName+" tbody td:nth-child("+($(this).index()+1)+")"
     console.log(colNum);
 lengths=[]
 $(colNum).each ( function () {
 lengths.push( $(this).text().length );
 })
     console.log ('char count-'+Math.max.apply(Math,lengths) )
 var maxL=Math.max.apply(Math,lengths)+'px'
 $('#'+tableName+" thead td:nth-child("+($(this).index()+1)+")"+".inputFilter").children().attr('size',Math.max.apply(Math,lengths))
 $()
 })
}



