// icon Accordion
$('.collapse').on('shown.bs.collapse', function(){
	$(this).parent().find(".fa-caret-down").removeClass("fa-caret-down").addClass("fa-minus");
}).on('hidden.bs.collapse', function(){
	$(this).parent().find(".fa-minus").removeClass("fa-minus").addClass("fa-caret-down");
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
		console.log('fechado');
	}else{
		openFilters();
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

// scrool botao filtro
var btFiltro = $('#btnFiltroD');
$(window).scroll(function () {
	if ($(this).scrollTop() > 450) {
		btFiltro.css({'width':'100%'});
	} else {
		btFiltro.css({'width':'auto'});
	}
});
