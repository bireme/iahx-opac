// used for combine user query from search form (header) with selected filters (body)
$(function(){

    // if user submit the search form (header) force the submit of filter form with hidden query field
    $('#searchForm').submit( function(event) {
        event.preventDefault();
        user_query = $('#searchForm #q').val();
        interface_lang = $('#searchForm #lang').val();
        // populate hidden fields to preserve expression and language
        $('#q_index_filter').val(user_query);
        $('#lang_index_filter').val(interface_lang);
        $('#form_clusters').submit();
    });
    // if user submit the filter form populate the hidden query field
    $('#form_clusters').submit( function(event) {
        user_query = $('#searchForm #q').val();
        $('#q_index_filter').val(user_query);
    });

})
