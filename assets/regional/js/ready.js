$(function(){

    $('.defaultValue').each(function(){
        var defaultVal = $(this).attr('title');
        $(this).focus(function(){
            if ($(this).val() == defaultVal){
                $(this).removeClass('active').val('');
            }
        })
        .blur(function(){
            if ($(this).val() == ''){
                $(this).addClass('active').val(defaultVal);
            }
        })
        .blur().addClass('active');
    });

    $('form').submit(function(){
        $('.defaultValue').each(function(){
            var defaultVal = $(this).attr('title');
            if ($(this).val() == defaultVal){
                $(this).val('');
            }
        });
    });

    $('.nav-toggle').click(function(){
            //get collapse content selector
            var collapse_content_selector = $(this).attr('href');

            //make the collapse content to be shown or hide
            var toggle_switch = $(this);
            $(collapse_content_selector).toggle(function(){
                //animation complete
            });
            return false;
    });

    $('#flash').delay(500).fadeIn('normal', function() {
        $(this).delay(3500).fadeOut();
    });

    // prevent submit of default placeholder text
    $("#searchForm").submit( function( event ) {
      $("#q").focus();
    });

    // show loading indicator for any form submit
    $("form").submit( function( event ) {
        var modal_export_open = ($("#ModalExport").data('bs.modal') || {})._isShown
        var modal_email_open = ($("#ModalEmail").data('bs.modal') || {})._isShown
        // don't show loading if export or email modal are open
        if (modal_export_open || modal_email_open){
        }else{
            $(".loading").removeClass('d-none');
            $(".loading").addClass('d-block');
        }
    });
    // show loading indicator breadcrumb links
    $(".breadcrumb-item a").click( function( event ) {
        $(".loading").removeClass('d-none');
        $(".loading").addClass('d-block');
    });

})
