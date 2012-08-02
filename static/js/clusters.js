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


function show_all_clusters() {
    $(".bContent ul").slideDown(200);
}

function hide_all_clusters() {
    $(".bContent ul").slideUp(200);
}

function toggle_cluster(name) {
    $("#ul_" + name).slideToggle(100);
}