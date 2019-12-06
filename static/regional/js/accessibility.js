/*Versão Beta*/
/*Aumentar ou Reduzir Fontes*/
$(document).ready(function(){
	var fontPadrao = 16; var classPadrao = 'p, li';
	var font12 = 12; var class12 = '.listSelectCont li a, footer, .atribArtigos, .breadcrumb-item a, .breadcrumb-item, #btnFiltroD, .formAdvance label';
	var font14 = 14; var class14 = '.box2, .title1, .textarea1, .selectOrder, .similares, .btnTools, .link1, .tree li, .btnBlue, .btnBlueM';
	var font16 = 16; var class16 = '.tab-content, .reference, .dataArticle, .btnFilter, .titleBox2, .similares .link1, #searchDocsTrigger, .listSelectCont li a, .formSelect, #q';
	var font20 = 20; var class20 = '.titleArt, .similaresMais';
	$('#fontPlus').click(function(){
		if (font16<30){
			fontPadrao = fontPadrao+1; font12 = font12+1; font14 = font14+1; font16 = font16+1; font20 = font20+1;
			$(classPadrao).css({'font-size' : fontPadrao+'px'});
			$(class12).css({'font-size' : font12+'px'});
			$(class16).css({'font-size' : font16+'px'});
			$(class14).css({'font-size' : font14+'px'});
			$(class20).css({'font-size' : font20+'px'});
		}
	});
	$('#fontLess').click(function(){
		if (font16>14){
			fontPadrao = fontPadrao-1; font12 = font12-1; font16 = font16-1; font14 = font14-1; font20 = font20-1;
			$(classPadrao).css({'font-size' : fontPadrao+'px'});
			$(class12).css({'font-size' : font12+'px'});
			$(class14).css({'font-size' : font14+'px'});
			$(class16).css({'font-size' : font16+'px'});
			$(class20).css({'font-size' : font20+'px'});
		}
	});
	$('#fontNormal').click(function(){
		fontPadrao = 16; font12 = 12; font16 = 16; font14 = 14; font20 = 20;
		$(classPadrao).css({'font-size' : 16+'px'});
		$(class12).css({'font-size' : 12+'px'});
		$(class14).css({'font-size' : 14+'px'});
		$(class16).css({'font-size' : 16+'px'});
		$(class20).css({'font-size' : 20+'px'});
	});
})
/*Navegação por atalhos*/
var pressedALT = false;
document.onkeyup=function(e){
	if(e.which == 18){
		pressedALT =false;
	}
}
document.onkeydown=function(e){
	if(e.which == 18){
		pressedALT = true;
	}
	// Main Alt + 1
	if(e.which == 49 && pressedALT == true) {
		window.location.assign("#main_container");
	}
	//Pesquisa ALT + 2
	if(e.which == 50 && pressedALT == true) {
		$("#q").focus();
	}
	//Footer ALT + 3
	if(e.which == 51 && pressedALT == true) {
		window.location.assign("#footer");
	}
}
$('a[href="#pesquisa"]').click(function(){
	$("#q").focus();
})
// cache contraste
var cor = Cookies.get('cor');
// Ao Abrir a pagina
$( document ).ready(function() {
	if(cor == '' || typeof cor === "undefined"){
		$('body').removeClass('bodyBlack');
	}else{
		$('body').addClass('bodyBlack');
	}
});
//Ao clicar Contraste
$('#contraste').on( "click", function(){
	if(cor == 'bodyBlack'){
		Cookies.set('cor', '', { expires: 1 });
	}else{
		Cookies.set('cor', 'bodyBlack', { expires: 1 });
	}
	$('body').toggleClass('bodyBlack');
});
/*Busca por voz */
window.addEventListener('DOMContentLoaded', function() {
	var speakBtn = document.querySelector('#speakBtn');
        // testa se o navegador suporta o reconhecimento de voz
        if (window.SpeechRecognition || window.webkitSpeechRecognition) {
            // captura a voz
            var SpeechRecognition = SpeechRecognition || webkitSpeechRecognition;
            var recognition = new SpeechRecognition();
            // inicia reconhecimento
            speakBtn.addEventListener('click', function(e) {
            	recognition.start();
            }, false);
            // resultado do reconhecimento
            recognition.addEventListener('result', function(e) {
            	// console.log(e);
            	var result = e.results[0][0].transcript;
            	// console.log(result);
            	document.getElementById("q").value = result;
                $("#searchForm").submit();
            }, false);
        } else {
            // nao suportado
        	$('#speakBtn').css('display','none');
        }
    }, false);
