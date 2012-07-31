// onload
$(function(){
    // watermark do input q
    $("#q").watermark("Entre uma ou mais palavras");
    
})

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



function showMoreClusterItems(field){
    
    var form = $('#searchForm');
    var fb = form.find("input[name=fb]");
    var open_cluster = form.find("input[name=open_cluster]");

    fb.val(field + ":-1");
    open_cluster.val(field);

    document.searchForm.submit();

}

//  outras funcoesssss
function go_to_page(page) {
    var form = document.searchForm;
    var count = document.searchForm.count.value;
    var from = (page*count)-count+1;
    
    form.from.value = from;
    form.page.value = page;
    form.submit();
}

function change_output(output) {
    var form = document.searchForm;
    form.output.value = output;

    form.submit();
}

function change_count(elem) {
    var form = document.searchForm;
    form.count.value = elem.value;

    form.submit();   
}

function change_language(lang) {
    document.searchForm.lang.value = lang;
    document.searchForm.submit();
}

// validate form
function validate_form() {
    var txt = "Entre uma ou mais palavras";
    var form = document.searchForm;

    if(form.q.value == txt) {
        form.q.value = "";
    }
    
    return true;

}

// minha selecao
function select_all(element) {
    var checkboxes = $('.my_selection');
    
    if(element.checked) {
        checkboxes.attr('checked', true);
    } else {
        checkboxes.attr("checked", false);
    }
}

