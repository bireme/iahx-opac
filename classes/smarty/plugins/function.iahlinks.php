<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.iahlinks.php
 * Type:     function
 * Name:     iahlinks
 * Purpose:  outputs fulltext links
 * -------------------------------------------------------------
 */
function smarty_function_iahlinks($params, &$smarty)
{

	$output = "";
	$scieloUrl['scielo-scl'] = "http://www.scielo.br/";
	$scieloUrl['scielo-chl'] = "http://www.scielo.cl/";
	$scieloUrl['scielo-spa'] = "http://www.scielosp.org/";

	$scieloUrl['scl'] = $scieloUrl['scielo-scl'];
	$scieloUrl['chl'] = $scieloUrl['scielo-chl'];
	$scieloUrl['spa'] = $scieloUrl['scielo-spa'];

	$scieloUrl['arg'] = "http://www.scielo.org.ar/";
	$scieloUrl['cub'] = "http://scielo.sld.cu/";
	$scieloUrl['esp'] = "http://scielo.isciii.es/";
	$scieloUrl['col'] = "http://www.scielo.org.co/";
	$scieloUrl['mex'] = "http://www.scielo.org.mx/";
	$scieloUrl['ven'] = "http://www.scielo.org.ve/";
	$scieloUrl['prt'] = "http://www.scielo.oces.mctes.pt/";
	$scieloUrl['sss'] = "http://socialsciences.scielo.org/";

	$scieloLabel['scielo-scl']['pt'] = "Brasil";
	$scieloLabel['scielo-scl']['en'] = "Brazil";
	$scieloLabel['scielo-chl']['pt'] = "Chile";
	$scieloLabel['scielo-spa']['pt'] = "Saúde Pública";
	$scieloLabel['scielo-spa']['es'] = "Salud Pública";
	$scieloLabel['scielo-spa']['en'] = "Public Health";
	
	$scieloLabel['scl']['pt'] = $scieloLabel['scielo-scl']['pt'];
	$scieloLabel['scl']['en'] = $scieloLabel['scielo-scl']['en'];
	$scieloLabel['arg']['pt'] = "Argentina";
	$scieloLabel['esp']['pt'] = "Espanha";
	$scieloLabel['esp']['es'] = "España";
	$scieloLabel['esp']['en'] = "Spain";
	$scieloLabel['col']['pt'] = "Colômbia";
	$scieloLabel['cub']['pt'] = "Cuba";
	$scieloLabel['mex']['pt'] = "México";
	$scieloLabel['ven']['pt'] = "Venezuela";
	$scieloLabel['sss']['pt'] = "Social Sciences";
	$scieloLabel['prt']['pt'] = "Portugual";
	
	$scielo  = $params['scielo'];		//scielo links service
	$document= $params['document'];		//url's descritos no documento
	$lang = (string)$params['lang'];
	$id = $params['id'];
	$scieloLinkList = array();
	$fulltextLinkList = array();

	// 1. tratamento dos links do servico iahlinks	
	for ($occ = 0; $occ <  count($scielo); $occ++) {
		$link = $scielo[$occ];

		$found = array_search($id,$link->id);
		
		if 	($found !== false){
			foreach($link as $site => $url){
				if ($site != 'id'){
					foreach($url as $pid){
						$fullLink = $scieloUrl[$site] . "scielo.php?script=sci_arttext&amp;pid=" . $pid . "&amp;lang=" . $lang;
						$scieloLinkList[] = $fullLink;
						
						if ( isset($scieloLabel[$site][$lang]) ){
							$label = $scieloLabel[$site][$lang];
						}else{
							$label = $scieloLabel[$site]["pt"];
						}

						$output .= '<li><a href="' . $fullLink  . '" target="_blank">SciELO ' . $label . '</a></li>';
					}
				}
			}
		}	
	}	

	// 2. tratamento dos links que estao registrados no documento
	for ($occ = 0; $occ <  count($document); $occ++) {
		$link = $document[$occ];
		$url = parse_url($link);
	
		// verificar se urls scielos descritos no documento já não estão no output
		if ( eregi('scielo',$url['host']) && eregi($url['host'],$output) ){
			// caso seja link para o scielo e já tenha sido adicionado na varivel output não duplica
		}else{
			$output.= '<li><a href="' . $link  . '" target="_blank">' . $link . '</a></li>';
			if ( eregi('scielo',$url['host']) ){
				$scieloLinkList[] = $link;
			}else{
				$fulltextLinkList[] = $link;
			}
			
		}
	}	
	
	// 3. tratamento de artigos SciELO (campo id é composto pelo PID mais subcampo ^c que indica qual SciELO)
	// ex. art-S1413-81232008000200004^cscl
	if (eregi('\^c[a-z]{3,5}',$id) ){
		$pid = substr($id,strpos($id,'-')+1,strpos($id,'^c')-4);
		$site = substr($id,strpos($id,'^c')+2);
	
		$artLink = $scieloUrl[$site] . "scielo.php?script=sci_arttext&pid=" . $pid . "&lang=" . $lang;
		$output.= '<li><a href="' . $artLink  . '" target="_blank">' . $artLink . '</a></li>';
	}


	if ($output != ''){
		$output = "<span>Link(s):</span><ul>" . $output . "</ul>";
	}

	$smarty->assign(scieloLinkList, $scieloLinkList);
	$smarty->assign(fulltextLinkList, $fulltextLinkList);
	
    return $output;
}
?>
