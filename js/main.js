
$j(document).ready(function() {    
           
    $j("a.popup").colorbox({
        width:"93%", 
        height:"500", 
        iframe:true
    });
    
    $j('.zebra tr:even').addClass('wt');

    $j('.del').click(function() {
        if (confirm("Уверены, что хотите удалить?")) {
            return true;
        }
        else {
            return false;
        }
    });
});