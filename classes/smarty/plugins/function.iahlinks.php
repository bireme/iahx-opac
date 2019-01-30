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

    $scieloUrl['scielo-arg'] = "http://www.scielo.org.ar/";
    $scieloUrl['scielo-scl'] = "http://www.scielo.br/";
    $scieloUrl['scielo-chl'] = "http://www.scielo.cl/";
    $scieloUrl['scielo-spa'] = "http://www.scielosp.org/";

    $scieloUrl['scl'] = $scieloUrl['scielo-scl'];
    $scieloUrl['chl'] = $scieloUrl['scielo-chl'];
    $scieloUrl['spa'] = $scieloUrl['scielo-spa'];
    $scieloUrl['arg'] = $scieloUrl['scielo-arg'];

    $scieloUrl['cub'] = "http://scielo.sld.cu/";
    $scieloUrl['esp'] = "http://scielo.isciii.es/";
    $scieloUrl['col'] = "http://www.scielo.org.co/";
    $scieloUrl['mex'] = "http://www.scielo.org.mx/";
    $scieloUrl['ven'] = "http://www.scielo.org.ve/";
    $scieloUrl['prt'] = "http://www.scielo.oces.mctes.pt/";
    $scieloUrl['sss'] = "http://socialsciences.scielo.org/";

    $scieloLabel['scielo-arg']['pt'] = "Argentina";
    $scieloLabel['scielo-scl']['pt'] = "Brasil";
    $scieloLabel['scielo-scl']['en'] = "Brazil";
    $scieloLabel['scielo-chl']['pt'] = "Chile";
    $scieloLabel['scielo-spa']['pt'] = "Saúde Pública";
    $scieloLabel['scielo-spa']['es'] = "Salud Pública";
    $scieloLabel['scielo-spa']['en'] = "Public Health";

    $scieloLabel['scl']['pt'] = $scieloLabel['scielo-scl']['pt'];
    $scieloLabel['scl']['en'] = $scieloLabel['scielo-scl']['en'];
    $scieloLabel['spa']['pt'] = $scieloLabel['scielo-spa']['pt'];
    $scieloLabel['spa']['es'] = $scieloLabel['scielo-spa']['es'];
    $scieloLabel['spa']['en'] = $scieloLabel['scielo-spa']['en'];
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

    $scielo  = $params['scielo'];           //scielo links service
    $document= $params['document'];         //url's descritos no documento
    $la_text = $params['la_text'];      //idiomas disponiveis do texto completo (array)
    $la_abstract = $params['la_abstract'];  //idiomas disponiveis do resumo (array)

    $lang = (string)$params['lang'];
    $id = $params['id'];
    $scieloLinkList = array();
    $fulltextLinkList = array();
    $mediaLinkList = array();
    $abstractFulltextList="";

    // 1. tratamento dos links do servico iahlinks
    for ($occ = 0; $occ <  count($scielo); $occ++) {
        $link = $scielo[$occ];

        $found = array_search($id,$link->id);

        if  ($found !== false){
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
        if ( preg_match('/scielo/',$url['host']) && preg_match($url['host'],$output) ){
            // caso seja link para o scielo e já tenha sido adicionado na varivel output não duplica
        }else{
            $output.= '<li><a href="' . $link  . '" target="_blank">' . $link . '</a></li>';
        }

        if ( preg_match('/scielo/',$url['host']) ){
            $scieloLinkList[] = $link;
        }elseif( preg_match( '/\.wma|\.mp3/i', $link) ){
            $audioLinkList[] = $link;
        }elseif( preg_match( '/\.mov|\.mp4|\.wav|video|midias/i', $link) ){
            $videoLinkList[] = $link;
        }else{
            $fulltextLinkList[] = $link;
        }

    }

    if ( count($scieloLinkList) > 0 && $la_text != '' && $la_text != '' ){
        $abstractFulltextList = makeAbstractFulltextList($scieloLinkList, $la_abstract, $la_text, $lang);
    }

    // 3. tratamento de artigos SciELO (campo id é composto pelo PID mais subcampo ^c que indica qual SciELO)
    // ex. art-S1413-81232008000200004^cscl
    if (preg_match('\^c[a-z]{3,5}',$id) ){
        $pid = substr($id,strpos($id,'-')+1,strpos($id,'^c')-4);
        $site = substr($id,strpos($id,'^c')+2);

        if ( isset($scieloLabel[$site][$lang]) ){
            $label = $scieloLabel[$site][$lang];
        }else{
            $label = $scieloLabel[$site]["pt"];
        }

        $artLink = $scieloUrl[$site] . "scielo.php?script=sci_arttext&pid=" . $pid . "&lang=" . $lang;
        $output.= '<li><a href="' . $artLink  . '" target="_blank">SciELO ' . $label . '</a></li>';
    }


    if ($output != ''){
        $output = "<span>Link(s):</span><ul>" . $output . "</ul>";
    }

    $smarty->assign(scieloLinkList, $scieloLinkList);
    $smarty->assign(fulltextLinkList, $fulltextLinkList);
    $smarty->assign(abstractFulltextList, $abstractFulltextList);
    $smarty->assign(audioLinkList, $audioLinkList);
    $smarty->assign(videoLinkList, $videoLinkList);

    return $output;
}

