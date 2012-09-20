$(function(){
    
    $('.fancybox_iframe').fancybox({
        'width': 650,
        'height': 490,
        'autoScale': 'true',
        'centerOnScroll': true,
        'type' : 'iframe',
        'titlePosition' : 'inside',
    });

    $('.fancybox_iframe75').fancybox({
        'width': '75%',
        'height': '75%',
        'autoScale': 'false',
        'centerOnScroll': true,
        'type' : 'iframe',
    });

    $('.fancybox').fancybox();
    
    $('#affix').affix({
      offset: {
        top:  200,
        bottom: 270
      }
    })

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

})