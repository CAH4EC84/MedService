/**
 * Created by Alexander on 07.06.2016.
 */
function helpIcon () {
 $("#helpIcon").click(function () {
     console.log('HOVER!!!')
     $("#helpTextModal").dialog({
         modal:true,
         height:400,
         width:600,
         zIndex:5,
         resizable:true,
         title:'help'
     })
 })
}