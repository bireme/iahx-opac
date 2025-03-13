// icon Accordion
$('.collapse').on('shown.bs.collapse', function(){
	$(this).parent().find(".fa-angle-down").removeClass("fa-angle-down").addClass("fa-angle-up");
}).on('hidden.bs.collapse', function(){
	$(this).parent().find(".fa-angle-up").removeClass("fa-angle-up").addClass("fa-angle-down");
});

var items = $('#filterEsq');

function openFilters() {
	$(items).removeClass('close').addClass('open');
}

function closeFilters() {
	$(items).removeClass('open').addClass('close');
}

$('#btnFiltraM').click(function(event) {
	if(items.hasClass('open')){
		closeFilters();
	}else{
		openFilters();
	}
});

// get show/hide details switch from cookie
var show_detail = Cookies.get('show_detail');

// update switch on page load
$( document ).ready(function() {
	if(show_detail == 'on'){
        $('#showDetailSwitch').click();
	}
});
// update switch on page load
$('#showDetailSwitch').click(function(){
    // show/hide references details
    $('.reference-detail').toggleClass('collapse');

    // chck if after toggle reference is collapsed
    var hide_detail = $('.reference-detail').hasClass('collapse');
    // save state in cookie
    if( hide_detail == true){
        Cookies.set('show_detail', '', { expires: 1 });
	}else{
		Cookies.set('show_detail', 'on', { expires: 1 });
	}
});

// Show Abstract
function showAbstract(ab_id){
    // Copy the HTML content of the original div to the copy_ab_id div and display it
    var content = $('#' + ab_id).html();
    $('#copy_' + ab_id).html(content).toggleClass('collapse');
    return false;
};

// Scroll totop button
var toTop = $('#to-top');
// $( window ).on( "load", function() {
//     toTop.css({bottom: '-100px'});
// });

$(window).scroll(function () {
	if ($(this).scrollTop() > 1) {
		toTop.css({bottom: '11px'});
	} else {
		toTop.css({bottom: '-100px'});
	}
});

toTop.click(function () {
	$('html, body').animate({scrollTop: '0px'}, 800);
	return false;
});

// scrool botao filtro
var btFiltro = $('#btnFiltroD');
$(window).scroll(function () {
	if ($(this).scrollTop() > 450) {
		btFiltro.css({'width':'100%'});
	} else {
		btFiltro.css({'width':'auto'});
	}
});

$('.disclaimerTransparente').click(function(){
	$(this).parent().find(".fa-angle-down").toggleClass("fa-angle-up");
	$('#disclaimer').toggleClass("disclaimer");
})