
function open_popup_url(popup_url) {
    popup_win = window.open(popup_url, 'query_info', 'scrollbars=yes, width=800, height=500, top=50, left=10');
    if (window.focus) {
        popup_win.focus();
    }
}

function open_query_info_popup(filter_id, topic_id){
    var query_info_api_url = 'https://iahx-topic-query.bvsalud.org/api/topic/?filter=' + filter_id + '&topic=' + topic_id;

    instance_id = window.location.pathname.replaceAll('/', '');

    $.getJSON(query_info_api_url)
        .done(function( response ) {
            //console.log(response);
            if (response.length > 0){
                open_popup_url(response[0].query_url);
            }
    })
}