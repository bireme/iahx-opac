$('#btSidebar').click(function(e){
    e.preventDefault();
    var view_filter_id = $(this).data('view');
    var applied_filters = [];
    $("input[id^='view_filter_']").each(function(){
        applied_filters.push($(this).val());
    });
    $.get("/view-filter/" + view_filter_id, {"applied_filters[]": applied_filters}, function(response){
        $("#sidebar-body").html(response);
    });

    $("#sidebarOpacity").show();
	$("#mySidenav").addClass('sidenavMove');

})

$('#sidebarOpacity, #closeSidebar').click(function(){
	$("#sidebarOpacity").hide();
	$("#mySidenav").removeClass('sidenavMove');
})
