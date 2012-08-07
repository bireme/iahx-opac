$(function(){
    // getting the count to add in cluster menu
    manipulate_bookmark('count');
    
    var checkbox = $(".my_selection");

    checkbox.change(function(){
        var value = $(this).val();
        
        if($(this).is(':checked')) 
            manipulate_bookmark('a', value);
        else
            manipulate_bookmark('d', value);
    });

})

function manipulate_bookmark(func, id) {

    var href = "bookmark/"+ func;
    
    if(id != "")
        href = href + "/" + id; 

    $.get(href, function(data) {
        $(".my_selection_count").html(data);
    })

    if(func == "c") {
        $(".my_selection").attr('checked', false);
    }

}

function list_bookmark() {
    $.get('bookmark/list', function(q) {
        var form = document.searchForm;
        form.q.value = q;
        form.from.value = 1;
        form.submit();
    })
}