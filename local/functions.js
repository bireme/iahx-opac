function LLXP(obj, lang){
	url = "http://bases.bireme.br/iah/online/";

	if (lang == "pt"){ oneLetterLang = "P"; }
	if (lang == "es"){ oneLetterLang = "E"; }
	if (lang == "en"){ oneLetterLang = "I"; }

	url += oneLetterLang + "/llxp_disclaimer.htm?keepThis=true&TB_iframe=true&height=210&width=450"
	obj.href = url;	

}

function show_cochrane( lnk,refDB,refID ){
	
	form = document.searchForm;
	lang = form.lang.value;
	if (lang == "pt"){ oneLetterLang = "p"; }
	if (lang == "es"){ oneLetterLang = "e"; }
	if (lang == "en"){ oneLetterLang = "i"; }

    db = refDB.substr(refDB.indexOf("-")+1);
    db = db.toLowerCase();

    if (db == 'bandolier' || db == 'agencias' || db == 'kovacs' || db == 'evidargent' || db == 'clibplusrefs' || db == 'gestion'){
        lib = 'BCP';
    }else{
        lib = 'COC';
    }
    
	refUrl = "http://cochrane.bvsalud.org/doc.php?db=" + db + "&id=" + refID + "&lib=" + lib;

	lnk.href = refUrl;
	
	return true;
}
