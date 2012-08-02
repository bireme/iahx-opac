// onload
$(function(){
    // watermark do input q
    $("#q").watermark("Entre uma ou mais palavras");

    $('.fancybox_iframe').fancybox({
        'width': 650,
        'height': 490,
        'autoScale': 'true',
        'centerOnScroll': true,
        'type' : 'iframe',
    });

    $('.fancybox').fancybox();
    
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

function print(count) {

    var form = document.searchForm;
    if(count) {
        form.count.value = count;
    } else {
        form.count.value = 300;
    }

    change_output("print");
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

/**
 * Mostra janela com grafico do cluster selecionado
 * @param {Node} obj
 * @param {String} titulo
 * @param {String} id
 */
function open_chart(obj, titulo, id){
    var regex = /\(\d+\)/;
    var params= "";

    var grupo = document.getElementById("ul_" + id);
    var lista = grupo.getElementsByTagName('li');

    for (i = 0; i < lista.length; i++){
        cluster = lista[i].innerHTML;
        clusterLabel = lista[i].getElementsByTagName('a')[0].innerHTML;

        ma = regex.exec(cluster);
        if (ma != null) {
            clusterTotal = ma[0].replace(/[()]/g,'');
            params += "&l[]=" + clusterLabel + "&d[]=" + clusterTotal;
        }
    }
    // caso seja o cluster de ano passa parametro para realizar sort
    if (id == 'year_cluster'){
        params += "&sort=true";
    }
    url = "chart/?title=" + titulo + params + "&KeepThis=true&TB_iframe=true&height=480&width=650";
    obj.href = url;
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

