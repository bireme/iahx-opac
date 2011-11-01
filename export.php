<?php
    require_once("config.php");
    require_once("./classes/Dia.php");
    require_once("./classes/Page.php");
    require_once("./classes/Log.php");
    require_once("./classes/smarty/Smarty.class.php");
    require_once("./classes/Bookmark.php");

    $option = (isset($_POST["option"]) ? $_POST["option"] : 'selected');

    $q  = stripslashes($_POST["q"]);
    
    if( $option == 'from_to' ){
        $from = ( isset($_POST["from"]) ? abs( $_POST["from"] ) : 0 );
        $count = ( isset($_POST["count"]) ? abs( $_POST["count"] ) : sizeOf($bookmark->list) );
    }else if( $option == 'selected' ){
        session_start();
        if( isset($_SESSION["bookmark"]) ){
            $bookmark = unserialize($_SESSION["bookmark"]);
            $q = "\"".$bookmark->list[0]."\"";
            $len = sizeOf($bookmark->list);
            for($i = 1; $i < $len; $i=$i+1){
                $q .= " OR "."\"".$bookmark->list[$i]."\"";
            }
            $q = "id:(". $q .")";

            $from = 0;
            $count = sizeOf($bookmark->list);
        }else{
            die("Error");
        }
    }else if( $option == 'all_references' ){
        $from = 0;
        $count = 100; // total to be exported at time (loop)
    }
    
    $col = $_REQUEST["col"];
    $site = $_REQUEST["site"];

    if ( !isset($col) ){
        $col = $defaultCollection;              // valor default criada no script de configuracao do sistema
        $VARS["col"] = $col;                    // registra no array vars para uso na XSL
    }

    if ( !isset($site) ){
        $site= $defaultSite;                    // valor default criada no script de configuracao do sistema
        $VARS["site"] = $site;                  // registra no array vars para uso na XSL
    }

    $index= $_REQUEST["index"];
    $sort = $_REQUEST["sort"];                  //sort parameter
    if (isset($_REQUEST['sort']) && $_REQUEST['sort'] != ""){
        $sort = getSortValue($colectionData,$_REQUEST["sort"]);     //get sort field to apply
    }else{
        $sort = getDefaultSort($colectionData, $q);     //get default sort
    }
    $output = ( isset($_REQUEST["output"]) && $_REQUEST["output"] != '' ? $_REQUEST["output"] : "json" );

    $where = $_REQUEST["where"];                            //select where search
    $whereFilter = getWhereFilter($colectionData,$where);
    $filter_chain = $_REQUEST["filter_chain"];          //user filter sequence (history)
    $VARS["count"] = $count;

    //DIA server connection object
    $dia = new Dia($site, $col, $count, $output, $lang);
    $page= new Page();

    $initial_restricion = html_entity_decode($colectionData->restriction);
    // filtro de pesquisa = restricao inicial  E filtro where  E filtro externo E filtro(s) selecionados
    $filterSearch = array_merge((array)$initial_restricion,(array)$whereFilter, (array)$filter,(array)$filter_chain);

    // set additiona parameters
    $dia->setParam('fb',$fb);
    $dia->setParam('fl',$fl);
    $dia->setParam('sort',$sort);

    // create a loop for export all citation
    header("Content-type: application/x-Research-Info-Systems; charset=UTF-8");
    header('Content-Disposition: attachment; filename="citation.ris"');
    // add BOM code
    print(pack("CCC",0xef,0xbb,0xbf));

    
    $diaResponse = $dia->search($q, $index, $filterSearch, $from);
    $result = json_decode($diaResponse);
    $num_found = intval($result->diaServerResponse[0]->response->numFound);
    $page->RIS();

    if( $option == 'all_references' ) {
        
        for ($export_from = $count+1; $export_from <= $num_found; $export_from += $count){            
            $diaResponse = $dia->search($q, $index, $filterSearch, $export_from);
            $result = json_decode($diaResponse);
            $page->RIS();
        }
    }
?>
