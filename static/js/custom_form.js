function add_query(current, q, bool, index) {

    if (q != ''){
		if (current != ''){
			current = current + " " + bool + " ";
		}

		if(index == null) {
			current = current + q;
		} else {
			current = current + index + ':(' + q + ')';
		}
	}

    return current;
}

function search() {
    var search_form = document.searchForm;
    var query = $('#q').val();

    // query_filter
	$('input[name^="query_filter"]:checked').each(function(i){
		query_filter = $(this).val();
        query = add_query(query, query_filter, 'AND');
	});

    alert(query);
    
    search_form.q.value = $.trim(query);
    search_form.submit();

    return false;
}
