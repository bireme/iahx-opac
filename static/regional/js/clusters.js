function remove_filter(id) {
    if ( $("#"+id).is(":visible") ) {
        $("#"+id).attr("checked", false);
    }else{
        // remove hidden file
        $("#"+id).remove();
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

function show_more_clusters (cluster, limit) {

    var form = document.getElementById("form_clusters");
    // add anchor name for go to cluster position after reload page
    form.action = form.action + "#" + cluster;
    form.fb.value = cluster + ":" + limit;

    $("#form_clusters").submit();

    form.fb.value == "";
    return false;
}

function reset_filters(){
    $('<input>').attr({
        type: 'hidden',
        id: 'reset_filters',
        name: 'reset_filters',
        value: 'ALL'
    }).appendTo('#form_clusters');

    $("#form_clusters").submit();
}

function add_view_filter(value){
    view_filter_id = 'view_filter_' + value;
    // if filter already applied remove it
    if ($('#' + view_filter_id).length){
        $('#' + view_filter_id).remove();
    }else{
    // create new filter as input hidden
        $('<input>').attr({
            type: 'hidden',
            id: view_filter_id,
            name: 'view_filter[]',
            value: value
        }).appendTo('#form_clusters');
    }
    // resubmit cluster form
    $("#form_clusters").submit();
}

function add_wizard_filter(filter_name, filter_value){
    // create new filter as input hidden
    $('<input>').attr({
        type: 'hidden',
        name: 'filter[' + filter_name + '][]',
        value: filter_value
    }).appendTo('#form_clusters');
}


var more_filter_from = {};

function show_more_filter_items(filter_id, initial_from=10){
    var LONG_FILTER_LIMIT = 100;
    var from_param = initial_from;
    var long_filter_ids = ['la', 'mj_cluster'];

    var list_filter_url = SEARCH_URL + 'list-filter/' + filter_id + '?lang=' + LANG;

    if ( long_filter_ids.indexOf(filter_id) != -1 ){
         if (filter_id in more_filter_from){
             from_param = more_filter_from[filter_id];
             more_filter_from[filter_id] += LONG_FILTER_LIMIT;
         }else{
             more_filter_from[filter_id] = LONG_FILTER_LIMIT + from_param;
         }

         list_filter_url += '&limit=' + LONG_FILTER_LIMIT;
     }
     list_filter_url += '&from=' + from_param;

     $.ajax({
         url: list_filter_url,
         cache: false,
         beforeSend: function(){
             $('#loading_more_spin_' + filter_id).show();
         },
         complete: function(){
             $('#loading_more_spin_' + filter_id).hide();
         },
         success: function(html){
             $('#show_more_' + filter_id).append(html);
             if ( html == '' || !(filter_id in more_filter_from) ){
                $('#show_more_link_' + filter_id).empty();
            }
         }
     });
 }
