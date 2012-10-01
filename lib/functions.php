<?php

// translation
function translate($label, $group=NULL) {

    global $texts, $lang;

    // labels on texts.ini must be array key without spaces
    $label = str_replace(' ','_', $label);

    if($group == NULL) {
        if(isset($texts[$label]) and $texts[$label] != "") {
            return $texts[$label];
        }
    } else {
        if(isset($texts[$group][$label]) and $texts[$group][$label] != "") {
            return $texts[$group][$label];
        }
    }

    return ucfirst(str_replace("_", " ", $label));
}

// funcao retirada da pagina http://www.php.net/utf8_encode 
function isUTF8($string){
    if (is_array($string)) {
        $enc = implode('', $string);
        return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
    }else{
        return (utf8_encode(utf8_decode($string)) == $string);
    }
}

function getWhereFilter($colectionData, $where){
    $whereFilter = "";
    if (isset($colectionData->where_list)){
        foreach($colectionData->where_list->where as $whereOpt  ){
            if ($whereOpt->name == $where){
                $whereFilter = $whereOpt->filter;
                break;
            }
        }
    }
    return html_entity_decode($whereFilter);
}

function getSortValue($colectionData, $sort){
    $whereFilter = "";
    if ( isset($colectionData->sort_list) ){
        foreach( $colectionData->sort_list->sort as $sortItem  ){
            if ($sortItem->name == $sort || $sortItem->value == $sort){
                $sortValue = $sortItem->value;
                break;
            }
        }
    }
    return urlencode($sortValue);
}

function getDefaultSort($colectionData, $q){
    $sortValue = "";
    $count = 0;
    if ( isset($colectionData->sort_list) ){
        foreach( $colectionData->sort_list->sort as $sortItem  ){
            // seleciona primeito item do config como default
            if ( $count == 0){
                $sortValue = $sortItem->value;              
            }
            // caso a query esteja vazia verifica se o item possue default_for_empty_query  
            if ( $q == '' && isset($sortItem->default_for_empty_query) ){
                $sortValue = $sortItem->value;
            }
            $count++;
        }
    }
    return urlencode($sortValue);
}
// function to work when PHP directive magic_quotes_gpc is OFF
function addslashes_array($a){
    if(is_array($a)){
        foreach($a as $n=>$v){
            $b[$n]=addslashes_array($v);
        }
        return $b;
    }else{
        if ($a != ''){
            return addslashes($a);
        }
    }
}

/* Log User Actions */

function log_user_action($lang, $col, $site, $query, $index, $where, $filter, $page, $output, $session_id){
    global $config, $DEFAULT_PARAMS; 

    // set default values
    $col = ($col != '' ? $col : $DEFAULT_PARAMS['defaultCollection']);
    $site = ($site != '' ? $site : $DEFAULT_PARAMS['defaultSite']);
    $query = ($query != ''? $query : "*");
    $index = ($index != ''? $index : "*");
    $where = ($where != ''? $where : "*");
    $filter = ($filter != ''? $filter : "*");
    $page = ($page != ''? $page : "1");
    $output = ($output != ''? $output : "site");

    // log user action
    if ($config->log_user_search == 'true' ){        
        $log = new Log();
        $log->fields['ip']   = $_SERVER["REMOTE_ADDR"];
        $log->fields['lang'] = $lang;
        $log->fields['col']  = $col;
        $log->fields['site'] = $site;
        $log->fields['query']= $query;
        $log->fields['index']= $index;
        $log->fields['where']= $where;
        $log->fields['filter'] = $filter;
        $log->fields['page'] = $page;
        $log->fields['output'] = $output;
        $log->fields['referer'] = $_SERVER['HTTP_REFERER'];
        $log->fields['session'] = $session_id;

        $log->writeLog();
    }    


}


/* Twig Extensions */
function custom_template($filename) {
    if( file_exists(CUSTOM_TEMPLATE_PATH . $filename) ) {
        return str_replace(TEMPLATE_PATH, "", CUSTOM_TEMPLATE_PATH) . $filename;
    } 
    
    return $filename;
}

function occ($params) {

    extract($params); 
    
    if ( is_array($element) ){
        for ($occ = 0; $occ <  count($element); $occ++) {
            if ($occ > 0){
                $output .= $separator . " ";
            }
            if ( $translate == true ){
                $text = translate($element[$occ], $group);
            }else{
                $text = $element[$occ];
            }
            $output .= $text;
        }
    }else{
        $output .= $element;
    }    

    return $output;
    
}

function filter_substring_after($text, $needle = '-'){
    if (strpos($text, $needle) !== false){
        return substr($text, strpos($text, $needle)+strlen($needle));
    }else{
        return "";
    }
}

function filter_substring_before($text, $needle = '-'){
    if (strpos($text, $needle) !== false){
        return substr($text, 0,strpos($text, $needle));
    }else{
        return "";
    }
}

function filter_contains($text, $needle){
    return mb_strpos($text, $needle) !== false;
}

function filter_starts_with($text, $needle){
    $length = strlen($needle);
    return (substr($text, 0, $length) === $needle);
}



?>