// icon Accordion
$('.collapse').on('shown.bs.collapse', function(){
	$(this).parent().find(".fa-caret-down").removeClass("fa-caret-down").addClass("fa-minus");
}).on('hidden.bs.collapse', function(){
	$(this).parent().find(".fa-minus").removeClass("fa-minus").addClass("fa-caret-down");
});



var items = $('#filterEsq');

function open() {
	$(items).removeClass('close').addClass('open');
}

function close() {
	$(items).removeClass('open').addClass('close');
}
$('#btnFiltraM').click(function(event) {
	if(items.hasClass('open')){
		close();
		console.log('fechado');
	}else{
		open();
		console.log('aberto')
	}
});


/*Ativar Resumo*/
$('#customSwitches').click(function(){
	$('.reference-detail').toggleClass('collapse');
});


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
