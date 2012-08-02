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