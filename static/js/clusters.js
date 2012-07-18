function remove_filter(item) {

    filters = $("#form_clusters input[type=checkbox]");
    
    // se os checkboxs existirem
    if(filters.length > 0) {

        // varre todos os itens e da check no de valor "item"
        filters.each(function(){
            if($(this).val() == item) {
                $(this).attr('checked', false);
            }
        });
    
    }
    
    $("#form_clusters").submit();

}

function add_filter(id) {
    
    filter = $("#"+id);

    // check or uncheck
    if(filter.is(':checked')) {
        filter.attr('checked', false);
    } else {
        filter.attr('checked', true);
    }

    $("#form_clusters").submit();
}

$(function(){

    // fecha todos
    $(".collapseAll").click(function(){
        $(".cluster ul").hide();
    });

    // mostra todos
    $(".expandAll").click(function(){
        $(".cluster ul").show();
    });

    // abre/fecha os clusters
    $(".cluster strong").click(function(){
        var ul = $(this).next('ul')
        ul.toggle();
    });

    $(".cluster strong").each(function(){
        $(this).find('ul').hide();
    });

    
})

function showMoreClusterItems(field){
    
    var form = $('#searchForm');
    var fb = form.find("input[name=fb]");
    var open_cluster = form.find("input[name=open_cluster]");

    fb.val(field + ":-1");
    open_cluster.val(field);

    document.searchForm.submit();

}