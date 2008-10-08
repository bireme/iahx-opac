function show_refDB( refDB,refID ){
	
	form = document.searchForm;
	lang = form.lang.value;
	if (lang == "pt"){ oneLetterLang = "P"; }
	if (lang == "es"){ oneLetterLang = "E"; }
	if (lang == "en"){ oneLetterLang = "I"; }

	if (refDB == 'colecionaSUS'){
		refUrl = "http://coleciona-sus.bvs.br/cgi-bin/wxis.exe/iah/?IsisScript=iah/iah.xis&base=COLECIONASUS" + "&lang=" + oneLetterLang + "&nextAction=lnk&exprSearch=" + refID + "&indexSearch=ID" + "#1";
	}else{	
		if (refDB == 'TESE' || refDB == 'SIRPEP' || refDB == 'INDEXPSI'){
			refUrl = "http://www.psi.bvs.br/cgi-bin/wxis.exe/iah/?IsisScript=iah/iah.xic&lang=P&base=" + refDB + "&nextAction=lnk&exprSearch=" + refID + "&indexSearch=ID" + "#1";
		}else{
			if (refDB == 'HISA'){
				refUrl = "http://basehisa.coc.fiocruz.br/cgi-bin/wxis.exe/?IsisScript=iah/iah.xic&lang=P&base=HISA&nextAction=lnk&exprSearch=" + refID + "&indexSearch=ID" + "&ref=bvs.brasil#1";
			}else{
				refUrl = "http://bases.bireme.br/cgi-bin/wxislind.exe/iah/online/?IsisScript=iah/iah.xis&base=" + refDB + "&lang=" + oneLetterLang.toLowerCase() + "&nextAction=lnk&exprSearch=" + refID + "&indexSearch=ID" + "&ref=bvs.brasil#1";
			}
		}
	} 
		
	refWindow = window.open(refUrl, "reference", "height=590,width=730,menubar=yes,toolbar=yes,location=no,resizable=yes,scrollbars=yes,status=no,left=400");
	refWindow.focus();
	
	return false;
}