function makeAbstractFulltextList($scieloLinkList, $la_abstract, $la_text, $lang){
        $firsScieloOfList = $scieloLinkList[0];
        $abstractFulltextList = "";

        $translate['pt'] = array( 'pt' => 'português',
                                  'es' => 'espanhol',
                                  'en' => 'inglês',
                                  'fulltext' => 'Texto completo',
                                  'text' => 'Texto em',
                                  'abstract' => 'Resumo'
                                );

        $translate['es'] = array( 'pt' => 'portugués',
                                  'es' => 'español',
                                  'en' => 'inglés',
                                  'fullttext' => 'Texto completo',
                                  'text' => 'Texto en',
                                  'abstract' => 'Resumen'
                                );

        $translate['en'] = array( 'pt' => 'portuguese',
                                  'es' => 'spanish',
                                  'en' => 'english',
                                  'fulltext' => 'Fulltext',
                                  'text' => 'Text in',
                                  'abstract' => 'Abstract'
                                );


        // monta lista em html contendo as opções de idioma disponíveis para o resumo e texto do artigo
         $abstractFulltextList = '';
        if ( isset($la_abstract) ){
            $scielo_href = str_replace('sci_arttext','sci_abstract',$firsScieloOfList);
            if ( preg_match('/\?/', $scielo_href) ){
                $scielo_href .= '&tlng='. $la;
            }

            $abstractFulltextList = '<span>';
            $abstractFulltextList .= '<a href="' . $scielo_href . '" target="_blank">' .  $translate[$lang]['abstract'] . '</a>';
            /*
            $c = 0;
            foreach($la_abstract as $la){
                if ($c > 0) {
                    $abstractFulltextList .= " | ";
                }
                $scielo_href = str_replace('sci_arttext','sci_abstract',$firsScieloOfList);
                if ( preg_match('/\?/', $scielo_href) ){
                    $scielo_href .= '&tlng='. $la;
                }

                $abstractFulltextList .= '<a href="' . $scielo_href .  '" target="_blank">' . $translate[$lang][$la] . '</a>';
                $c++;
            }
            */
            $abstractFulltextList .= '</span>';
        }
        if ( isset($la_text) ){
            $scielo_href = $firsScieloOfList;
            if ( preg_match('/\?/', $scielo_href) ){
                $scielo_href .= '&tlng='. $la;
            }
            $abstractFulltextList .= '<a name="abs"><img src="./image/common/viewFullText.gif"/></a>';
            $abstractFulltextList .= '<span><a href="' . $scielo_href . '" target="_blank"> ' . $translate[$lang]['fulltext'] . '</a>';
            /*
            $c = 0;
            foreach($la_text as $la){
                if ($c > 0) {
                    $abstractFulltextList .= " | ";
                }
                $scielo_href = $firsScieloOfList;
                if ( preg_match('/\?/', $scielo_href) ){
                    $scielo_href .= '&tlng='. $la;
                }
                $abstractFulltextList .= '<a href="' . $scielo_href . '" target="_blank">' . $translate[$lang][$la] . '</a>';
                $c++;
            }
            */
            $abstractFulltextList .= '</span>';
        }


        return $abstractFulltextList;
}

?>
